<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncWialonVehicleUnits extends Command
{
    /** Wialon flags: base properties (id/name) + advanced properties (uid/IMEI). */
    private const UNIT_FLAGS = 0x00000001 | 0x00000100 | 0x00000200;

    protected $signature = 'wialon:sync-vehicles
                            {--dry-run : Show proposed links without writing to the database}
                            {--host= : Override the Wialon host configured in WIALON_HOST}';

    protected $description = 'Match vehicles to Wialon units by exact fleet/truck name and sync gps_unit_id/IMEI';

    public function handle(): int
    {
        $token = (string) config('services.wialon.token');
        $host = rtrim((string) ($this->option('host') ?: config('services.wialon.host')), '/');

        if ($token === '') {
            $this->error('WIALON_TOKEN is not configured. Add it to .env and run php artisan config:clear.');

            return self::FAILURE;
        }

        if ($host === '') {
            $this->error('WIALON_HOST is not configured.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $client = Http::acceptJson()->timeout(30)->retry(2, 500);
        $sid = null;

        try {
            $sid = $this->login($client, $host, $token);
            $units = $this->fetchUnits($client, $host, $sid);
            $result = $dryRun
                ? $this->sync($units, true)
                : DB::transaction(fn () => $this->sync($units, false));
        } catch (\Throwable $exception) {
            Log::error('Wialon vehicle sync failed', [
                'message' => $exception->getMessage(),
                'host' => $host,
            ]);
            $this->error('Wialon sync failed: ' . $exception->getMessage());

            return self::FAILURE;
        } finally {
            if ($sid !== null) {
                $this->logout($client, $host, $sid);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s complete: %d unit(s), %d vehicle(s) matched, %d updated, %d already current, %d unmatched, %d conflict(s), %d unassigned unit(s).',
            $dryRun ? 'Dry run' : 'Sync',
            count($units),
            $result['matched'],
            $result['updated'],
            $result['unchanged'],
            count($result['unmatchedVehicles']),
            count($result['conflicts']),
            count($result['unmatchedUnits']),
        ));

        $this->renderReport('Unmatched vehicles', $result['unmatchedVehicles'], ['fleet_no', 'truck_no']);
        $this->renderReport('Conflicts (not updated)', $result['conflicts'], ['fleet_no', 'reason']);
        $this->renderReport('Wialon units without a vehicle', $result['unmatchedUnits'], ['id', 'name', 'imei']);

        Log::info('Wialon vehicle sync completed', [
            'dry_run' => $dryRun,
            'units' => count($units),
            'matched' => $result['matched'],
            'updated' => $result['updated'],
            'unchanged' => $result['unchanged'],
            'unmatched_vehicles' => $result['unmatchedVehicles'],
            'conflicts' => $result['conflicts'],
            'unmatched_units' => $result['unmatchedUnits'],
        ]);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{id: int, name: string, imei: ?string, hw_type_id: ?int}>
     */
    private function fetchUnits(PendingRequest $client, string $host, string $sid): array
    {
        $response = $this->request($client, $host, 'core/search_items', [
            'spec' => [
                'itemsType' => 'avl_unit',
                'propName' => 'sys_name',
                'propValueMask' => '*',
                'sortType' => 'sys_name',
            ],
            'force' => 1,
            'flags' => self::UNIT_FLAGS,
            'from' => 0,
            'to' => 0,
        ], $sid);

        if (isset($response['error'])) {
            throw new RuntimeException('Wialon unit search error ' . $response['error'] . '.');
        }

        return collect($response['items'] ?? [])
            ->map(fn (array $unit) => [
                'id'         => (int) ($unit['id'] ?? 0),
                'name'       => trim((string) ($unit['nm'] ?? '')),
                'imei'       => $this->emptyToNull($unit['uid'] ?? null),
                'hw_type_id' => isset($unit['hw']) && is_numeric($unit['hw']) ? (int) $unit['hw'] : null,
            ])
            ->filter(fn (array $unit) => $unit['id'] > 0 && $unit['name'] !== '')
            ->values()
            ->all();
    }

    private function login(PendingRequest $client, string $host, string $token): string
    {
        $response = $this->request($client, $host, 'token/login', ['token' => $token]);
        $sid = $response['eid'] ?? $response['session'] ?? null;

        if (! is_string($sid) || $sid === '') {
            $err = isset($response['error']) ? ' (Wialon error ' . $response['error'] . ')' : '';
            throw new RuntimeException('Wialon token login was rejected' . $err . '. Check WIALON_TOKEN and its unit-view rights.');
        }

        return $sid;
    }

    /** @return array<string, mixed> */
    private function request(PendingRequest $client, string $host, string $service, array $params, ?string $sid = null): array
    {
        $query = ['svc' => $service, 'params' => json_encode($params, JSON_THROW_ON_ERROR)];

        if ($sid !== null) {
            $query['sid'] = $sid;
        }

        $response = $client->get($host . '/wialon/ajax.html', $query);

        if ($response->failed()) {
            throw new RuntimeException('Wialon HTTP request failed with status ' . $response->status() . '.');
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException('Wialon returned an invalid JSON response.');
        }

        return $body;
    }

    private function logout(PendingRequest $client, string $host, string $sid): void
    {
        try {
            $this->request($client, $host, 'core/logout', [], $sid);
        } catch (\Throwable $exception) {
            Log::warning('Wialon logout failed after vehicle sync', ['message' => $exception->getMessage()]);
        }
    }

    /**
     * Name is deliberately the primary key: fleet_no first, then truck_no.
     * An existing IMEI must agree with a named unit; otherwise the command
     * reports the conflict rather than silently re-linking a vehicle.
     *
     * @param array<int, array{id: int, name: string, imei: ?string}> $units
     * @return array<string, mixed>
     */
    private function sync(array $units, bool $dryRun): array
    {
        $unitsByName = [];
        $unitsByImei = [];

        foreach ($units as $unit) {
            $unitsByName[$this->normalise($unit['name'])][] = $unit;

            if ($unit['imei'] !== null) {
                $unitsByImei[$this->normalise($unit['imei'])][] = $unit;
            }
        }

        $result = [
            'matched' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'unmatchedVehicles' => [],
            'conflicts' => [],
            'unmatchedUnits' => $units,
        ];
        $matchedUnitIds = [];
        $now = Carbon::now();

        Vehicle::query()
            ->where(function ($query) {
                $query->whereNull('gps_provider')->orWhere('gps_provider', 'wialon');
            })
            ->orderBy('fleet_no')
            ->each(function (Vehicle $vehicle) use (&$result, &$matchedUnitIds, $unitsByName, $unitsByImei, $dryRun, $now) {
                [$unit, $method, $reason] = $this->findUnit($vehicle, $unitsByName, $unitsByImei);

                if ($unit === null) {
                    $entry = ['fleet_no' => $vehicle->fleet_no, 'truck_no' => $vehicle->truck_no];

                    if ($reason === 'No exact Wialon name or IMEI match') {
                        $result['unmatchedVehicles'][] = $entry;
                    } else {
                        $entry['reason'] = $reason;
                        $result['conflicts'][] = $entry;
                    }

                    return;
                }

                if (isset($matchedUnitIds[$unit['id']])) {
                    $result['conflicts'][] = [
                        'fleet_no' => $vehicle->fleet_no,
                        'reason' => 'Wialon unit ' . $unit['id'] . ' is already matched to fleet ' . $matchedUnitIds[$unit['id']],
                    ];

                    return;
                }

                $result['matched']++;
                $matchedUnitIds[$unit['id']] = $vehicle->fleet_no;

                $attributes = [
                    'gps_unit_id'    => $unit['id'],
                    'gps_hw_type_id' => $unit['hw_type_id'] ?? $vehicle->gps_hw_type_id,
                    'gps_imei'       => $unit['imei'],
                    'gps_provider'   => 'wialon',
                    'last_sync_at'   => $now,
                ];
                $changed = (string) $vehicle->gps_unit_id !== (string) $unit['id']
                    || (string) $vehicle->gps_hw_type_id !== (string) ($unit['hw_type_id'] ?? '')
                    || $this->emptyToNull($vehicle->gps_imei) !== $unit['imei']
                    || $vehicle->gps_provider !== 'wialon';

                if ($changed) {
                    $result['updated']++;
                    $this->line(sprintf('%s: %s -> unit %d (%s)%s', $dryRun ? 'Would link' : 'Linked', $vehicle->fleet_no, $unit['id'], $unit['name'], $method === 'imei' ? ' [IMEI fallback]' : ''));

                    if (! $dryRun) {
                        $vehicle->forceFill($attributes)->save();
                    }
                } else {
                    $result['unchanged']++;

                    if (! $dryRun) {
                        $vehicle->forceFill(['last_sync_at' => $now])->save();
                    }
                }
            });

        $result['unmatchedUnits'] = array_values(array_filter(
            $units,
            fn (array $unit) => ! isset($matchedUnitIds[$unit['id']]),
        ));

        return $result;
    }

    /**
     * @param array<string, array<int, array{id: int, name: string, imei: ?string}>> $unitsByName
     * @param array<string, array<int, array{id: int, name: string, imei: ?string}>> $unitsByImei
     * @return array{0: ?array{id: int, name: string, imei: ?string}, 1: ?string, 2: string}
     */
    private function findUnit(Vehicle $vehicle, array $unitsByName, array $unitsByImei): array
    {
        $storedImei = $this->emptyToNull($vehicle->gps_imei);
        if ($storedImei === null && $vehicle->gps_unit_id !== null && strlen((string) $vehicle->gps_unit_id) >= 13) {
            $storedImei = (string) $vehicle->gps_unit_id;
        }

        foreach (['fleet_no', 'truck_no'] as $field) {
            $value = $this->emptyToNull($vehicle->{$field});

            if ($value === null) {
                continue;
            }

            $matches = $unitsByName[$this->normalise($value)] ?? [];

            if (count($matches) > 1 && $storedImei !== null) {
                $imeiMatches = array_values(array_filter(
                    $matches,
                    fn (array $unit) => $unit['imei'] !== null && $this->normalise($unit['imei']) === $this->normalise($storedImei),
                ));

                if (count($imeiMatches) === 1) {
                    return [$imeiMatches[0], 'name+imei', ''];
                }
            }

            if (count($matches) === 1) {
                $unit = $matches[0];

                if ($storedImei !== null && $unit['imei'] !== null && $this->normalise($storedImei) !== $this->normalise($unit['imei'])) {
                    return [null, null, 'Name match disagrees with stored gps_imei'];
                }

                return [$unit, 'name', ''];
            }

            if (count($matches) > 1) {
                return [null, null, 'Multiple Wialon units have the same ' . $field . ' name'];
            }
        }

        if ($storedImei !== null) {
            $matches = $unitsByImei[$this->normalise($storedImei)] ?? [];

            if (count($matches) === 1) {
                return [$matches[0], 'imei', ''];
            }

            if (count($matches) > 1) {
                return [null, null, 'Multiple Wialon units have the stored gps_imei'];
            }
        }

        return [null, null, 'No exact Wialon name or IMEI match'];
    }

    /** Case-insensitive exact comparison with leading/trailing/duplicate spaces ignored. */
    private function normalise(string $value): string
    {
        return mb_strtoupper((string) preg_replace('/\\s+/', ' ', trim($value)));
    }

    private function emptyToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** @param array<int, array<string, mixed>> $rows @param array<int, string> $headers */
    private function renderReport(string $title, array $rows, array $headers): void
    {
        if ($rows === []) {
            return;
        }

        $this->newLine();
        $this->warn($title . ':');
        $this->table($headers, array_map(
            fn (array $row) => array_map(fn (string $header) => (string) ($row[$header] ?? ''), $headers),
            $rows,
        ));
    }
}
