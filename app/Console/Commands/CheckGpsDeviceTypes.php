<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckGpsDeviceTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:check-devices {--fix : Prompt to fix invalid entries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check GPS device types for valid Wialon hardware type IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('GPS Device Types Configuration Check');
        $this->newLine();

        $devices = DB::table('gps_device_types')
            ->select('id', 'name', 'wialon_hw_type_id', 'status')
            ->get();

        if ($devices->isEmpty()) {
            $this->warn('⚠️  No GPS device types found in database!');
            $this->newLine();
            $this->info('You need to add GPS device types before creating vehicles with Wialon.');
            return 1;
        }

        $tableData = [];
        $invalidDevices = [];

        foreach ($devices as $device) {
            $status = $device->status == 1 ? '✓ Active' : '✗ Inactive';
            $hwType = $device->wialon_hw_type_id ?? 'NULL';
            
            $isValid = !empty($device->wialon_hw_type_id) && $device->wialon_hw_type_id > 0;
            $validation = $isValid ? '✓ Valid' : '✗ Invalid';
            
            if (!$isValid && $device->status == 1) {
                $invalidDevices[] = $device;
            }

            $tableData[] = [
                substr($device->id, 0, 8) . '...',
                $device->name,
                $hwType,
                $validation,
                $status,
            ];
        }

        $this->table(
            ['ID', 'Device Name', 'Wialon HW Type ID', 'Validation', 'Status'],
            $tableData
        );

        $this->newLine();

        // Summary
        $total = $devices->count();
        $active = $devices->where('status', 1)->count();
        $valid = $devices->filter(function ($d) {
            return !empty($d->wialon_hw_type_id) && $d->wialon_hw_type_id > 0;
        })->count();
        $invalid = $total - $valid;

        $this->info("Summary:");
        $this->line("Total Devices: {$total}");
        $this->line("Active Devices: {$active}");
        $this->line("Valid Wialon HW Type IDs: {$valid}");
        
        if ($invalid > 0) {
            $this->line("Invalid Wialon HW Type IDs: {$invalid}");
        }

        if (!empty($invalidDevices)) {
            $this->newLine();
            $this->warn('⚠️  Warning: The following active devices have invalid Wialon hardware type IDs:');
            foreach ($invalidDevices as $device) {
                $this->line("  - {$device->name} (ID: {$device->wialon_hw_type_id ?? 'NULL'})");
            }
            $this->newLine();
            $this->error('These devices cannot be used for vehicle creation until fixed!');
            $this->newLine();
            
            if ($this->option('fix')) {
                $this->info('Common Wialon Hardware Type IDs:');
                $this->line('  1    - Generic GPS tracker');
                $this->line('  12   - Teltonika FM series');
                $this->line('  111  - Teltonika FMB640');
                $this->line('  1470 - Teltonika FMB920');
                $this->line('  2220 - Teltonika FMC130');
                $this->line('  3330 - Concox GT06N');
                $this->newLine();
                $this->info('To fix, update the gps_device_types table:');
                $this->line('UPDATE gps_device_types SET wialon_hw_type_id = [correct_id] WHERE id = [device_id];');
            }
        } else {
            $this->newLine();
            $this->info('✓ All device types have valid Wialon hardware type IDs!');
        }

        return $invalid > 0 ? 1 : 0;
    }
}
