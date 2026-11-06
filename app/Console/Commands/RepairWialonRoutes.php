<?php

namespace App\Console\Commands;

use App\Models\Waybill;
use App\Services\WialonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RepairWialonRoutes extends Command
{
    protected $signature = 'wialon:repair-routes {--waybill= : Limit the repair to a waybill UUID} {--dry-run : Report route assignments without changing Wialon}';

    protected $description = 'Check Wialon route vehicle assignments and repair missing assignments';

    public function handle(WialonService $wialon): int
    {
        $query = Waybill::query()
            ->with('vehicle:id,gps_unit_id,gps_imei')
            ->whereNotNull('wialon_route_id')
            ->whereHas('vehicle', fn ($vehicles) => $vehicles->whereNotNull('gps_unit_id'));

        if ($waybillId = $this->option('waybill')) {
            $query->whereKey($waybillId);
        }

        $waybills = $query->orderBy('waybill_number')->get();
        if ($waybills->isEmpty()) {
            $this->info('No waybills with a Wialon route and linked GPS vehicle were found.');
            return self::SUCCESS;
        }

        $hasFailures = false;
        foreach ($waybills as $waybill) {
            $routeId = (int) $waybill->wialon_route_id;
            try {
                $unitId = $wialon->resolveUnitId($waybill->vehicle->gps_unit_id, $waybill->vehicle->gps_imei);
                $snapshot = $wialon->getRouteSnapshot($routeId);
                $assignedUnits = array_map('intval', $snapshot['rcfg']['units'] ?? []);

                if ($unitId !== null && in_array($unitId, $assignedUnits, true)) {
                    $this->line(sprintf('OK      %s | route %d | unit %d', $waybill->waybill_number, $routeId, $unitId));
                    continue;
                }

                $this->warn(sprintf('MISSING %s | route %d | unit %s', $waybill->waybill_number, $routeId, $unitId === null ? 'unresolved' : $unitId));
                if ($this->option('dry-run')) {
                    continue;
                }

                $result = $unitId === null ? null : $wialon->syncWaybillRoute($unitId, [
                    'waybill_number' => $waybill->waybill_number,
                    'origin' => $waybill->origin,
                    'origin_lat' => $waybill->origin_lat,
                    'origin_lng' => $waybill->origin_lng,
                    'destination' => $waybill->destination,
                    'destination_lat' => $waybill->destination_lat,
                    'destination_lng' => $waybill->destination_lng,
                    'checkpoints' => $waybill->checkpoints,
                    'wialon_route_id' => $routeId,
                    'gps_imei' => $waybill->vehicle->gps_imei,
                ]);

                if (($result['unit_assigned'] ?? false) === true) {
                    $newRouteId = (int) ($result['wialon_route_id'] ?? $routeId);
                    if ($newRouteId !== $routeId) {
                        $waybill->forceFill(['wialon_route_id' => $newRouteId])->save();
                    }
                    $this->info(sprintf('REPAIRED %s | route %d | unit %d', $waybill->waybill_number, $newRouteId, $unitId));
                    continue;
                }

                $hasFailures = true;
                $this->error(sprintf('FAILED  %s | route %d | %s', $waybill->waybill_number, $routeId, $result['error'] ?? 'Unit could not be resolved.'));
            } catch (\Throwable $exception) {
                $hasFailures = true;
                Log::warning('Wialon route repair failed', [
                    'waybill_id' => $waybill->id,
                    'wialon_route_id' => $routeId,
                    'error' => $exception->getMessage(),
                ]);
                $this->error(sprintf('FAILED  %s | route %d | %s', $waybill->waybill_number, $routeId, $exception->getMessage()));
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }
}
