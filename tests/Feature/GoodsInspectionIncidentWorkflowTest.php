<?php

namespace Tests\Feature;

use App\Exceptions\InspectionIncidentsRequiredException;
use App\Models\BreathaliserTest;
use App\Models\DriverDetail;
use App\Models\DriverMisconduct;
use App\Models\GoodsInspection;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\RouteDiversion;
use App\Models\Status;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Waybill;
use App\Notifications\GoodsInspectionCompletedNotification;
use App\Repositories\GoodsInspectionRepository;
use App\Services\InspectionIncidentRequirementService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class GoodsInspectionIncidentWorkflowTest extends TestCase
{
    protected User $user;
    protected Vehicle $vehicle;
    protected DriverDetail $driver;
    protected Waybill $waybill;
    protected InspectionIncidentRequirementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InspectionIncidentRequirementService::class);

        // Ensure status 2 exists for waybills
        $status = Status::firstOrCreate(['value' => 2], ['name' => 'Active']);

        $this->user = User::first() ?? User::create([
            'first_name' => 'John',
            'last_name' => 'Inspector',
            'email' => 'inspector_' . Str::random(6) . '@example.com',
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
            'emergency_contact_phone_number' => '0987654321',
            'status' => '1',
        ]);

        $this->vehicle = Vehicle::first() ?? Vehicle::create([
            'fleet_no' => 'FL-999',
            'truck_no' => 'TRK-999',
            'status' => '1',
        ]);

        $this->waybill = Waybill::first() ?? Waybill::create([
            'waybill_number' => 'WB-12345',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status_id' => $status->id,
            'quantity' => 50,
            'origin' => 'Port A',
            'destination' => 'Warehouse B',
        ]);
    }

    public function test_shortage_and_goods_mismatch_trigger_illegal_shortage(): void
    {
        // Declared 50, Observed 40 => Shortage
        $findings = [
            'declared_quantity' => 50,
            'observed_quantity' => 40,
            'goods_match_waybill' => 'No',
        ];

        $required = $this->service->getRequiredIncidents($findings);
        $this->assertContains(InspectionIncidentRequirementService::ILLEGAL_SHORTAGE, $required);
        $this->assertNotContains(InspectionIncidentRequirementService::ILLEGAL_HAULAGE, $required);
    }

    public function test_excess_quantity_and_unauthorised_items_trigger_illegal_haulage(): void
    {
        // Declared 50, Observed 60 => Excess
        $findings = [
            'declared_quantity' => 50,
            'observed_quantity' => 60,
            'unauthorised_items' => 'Yes',
        ];

        $required = $this->service->getRequiredIncidents($findings);
        $this->assertContains(InspectionIncidentRequirementService::ILLEGAL_HAULAGE, $required);
        $this->assertNotContains(InspectionIncidentRequirementService::ILLEGAL_SHORTAGE, $required);
    }

    public function test_doors_insecure_and_cargo_insecure_trigger_driver_misconduct(): void
    {
        $findings = [
            'doors_tarpaulin_secure' => 'No',
            'cargo_secured' => 'No',
        ];

        $required = $this->service->getRequiredIncidents($findings);
        $this->assertContains(InspectionIncidentRequirementService::DRIVER_MISCONDUCT, $required);
    }

    public function test_route_diversion_and_breathaliser_triggers(): void
    {
        $findings = [
            'route_diversion' => 'Yes',
            'breathaliser_test' => 'Failed',
        ];

        $required = $this->service->getRequiredIncidents($findings);
        $this->assertContains(InspectionIncidentRequirementService::ROUTE_DIVERSION, $required);
        $this->assertContains(InspectionIncidentRequirementService::BREATHALISER, $required);
    }

    public function test_create_incident_stores_record_with_inspection_id(): void
    {
        $inspection = GoodsInspection::create([
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester',
            'declared_quantity' => '50',
            'observed_quantity' => '35',
            'goods_match_waybill' => 'No',
            'illegal_shortage' => 'Yes',
            'goods_status' => 'issues',
            'status' => '0',
            'created_by' => $this->user->id,
        ]);

        $res = $this->service->createIncident($inspection, InspectionIncidentRequirementService::ILLEGAL_SHORTAGE);

        $this->assertTrue($res['created']);
        $this->assertInstanceOf(IllegalShortage::class, $res['record']);
        $this->assertEquals($inspection->id, $res['record']->inspection_id);
        $this->assertEquals(15, $res['record']->shortage_quantity);

        // Subsequent call returns existing record without duplicating
        $res2 = $this->service->createIncident($inspection, InspectionIncidentRequirementService::ILLEGAL_SHORTAGE);
        $this->assertFalse($res2['created']);
        $this->assertEquals($res['record']->id, $res2['record']->id);
    }

    public function test_completing_inspection_auto_creates_missing_required_incidents_on_save(): void
    {
        $repo = app(GoodsInspectionRepository::class);

        // Has shortage and unauth items => requires illegal_shortage and illegal_haulage
        // When saved with status = 1, repository automatically creates both incidents!
        $dto = new \App\Dto\GoodsInspectionDto([
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester',
            'declared_quantity' => '50',
            'observed_quantity' => '40',
            'goods_match_waybill' => 'No',
            'unauthorised_items' => 'Yes',
            'illegal_shortage' => 'Yes',
            'illegal_haulage' => 'Yes',
            'goods_status' => 'issues',
            'status' => '1',
            'created_by' => $this->user->id,
        ]);

        $inspection = $repo->store($dto);

        $this->assertNotNull($inspection);
        $this->assertEquals('1', (string) $inspection->status);

        // Verify both required incidents were auto-created and linked
        $shortage = IllegalShortage::where('inspection_id', $inspection->id)->first();
        $this->assertNotNull($shortage);

        $haulage = IllegalHaulage::where('inspection_id', $inspection->id)->first();
        $this->assertNotNull($haulage);
    }

    public function test_completing_inspection_succeeds_when_clean_or_all_incidents_created(): void
    {
        $repo = app(GoodsInspectionRepository::class);

        // 1. Clean inspection without issues
        $cleanDto = new \App\Dto\GoodsInspectionDto([
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester',
            'declared_quantity' => '50',
            'observed_quantity' => '50',
            'goods_match_waybill' => 'Yes',
            'cargo_secured' => 'Yes',
            'doors_tarpaulin_secure' => 'Yes',
            'unauthorised_items' => 'No',
            'route_diversion' => 'No',
            'illegal_haulage' => 'No',
            'illegal_shortage' => 'No',
            'breathaliser_test' => 'Passed',
            'driver_misconduct' => 'No',
            'goods_status' => 'cleared',
            'status' => '1',
            'created_by' => $this->user->id,
        ]);

        $created = $repo->store($cleanDto);
        $this->assertNotNull($created);
        $this->assertEquals('1', (string) $created->status);
    }

    public function test_notification_dispatches_when_inspection_completed(): void
    {
        Notification::fake();

        $inspection = GoodsInspection::create([
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester',
            'declared_quantity' => '50',
            'observed_quantity' => '50',
            'goods_match_waybill' => 'Yes',
            'goods_status' => 'cleared',
            'status' => '1',
            'created_by' => $this->user->id,
        ]);

        app(\App\Services\IncidentWaybillNotificationService::class)->notifyGoodsInspectionCompleted($inspection);

        Notification::assertSentTo(
            $this->user,
            GoodsInspectionCompletedNotification::class
        );
    }

    public function test_saving_draft_as_completed_updates_existing_inspection_without_duplication(): void
    {
        $repo = app(GoodsInspectionRepository::class);

        // 1. Create a draft inspection record
        $draft = GoodsInspection::create([
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester Draft',
            'declared_quantity' => '50',
            'observed_quantity' => '50',
            'goods_match_waybill' => 'Yes',
            'goods_status' => 'cleared',
            'status' => '0',
            'created_by' => $this->user->id,
        ]);

        $initialCount = GoodsInspection::count();

        // 2. Submit form payload with inspection_id matching the draft and status = 1
        $dto = new \App\Dto\GoodsInspectionDto([
            'inspection_id' => $draft->id,
            'vehicle_id' => $this->vehicle->id,
            'waybill_id' => $this->waybill->id,
            'fleet_no' => 'FL-999',
            'inspection_date' => now()->toDateString(),
            'inspection_type' => 'Pre-Departure',
            'inspector_name' => 'Tester Updated',
            'declared_quantity' => '50',
            'observed_quantity' => '50',
            'goods_match_waybill' => 'Yes',
            'goods_status' => 'cleared',
            'status' => '1',
            'remarks' => 'Completed report',
            'created_by' => $this->user->id,
        ]);

        $saved = $repo->store($dto);

        // 3. Verify no new record was created, but draft was updated to completed
        $this->assertEquals($initialCount, GoodsInspection::count());
        $this->assertEquals($draft->id, $saved->id);
        $this->assertEquals('1', (string) $saved->status);
        $this->assertEquals('Tester Updated', $saved->inspector_name);
        $this->assertEquals('Completed report', $saved->remarks);
    }
}

