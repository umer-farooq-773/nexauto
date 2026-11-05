<?php

namespace Tests\Feature;

use App\Models\DriverDetail;
use App\Models\GoodsInspection;
use App\Models\GoodsInspectionCargo;
use App\Models\Status;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Waybill;
use App\Models\WaybillCargo;
use App\Services\InspectionIncidentRequirementService;
use Illuminate\Support\Str;
use Tests\TestCase;

class MultiCargoWaybillAndInspectionTest extends TestCase
{
    protected User $user;
    protected Vehicle $vehicle;
    protected DriverDetail $driver;
    protected Status $status;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->status = Status::first() ?? Status::create([
            'name' => 'Active',
            'value' => 2,
            'badge_class' => 'badge bg-success',
            'badge_label' => 'Active'
        ]);

        $roles = [
            \App\Constants\Constants::SUPERADMIN => 'Superadmin',
            \App\Constants\Constants::ADMIN => 'Admin',
            \App\Constants\Constants::CHAMPS_MANAGEMENT => 'Champs Management',
            \App\Constants\Constants::CLIENT_MANAGEMENT => 'Client Management',
            \App\Constants\Constants::CLIENT_DIRECTORS => 'Client Directors',
        ];
        foreach ($roles as $roleName => $roleTitle) {
            \App\Models\Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['color' => '#007bff', 'title' => $roleTitle]
            );
        }

        $this->user = User::first() ?? User::create([
            'first_name' => 'John',
            'last_name' => 'Inspector',
            'email' => 'tester_' . Str::random(6) . '@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($this->user);

        $this->driver = DriverDetail::first() ?? DriverDetail::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '1234567890',
            'address' => '123 Test Street',
            'national_id_passport_number' => 'NAT-123456',
            'date_of_birth' => '1990-01-01',
            'date_employed' => '2020-01-01',
            'drivers_license_number' => 'DL-123456',
            'license_category_class' => 'C',
            'license_expiry_date' => '2030-01-01',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_relationship' => 'Spouse',
            'emergency_contact_phone' => '0987654321',
        ]);

        $makeModel = \App\Models\VehicleMakeModel::first() ?? \App\Models\VehicleMakeModel::create(['name' => 'Volvo FH16']);
        $type = \App\Models\VehicleType::first() ?? \App\Models\VehicleType::create(['name' => 'Truck']);

        $this->vehicle = Vehicle::first() ?? Vehicle::factory()->create([
            'vehicle_make_model_id' => $makeModel->id,
            'vehicle_type_id' => $type->id,
        ]);
    }

    public function test_waybill_stores_and_updates_multiple_cargo_items()
    {
        $uniqueNumber = 'WB-M-' . Str::random(6);

        // 1. Store waybill with 2 cargo items
        $response = $this->post(route('waybill.store'), [
            'waybill_number' => $uniqueNumber,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status_id' => $this->status->id,
            'dispatch_date' => now()->format('Y-m-d'),
            'origin' => 'Lagos',
            'destination' => 'Abuja',
            'cargos' => [
                [
                    'cargo_description' => 'Premium Rice Bags',
                    'quantity' => 150,
                    'quantity_unit' => 'Bags',
                    'cargo_weight' => 7500,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                ],
                [
                    'cargo_description' => 'Refined Sugar Bags',
                    'quantity' => 80,
                    'quantity_unit' => 'Bags',
                    'cargo_weight' => 4000,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                ]
            ]
        ]);

        $response->assertRedirect(route('waybill.index'));
        $waybill = Waybill::where('waybill_number', $uniqueNumber)->with('cargos')->firstOrFail();

        $this->assertCount(2, $waybill->cargos);
        $this->assertEquals('Premium Rice Bags', $waybill->cargos[0]->cargo_description);
        $this->assertEquals(150, $waybill->cargos[0]->quantity);
        $this->assertEquals('Refined Sugar Bags', $waybill->cargos[1]->cargo_description);
        $this->assertEquals(80, $waybill->cargos[1]->quantity);

        // 2. Update waybill: modify cargo 1 and add cargo 3
        $cargo1Id = $waybill->cargos[0]->id;
        $responseUpdate = $this->put(route('waybill.update', $waybill->id), [
            'waybill_number' => $uniqueNumber,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status_id' => $this->status->id,
            'dispatch_date' => now()->format('Y-m-d'),
            'origin' => 'Lagos',
            'destination' => 'Abuja',
            'cargos' => [
                [
                    'id' => $cargo1Id,
                    'cargo_description' => 'Premium Rice Bags (Updated)',
                    'quantity' => 160,
                    'quantity_unit' => 'Bags',
                    'cargo_weight' => 8000,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                ],
                [
                    'cargo_description' => 'Vegetable Oil Cartons',
                    'quantity' => 50,
                    'quantity_unit' => 'Cartons',
                    'cargo_weight' => 1000,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                ]
            ]
        ]);

        $responseUpdate->assertRedirect(route('waybill.index'));
        $waybill->refresh();
        $waybill->load('cargos');

        $this->assertCount(2, $waybill->cargos);
        $this->assertEquals('Premium Rice Bags (Updated)', $waybill->cargos[0]->cargo_description);
        $this->assertEquals(160, $waybill->cargos[0]->quantity);
        $this->assertEquals('Vegetable Oil Cartons', $waybill->cargos[1]->cargo_description);
    }

    public function test_goods_inspection_evaluates_and_stores_multi_cargo_discrepancies()
    {
        $uniqueNumber = 'WB-M-' . Str::random(6);

        $waybill = Waybill::create([
            'waybill_number' => $uniqueNumber,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status_id' => $this->status->id,
            'dispatch_date' => now()->format('Y-m-d'),
            'cargo_description' => 'Rice & Beans',
            'quantity' => 200,
            'quantity_unit' => 'Bags',
            'cargo_weight_kg' => 10000,
            'weight_unit' => 'kg',
            'volume_unit' => 'm3',
        ]);

        $cargoRice = WaybillCargo::create([
            'waybill_id' => $waybill->id,
            'cargo_description' => 'Rice 50kg Bags',
            'quantity' => 100,
            'quantity_unit' => 'Bags',
            'cargo_weight' => 5000,
            'weight_unit' => 'kg',
            'volume_unit' => 'm3',
        ]);

        $cargoBeans = WaybillCargo::create([
            'waybill_id' => $waybill->id,
            'cargo_description' => 'Beans 50kg Bags',
            'quantity' => 100,
            'quantity_unit' => 'Bags',
            'cargo_weight' => 5000,
            'weight_unit' => 'kg',
            'volume_unit' => 'm3',
        ]);

        // Inspection payload: Rice has shortage (90 observed vs 100 declared), Beans has excess haulage (110 observed vs 100 declared)
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $waybill->id,
            'waybill_number' => $waybill->waybill_number,
            'inspector_name' => 'John Inspector',
            'inspection_date' => now()->format('Y-m-d'),
            'inspection_type' => 'Pre-Departure',
            'goods_status' => 'issues',
            'status' => '0', // draft
            'cargos' => [
                [
                    'waybill_cargo_id' => $cargoRice->id,
                    'cargo_description' => 'Rice 50kg Bags',
                    'declared_quantity' => 100,
                    'observed_quantity' => 90,
                    'difference_quantity' => 10,
                    'quantity_unit' => 'Bags',
                    'cargo_weight' => 5000,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                    'goods_match_waybill' => 'No',
                    'illegal_shortage' => 'Yes',
                    'illegal_haulage' => 'No',
                    'remarks' => '10 bags missing',
                ],
                [
                    'waybill_cargo_id' => $cargoBeans->id,
                    'cargo_description' => 'Beans 50kg Bags',
                    'declared_quantity' => 100,
                    'observed_quantity' => 110,
                    'difference_quantity' => 10,
                    'quantity_unit' => 'Bags',
                    'cargo_weight' => 5000,
                    'weight_unit' => 'kg',
                    'volume_unit' => 'm3',
                    'goods_match_waybill' => 'Yes',
                    'illegal_shortage' => 'No',
                    'illegal_haulage' => 'Yes',
                    'remarks' => '10 extra bags loaded',
                ],
            ]
        ];

        // Evaluate via Service
        $service = app(InspectionIncidentRequirementService::class);
        $tempInspection = new GoodsInspection($payload);
        $evaluation = $service->evaluate($tempInspection, $payload);

        $keys = array_column($evaluation['incidents'], 'key');
        $this->assertContains('illegal_shortage', $keys, 'Must detect shortage incident requirement');
        $this->assertContains('illegal_haulage', $keys, 'Must detect haulage incident requirement');

        // Create linked incidents via AJAX endpoint
        $shortageResponse = $this->postJson(route('goods_inspection.create_incident'), array_merge($payload, [
            'incident_type' => 'illegal_shortage'
        ]));
        if ($shortageResponse->status() !== 200) {
            dump($shortageResponse->json());
        }
        $shortageResponse->assertOk();
        $this->assertTrue($shortageResponse->json('created'));

        $haulageResponse = $this->postJson(route('goods_inspection.create_incident'), array_merge($payload, [
            'incident_id' => $shortageResponse->json('inspection_id'),
            'inspection_id' => $shortageResponse->json('inspection_id'),
            'incident_type' => 'illegal_haulage'
        ]));
        $haulageResponse->assertOk();
        $this->assertTrue($haulageResponse->json('created'));

        // Save Completed Inspection
        $inspectionId = $shortageResponse->json('inspection_id');
        $finalPayload = array_merge($payload, [
            'inspection_id' => $inspectionId,
            'status' => '1',
        ]);

        $finalResponse = $this->put(route('goods_inspection.update', $inspectionId), $finalPayload);
        $finalResponse->assertRedirect(route('goods_inspection.index'));

        $inspection = GoodsInspection::with('cargos')->findOrFail($inspectionId);
        $this->assertCount(2, $inspection->cargos);
        $this->assertEquals('Rice 50kg Bags', $inspection->cargos[0]->cargo_description);
        $this->assertEquals(90, $inspection->cargos[0]->observed_quantity);
        $this->assertEquals('Yes', $inspection->cargos[0]->illegal_shortage);
        $this->assertEquals('Beans 50kg Bags', $inspection->cargos[1]->cargo_description);
        $this->assertEquals(110, $inspection->cargos[1]->observed_quantity);
        $this->assertEquals('Yes', $inspection->cargos[1]->illegal_haulage);
    }
}
