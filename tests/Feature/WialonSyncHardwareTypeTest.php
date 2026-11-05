<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleMakeModel;
use App\Models\VehicleType;
use App\Services\WialonReportService;
use Tests\TestCase;

class WialonSyncHardwareTypeTest extends TestCase
{
    public function test_reconcile_units_persists_hw_type_id_on_create_and_update(): void
    {
        // Ensure default make/model & type exist
        $makeModel = VehicleMakeModel::firstOrCreate(['name' => 'Default Make']);
        $vehicleType = VehicleType::firstOrCreate(['name' => 'Default Type']);

        $service = app(WialonReportService::class);

        // 1. New unit created via reconcileUnits with hw_type_id
        $unitData = [
            [
                'id'         => 601645958,
                'name'       => 'BAB-406',
                'imei'       => '352093084920192',
                'hw_type_id' => 1048698,
            ],
        ];

        $service->reconcileUnits($unitData);

        $vehicle = Vehicle::where('gps_unit_id', '601645958')->first();
        $this->assertNotNull($vehicle);
        $this->assertEquals('BAB-406', $vehicle->fleet_no);
        $this->assertEquals(1048698, (int) $vehicle->gps_hw_type_id);
        $this->assertEquals('352093084920192', $vehicle->gps_imei);

        // 2. Existing unit updated via reconcileUnits with new hw_type_id
        $updatedUnitData = [
            [
                'id'         => 601645958,
                'name'       => 'BAB-406',
                'imei'       => '352093084920192',
                'hw_type_id' => 2050000,
            ],
        ];

        $service->reconcileUnits($updatedUnitData);

        $vehicle->refresh();
        $this->assertEquals(2050000, (int) $vehicle->gps_hw_type_id);
    }
}
