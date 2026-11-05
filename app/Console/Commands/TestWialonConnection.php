<?php

namespace App\Console\Commands;

use App\Services\WialonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestWialonConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wialon:test {--unit-name=TestUnit} {--hw-type-id=1} {--imei=123456789012345}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Wialon API connection and unit creation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Wialon Connection...');
        $this->newLine();

        // Check configuration
        $this->info('Configuration Check:');
        $host = config('services.wialon.host');
        $token = config('services.wialon.token');
        $creatorId = config('services.wialon.creator_id');

        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['Wialon Host', $host, $host ? '✓' : '✗'],
                ['Wialon Token', $token ? substr($token, 0, 10) . '...' : 'Not Set', $token ? '✓' : '✗'],
                ['Creator ID', $creatorId ?? 'Not Set', $creatorId ? '✓' : '✗'],
            ]
        );

        if (!$host || !$token) {
            $this->error('Wialon configuration is incomplete. Please check your .env file.');
            return 1;
        }

        $this->newLine();

        try {
            $service = new WialonService();
            
            // Test login
            $this->info('Testing Wialon Login...');
            $reflection = new \ReflectionClass($service);
            $loginMethod = $reflection->getMethod('login');
            $loginMethod->setAccessible(true);
            $sessionId = $loginMethod->invoke($service);

            if ($sessionId) {
                $this->info("✓ Login successful. Session ID: {$sessionId}");
            } else {
                $this->error('✗ Login failed. Check your token and host configuration.');
                return 1;
            }

            $this->newLine();

            // Test unit creation if requested
            if ($this->option('unit-name')) {
                $unitName = $this->option('unit-name');
                $hwTypeId = (int) $this->option('hw-type-id');
                $imei = $this->option('imei');

                $this->info('Testing Unit Creation...');
                $this->table(
                    ['Parameter', 'Value'],
                    [
                        ['Unit Name', $unitName],
                        ['Hardware Type ID', $hwTypeId],
                        ['IMEI', $imei],
                    ]
                );

                if ($this->confirm('Do you want to create this test unit in Wialon?', false)) {
                    try {
                        $result = $service->provisionUnit($unitName, $hwTypeId, $imei);
                        
                        $this->newLine();
                        $this->info('✓ Unit created successfully!');
                        $this->table(
                            ['Property', 'Value'],
                            [
                                ['Unit ID', $result['unit_id']],
                                ['Name', $result['name']],
                                ['IMEI', $result['imei']],
                            ]
                        );

                        $this->newLine();
                        $this->warn('NOTE: Please remember to delete this test unit from Wialon if it was only for testing purposes.');
                    } catch (\Throwable $th) {
                        $this->newLine();
                        $this->error('✗ Unit creation failed: ' . $th->getMessage());
                        $this->newLine();
                        $this->warn('Check the logs for more details:');
                        $this->line('php artisan log:tail');
                        return 1;
                    }
                }
            }

            $this->newLine();
            $this->info('✓ Wialon connection test completed successfully!');
            return 0;

        } catch (\Throwable $th) {
            $this->newLine();
            $this->error('✗ Test failed: ' . $th->getMessage());
            $this->newLine();
            
            if ($this->option('verbose')) {
                $this->line($th->getTraceAsString());
            }
            
            return 1;
        }
    }
}
