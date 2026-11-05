<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;

class AuditWialonVehicles extends Command
{
    protected $signature = 'wialon:audit-vehicles';

    protected $description = 'Audit vehicles table for invalid or swapped gps_unit_id (IMEI entered as unit_id) and gps_imei values';

    public function handle(): int
    {
        $this->info('Auditing vehicles table for Wialon system ID vs. hardware IMEI data entry issues…');

        $vehicles = Vehicle::query()->orderBy('fleet_no')->get();
        $total = $vehicles->count();

        $issues = [];
        $validCount = 0;

        foreach ($vehicles as $vehicle) {
            $unitId = $vehicle->gps_unit_id ? (string) $vehicle->gps_unit_id : null;
            $imei = $vehicle->gps_imei ? trim((string) $vehicle->gps_imei) : null;

            $hasIssue = false;
            $reasons = [];

            // Root cause check: IMEIs are typically 15 digits (13+ digits). Wialon system unit IDs are much shorter (9-10 digits).
            if ($unitId !== null && strlen($unitId) >= 13) {
                $hasIssue = true;
                $reasons[] = sprintf('gps_unit_id ("%s", %d digits) is an IMEI, not a Wialon system unit ID', $unitId, strlen($unitId));
            }

            // Check if gps_imei looks like a short system ID instead of an IMEI
            if ($imei !== null && strlen($imei) <= 10 && ctype_digit($imei)) {
                $hasIssue = true;
                $reasons[] = sprintf('gps_imei ("%s", %d digits) looks like a system unit ID, not a 15-digit IMEI', $imei, strlen($imei));
            }

            if ($hasIssue) {
                $issues[] = [
                    'fleet_no' => $vehicle->fleet_no ?? 'N/A',
                    'truck_no' => $vehicle->truck_no ?? 'N/A',
                    'gps_unit_id' => $unitId ?? '(null)',
                    'gps_imei' => $imei ?? '(null)',
                    'issue' => implode('; ', $reasons),
                ];
            } else {
                $validCount++;
            }
        }

        $this->newLine();

        if (count($issues) > 0) {
            $this->error(sprintf('Audit FAILED: %d of %d vehicle(s) have data-entry issues!', count($issues), $total));
            $this->table(['Fleet No', 'Truck No', 'gps_unit_id', 'gps_imei', 'Issue Detected'], $issues);
            $this->newLine();
            $this->warn('Run "php artisan wialon:sync-vehicles" to automatically fix these mismatched IDs.');

            return self::FAILURE;
        }

        $this->info(sprintf('Audit PASSED: All %d vehicle(s) have valid Wialon system unit ID and IMEI configurations.', $validCount));

        return self::SUCCESS;
    }
}
