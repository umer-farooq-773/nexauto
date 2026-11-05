<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;

class WialonSyncStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wialon:status {--failed : Show only failed syncs} {--pending : Show only pending syncs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show Wialon synchronization status for all vehicles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Wialon Synchronization Status');
        $this->newLine();

        $query = Vehicle::where('gps_provider', 'wialon');

        if ($this->option('failed')) {
            $query->where('gps_sync_status', 'failed');
            $this->warn('Showing only failed syncs');
        } elseif ($this->option('pending')) {
            $query->where('gps_sync_status', 'pending');
            $this->warn('Showing only pending syncs');
        }

        $vehicles = $query->orderBy('created_at', 'desc')->get();

        if ($vehicles->isEmpty()) {
            $this->info('No Wialon vehicles found.');
            return 0;
        }

        $tableData = [];
        foreach ($vehicles as $vehicle) {
            $status = $vehicle->gps_sync_status ?? 'unknown';
            $statusIcon = match($status) {
                'synced' => '✓',
                'failed' => '✗',
                'pending' => '⏳',
                default => '?'
            };

            $tableData[] = [
                $vehicle->id,
                $vehicle->truck_no ?? $vehicle->fleet_no ?? 'N/A',
                $vehicle->gps_imei ?? 'N/A',
                $vehicle->gps_unit_id ?? 'N/A',
                $statusIcon . ' ' . ucfirst($status),
                $vehicle->last_sync_at ? $vehicle->last_sync_at->diffForHumans() : 'Never',
                $vehicle->gps_sync_error ? substr($vehicle->gps_sync_error, 0, 50) . '...' : '-',
            ];
        }

        $this->table(
            ['ID', 'Vehicle', 'IMEI', 'Unit ID', 'Status', 'Last Sync', 'Error'],
            $tableData
        );

        $this->newLine();

        // Summary
        $total = $vehicles->count();
        $synced = $vehicles->where('gps_sync_status', 'synced')->count();
        $failed = $vehicles->where('gps_sync_status', 'failed')->count();
        $pending = $vehicles->where('gps_sync_status', 'pending')->count();

        $this->info("Summary:");
        $this->line("Total Wialon Vehicles: {$total}");
        $this->line("✓ Synced: {$synced}");
        $this->line("✗ Failed: {$failed}");
        $this->line("⏳ Pending: {$pending}");

        if ($failed > 0) {
            $this->newLine();
            $this->warn("You have {$failed} failed sync(s). Run with --failed flag to see details:");
            $this->line("php artisan wialon:status --failed");
        }

        return 0;
    }
}
