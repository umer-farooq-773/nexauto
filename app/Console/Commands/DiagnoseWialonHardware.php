<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WialonService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DiagnoseWialonHardware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wialon:diagnose-hardware 
                            {device_name=Concox GT06N : The GPS device name to query in Wialon} 
                            {--create-test-unit : Attempt a test core/create_unit call in Wialon and clean up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose Wialon hardware type resolution, creator permissions, and core/create_unit contract';

    protected WialonService $wialonService;

    public function __construct(WialonService $wialonService)
    {
        parent::__construct();
        $this->wialonService = $wialonService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceName = (string) $this->argument('device_name');
        $createTestUnit = (bool) $this->option('create-test-unit');

        $this->info("==================================================");
        $this->info(" WIALON HARDWARE & API DIAGNOSTIC TOOL ");
        $this->info("==================================================");
        $this->line("Target Device Name: <fg=yellow>{$deviceName}</>");
        $this->newLine();

        $host = rtrim(config('services.wialon.host'), '/');
        $token = config('services.wialon.token');

        if (empty($token)) {
            $this->error("❌ WIALON_TOKEN is not set in .env!");
            return 1;
        }

        // STEP 1: Login & Creator Resolution
        $this->info("--- STEP 1: Wialon Authentication & Creator Verification ---");
        try {
            $response = Http::timeout(30)->get($host . '/wialon/ajax.html', [
                'svc' => 'token/login',
                'params' => json_encode(['token' => $token])
            ]);

            $loginData = $response->json();
            if (!isset($loginData['eid']) || isset($loginData['error'])) {
                $errCode = $loginData['error'] ?? 'N/A';
                $this->error("❌ Login failed! Error Code: {$errCode}");
                return 1;
            }

            $sid = $loginData['eid'];
            $user = $loginData['user'] ?? [];
            $tokenInfo = $loginData['token'] ?? [];
            $tokenFlags = $tokenInfo['fl'] ?? null;
            $creatorId = (int) ($user['id'] ?? 0);
            $userName = $user['nm'] ?? 'unknown';
            $userBact = $user['bact'] ?? null;
            $userCrt = $user['crt'] ?? null;

            $this->line("✓ Logged-in User ID: <fg=green>{$creatorId}</>");
            $this->line("✓ Logged-in User Name: <fg=green>{$userName}</>");
            $this->line("✓ Account ID (bact): <fg=cyan>" . ($userBact ?? 'N/A') . "</>");
            $this->line("✓ Parent Creator (crt): <fg=cyan>" . ($userCrt ?? 'N/A') . "</>");

            if ($tokenFlags == 256) {
                $this->newLine();
                $this->error("⚠️  TOKEN PERMISSION RESTRICTION DETECTED (fl = 256 / 0x100):");
                $this->warn("   Your WIALON_TOKEN was generated with access_type=0x100 (Online Tracking Read-Only).");
                $this->warn("   While manual creation in Wialon Web UI works (because Web UI uses full access fl=-1),");
                $this->warn("   Wialon API explicitly BLOCKS unit creation (core/create_unit) for fl=256 tokens and returns error 6.");
                $this->info("   To fix, generate a Full Access Token using this URL:");
                $this->line("   https://hosting.wialon.eu/login.html?client_id=ChampsVMA&access_type=0xffff&response_type=token");
            } else {
                $this->line("✓ Token Scope Flags (fl): <fg=green>" . ($tokenFlags ?? 'Full Access (-1)') . "</>");
            }

        } catch (\Throwable $e) {
            $this->error("❌ Exception during login: " . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // STEP 2: Hardware Type Resolution
        $this->info("--- STEP 2: Hardware Type Lookup (`core/get_hw_types`) ---");
        $hwTypesResponse = Http::timeout(30)->get($host . '/wialon/ajax.html', [
            'svc' => 'core/get_hw_types',
            'sid' => $sid,
            'params' => json_encode([
                'filterType' => 'name',
                'filterValue' => [$deviceName],
                'includeType' => true,
            ])
        ])->json();

        if (empty($hwTypesResponse) || !is_array($hwTypesResponse)) {
            $this->warn("⚠️  No exact match found for hardware type '{$deviceName}' in Wialon.");
            $this->line("Searching with wildcards '*{$deviceName}*'...");

            $hwTypesResponse = Http::timeout(30)->get($host . '/wialon/ajax.html', [
                'svc' => 'core/get_hw_types',
                'sid' => $sid,
                'params' => json_encode([
                    'filterType' => 'name',
                    'filterValue' => ["*{$deviceName}*"],
                    'includeType' => true,
                ])
            ])->json();
        }

        if (empty($hwTypesResponse) || !is_array($hwTypesResponse)) {
            $this->error("❌ Hardware type '{$deviceName}' could not be found in Wialon via core/get_hw_types!");
            return 1;
        }

        $targetHw = $hwTypesResponse[0] ?? null;
        if (!$targetHw || !isset($targetHw['id'])) {
            $this->error("❌ Invalid response structure from core/get_hw_types.");
            return 1;
        }

        $realHwTypeId = (int) $targetHw['id'];
        $tpValue = $targetHw['tp'] ?? 'N/A';
        $hwName = $targetHw['name'] ?? 'N/A';
        $hwCategory = $targetHw['hw_category'] ?? 'N/A';

        $this->table(
            ['Property', 'Value', 'Notes'],
            [
                ['Device Name', $hwName, 'Matches request'],
                ['Real hwTypeId (id)', $realHwTypeId, 'CORRECT parameter for core/create_unit'],
                ['Type Protocol (tp)', $tpValue, 'Internal Wialon protocol identifier (NOT hwTypeId)'],
                ['Hardware Category', $hwCategory, 'Category type'],
            ]
        );

        $this->newLine();

        // STEP 3: Database Mapping Check
        $this->info("--- STEP 3: Database Mapping Check (`gps_device_types`) ---");
        $dbDevice = DB::table('gps_device_types')->where('name', 'like', "%{$deviceName}%")->first();

        if ($dbDevice) {
            $dbHwId = (int) $dbDevice->wialon_hw_type_id;
            $this->line("Database Record ID: {$dbDevice->id}");
            $this->line("Database Device Name: {$dbDevice->name}");
            $this->line("Database wialon_hw_type_id: " . ($dbHwId === $realHwTypeId ? "<fg=green>{$dbHwId} (CORRECT)</>" : "<fg=red>{$dbHwId} (MISMATCH - Should be {$realHwTypeId})</>"));
        } else {
            $this->warn("⚠️  No record found in local `gps_device_types` table matching '{$deviceName}'.");
        }

        $this->newLine();

        // STEP 4: Optional Unit Creation Test
        if (!$createTestUnit) {
            $this->info("--- STEP 4: Unit Creation Test ---");
            $this->line("To test a real unit creation against Wialon, re-run with option:");
            $this->line("  <fg=cyan>php artisan wialon:diagnose-hardware \"{$deviceName}\" --create-test-unit</>");
            return 0;
        }

        $this->info("--- STEP 4: Executing Test Unit Creation (`core/create_unit`) ---");
        $testUnitName = "ANTIGRAVITY-POSTMAN-TEST";
        $testImei = "359456789012348";

        $this->line("Creating test unit: <fg=yellow>{$testUnitName}</>");
        $this->line("Creator ID: <fg=yellow>{$creatorId}</>");
        $this->line("hwTypeId: <fg=yellow>{$realHwTypeId}</>");

        $createResponse = Http::timeout(30)->get($host . '/wialon/ajax.html', [
            'svc' => 'core/create_unit',
            'sid' => $sid,
            'params' => json_encode([
                'creatorId' => $creatorId,
                'name' => $testUnitName,
                'hwTypeId' => $realHwTypeId,
                'dataFlags' => 1
            ])
        ])->json();

        if (isset($createResponse['error'])) {
            $errCode = (int) $createResponse['error'];
            $this->error("❌ core/create_unit FAILED! Wialon Error Code: {$errCode}");
            if ($errCode === 6) {
                $this->error("   Explanation: Error 6 = 'Failed to fetch creator or create unit'.");
                $this->error("   Root Cause: The Wialon user '{$userName}' (ID {$creatorId}) lacks 'Create units' ACL permission or your account unit quota limit has been reached.");
            }
            return 1;
        }

        if (!isset($createResponse['item']['id'])) {
            $this->error("❌ core/create_unit response missing unit ID.");
            $this->line(json_encode($createResponse, JSON_PRETTY_PRINT));
            return 1;
        }

        $createdUnitId = (int) $createResponse['item']['id'];
        $this->info("✓ Wialon Unit Created Successfully! Real Unit ID: <fg=green>{$createdUnitId}</>");

        // Test IMEI Assignment
        $this->line("Assigning IMEI {$testImei} via `unit/update_device_type`...");
        $updateResponse = Http::timeout(30)->get($host . '/wialon/ajax.html', [
            'svc' => 'unit/update_device_type',
            'sid' => $sid,
            'params' => json_encode([
                'itemId' => $createdUnitId,
                'deviceTypeId' => $realHwTypeId,
                'uniqueId' => $testImei
            ])
        ])->json();

        $this->line("IMEI update response: " . json_encode($updateResponse));

        // Clean up test unit
        $this->line("Cleaning up test unit ID {$createdUnitId}...");
        Http::timeout(30)->get($host . '/wialon/ajax.html', [
            'svc' => 'item/delete_item',
            'sid' => $sid,
            'params' => json_encode(['itemId' => $createdUnitId])
        ]);
        $this->info("✓ Test unit deleted successfully.");

        return 0;
    }
}
