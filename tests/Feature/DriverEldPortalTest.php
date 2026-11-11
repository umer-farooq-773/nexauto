<?php

namespace Tests\Feature;

use App\Constants\Constants;
use App\Models\DriverDetail;
use App\Models\DriverEldLog;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMakeModel;
use App\Models\VehicleType;
use App\Models\Waybill;
use App\Models\WaybillDriverPlan;
use App\Notifications\DriverCredentialsNotification;
use App\Notifications\DriverOvertimeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class DriverEldPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $clientDirector;
    private Vehicle $vehicle;
    private Status $inTransit;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // Ensure roles exist
        Role::firstOrCreate(['name' => Constants::SUPERADMIN, 'guard_name' => 'web'], ['id' => 1, 'title' => 'Super Admin', 'color' => '#f00']);
        Role::firstOrCreate(['name' => Constants::ADMIN, 'guard_name' => 'web'], ['id' => 2, 'title' => 'Admin', 'color' => '#0f0']);
        Role::firstOrCreate(['name' => Constants::CLIENT_DIRECTORS, 'guard_name' => 'web'], ['id' => 7, 'title' => 'Client Director', 'color' => '#00f']);
        Role::firstOrCreate(['name' => Constants::DRIVER, 'guard_name' => 'web'], ['id' => 9, 'title' => 'Driver', 'color' => '#0ea5e9']);

        // Create Admin & Client Director
        $this->admin = User::create([
            'first_name'        => 'Super',
            'last_name'         => 'Admin',
            'email'             => 'admin_test_' . Str::random(5) . '@example.com',
            'phone_number'      => '+1111111111',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole(Constants::ADMIN);

        $this->clientDirector = User::create([
            'first_name'        => 'Client',
            'last_name'         => 'Director',
            'email'             => 'director_test_' . Str::random(5) . '@example.com',
            'phone_number'      => '+2222222222',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->clientDirector->assignRole(Constants::CLIENT_DIRECTORS);

        // Vehicle & Status
        $make = VehicleMakeModel::create(['name' => 'Volvo ' . Str::random(4)]);
        $type = VehicleType::create(['name' => 'Truck ' . Str::random(4)]);
        $this->vehicle = Vehicle::factory()->create([
            'vehicle_make_model_id' => $make->id,
            'vehicle_type_id'       => $type->id,
            'fleet_no'              => 'FLT-' . rand(100, 999),
            'truck_no'              => 'TRK-' . rand(100, 999),
        ]);

        $this->inTransit = Status::firstOrCreate(['value' => 2], [
            'name'        => 'in_transit',
            'badge_label' => 'In Transit',
            'badge_class' => 'bg-info',
        ]);
    }

    private function createDriver(?string $email = null): DriverDetail
    {
        $email = $email ?: 'driver_' . Str::random(6) . '@example.com';
        $user = User::create([
            'first_name'        => 'Driver',
            'last_name'         => 'Test',
            'email'             => $email,
            'phone_number'      => '+1234567890',
            'password'          => bcrypt('secret123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole(Constants::DRIVER);

        return DriverDetail::create([
            'user_id'                     => $user->id,
            'first_name'                  => 'John',
            'last_name'                   => 'Doe',
            'email'                       => $email,
            'address'                     => '123 Fleet Way',
            'phone_number'                => '+1234567890',
            'national_id_passport_number' => 'NIN-' . Str::random(8),
            'drivers_license_number'      => 'DL-' . Str::random(8),
            'status'                      => 1,
            'employer_number'             => 'EMP-1',
            'bvn_number'                  => 'BVN-' . Str::random(6),
        ]);
    }

    public function test_driver_portal_renders_single_screen_with_waybill_and_leaflet(): void
    {
        $driver = $this->createDriver();

        $waybill = Waybill::create([
            'waybill_number'   => 'WB-ELD-' . rand(1000, 9999),
            'vehicle_id'       => $this->vehicle->id,
            'driver_id'        => $driver->id,
            'status_id'        => $this->inTransit->id,
            'dispatch_date'    => now()->toDateString(),
            'origin'           => 'Origin Warehouse',
            'origin_lat'       => 33.6844,
            'origin_lng'       => 73.0479,
            'destination'      => 'Destination Depot',
            'destination_lat'  => 31.5204,
            'destination_lng'  => 74.3587,
            'checkpoints'      => [
                ['name' => 'Checkpoint 1', 'lat' => 32.5, 'lng' => 73.8]
            ],
            'estimated_duration' => 240, // 4 hours in minutes
        ]);

        WaybillDriverPlan::create([
            'waybill_id'      => $waybill->id,
            'driver_id'       => $driver->id,
            'sequence'        => 1,
            'planned_seconds' => 14400,
            'status'          => 'pending',
        ]);

        $response = $this->actingAs($driver->user)->get(route('driver.portal'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('DL-');
        $response->assertSee($waybill->waybill_number);
        $response->assertSee('leaflet-driver-map');
        $response->assertSee('countdown-timer-display');
        $response->assertSee('START DUTY');
        $response->assertSee('driverLogOffcanvas');
    }

    public function test_driver_duty_start_pause_resume_and_end_lifecycle(): void
    {
        $driver = $this->createDriver();

        $waybill = Waybill::create([
            'waybill_number'     => 'WB-' . rand(1000, 9999),
            'vehicle_id'         => $this->vehicle->id,
            'driver_id'          => $driver->id,
            'status_id'          => $this->inTransit->id,
            'dispatch_date'      => now()->toDateString(),
            'origin'             => 'City A',
            'destination'        => 'City B',
            'estimated_duration' => 240,
        ]);

        $plan = WaybillDriverPlan::create([
            'waybill_id'      => $waybill->id,
            'driver_id'       => $driver->id,
            'sequence'        => 1,
            'planned_seconds' => 14400,
            'status'          => 'pending',
        ]);

        // 1. Start Duty
        $startRes = $this->actingAs($driver->user)->postJson(route('driver.start_duty'), [
            'driver_id'  => $driver->id,
            'waybill_id' => $waybill->id,
            'latitude'   => 33.6844,
            'longitude'  => 73.0479,
        ]);
        $startRes->assertJson(['success' => true, 'status' => 'active']);
        $this->assertEquals('active', $plan->fresh()->status);
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'start_duty',
        ]);

        // 1.5 Add Remark / Note (driver comment test)
        $remarkRes = $this->actingAs($driver->user)->postJson(route('driver.add_remark'), [
            'driver_id'  => $driver->id,
            'waybill_id' => $waybill->id,
            'remark'     => 'Police inspection passed.',
            'latitude'   => 33.6844,
            'longitude'  => 73.0479,
        ]);
        $remarkRes->assertJson(['success' => true]);
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'manual_remark',
            'notes'      => 'Police inspection passed.',
        ]);

        // Also test with 'notes' key
        $remarkRes2 = $this->actingAs($driver->user)->postJson(route('driver.add_remark'), [
            'driver_id'  => $driver->id,
            'waybill_id' => $waybill->id,
            'notes'      => 'Fuel stop at petrol station.',
        ]);
        $remarkRes2->assertJson(['success' => true]);
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'manual_remark',
            'notes'      => 'Fuel stop at petrol station.',
        ]);

        // Verify getLogs returns proper structure without undefined
        $getLogsRes = $this->actingAs($driver->user)->getJson(route('driver.get_logs', ['driver_id' => $driver->id]));
        $getLogsRes->assertJson(['success' => true]);
        $this->assertNotEmpty($getLogsRes->json('logs'));
        $firstLog = $getLogsRes->json('logs.0');
        $this->assertArrayHasKey('event_label', $firstLog);
        $this->assertArrayHasKey('badge_class', $firstLog);
        $this->assertArrayHasKey('created_at', $firstLog);

        // 2. Pause Duty (Rest)
        $pauseRes = $this->actingAs($driver->user)->postJson(route('driver.pause_duty'), [
            'driver_id'       => $driver->id,
            'waybill_id'      => $waybill->id,
            'elapsed_seconds' => 1800,
        ]);
        $pauseRes->assertJson(['success' => true, 'status' => 'paused']);
        $this->assertEquals(1800, $plan->fresh()->actual_driven_seconds);
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'pause_duty',
        ]);

        // 3. Resume Duty
        $resumeRes = $this->actingAs($driver->user)->postJson(route('driver.resume_duty'), [
            'driver_id'  => $driver->id,
            'waybill_id' => $waybill->id,
        ]);
        $resumeRes->assertJson(['success' => true, 'status' => 'active']);
        $this->assertEquals('active', $plan->fresh()->status);

        // 4. End Duty
        $endRes = $this->actingAs($driver->user)->postJson(route('driver.end_duty'), [
            'driver_id'       => $driver->id,
            'waybill_id'      => $waybill->id,
            'elapsed_seconds' => 7200,
        ]);
        $endRes->assertJson(['success' => true, 'status' => 'completed']);
        $this->assertEquals('completed', $plan->fresh()->status);
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'end_duty',
        ]);
    }

    public function test_multi_driver_waybill_plan_allocation(): void
    {
        $driver1 = $this->createDriver('driver1_' . Str::random(5) . '@example.com');
        $driver2 = $this->createDriver('driver2_' . Str::random(5) . '@example.com');

        $waybill = Waybill::create([
            'waybill_number'     => 'WB-MULTI-' . rand(100, 999),
            'vehicle_id'         => $this->vehicle->id,
            'driver_id'          => $driver1->id,
            'status_id'          => $this->inTransit->id,
            'origin'             => 'Hub A',
            'destination'        => 'Hub C',
            'estimated_duration' => 360,
            'dispatch_date'      => now()->toDateString(),
        ]);

        // Driver 1: 4 hours (14400s)
        WaybillDriverPlan::create([
            'waybill_id'      => $waybill->id,
            'driver_id'       => $driver1->id,
            'sequence'        => 1,
            'planned_seconds' => 14400,
            'status'          => 'active',
        ]);

        // Driver 2: 2 hours (7200s)
        WaybillDriverPlan::create([
            'waybill_id'      => $waybill->id,
            'driver_id'       => $driver2->id,
            'sequence'        => 2,
            'planned_seconds' => 7200,
            'status'          => 'pending',
        ]);

        // When Driver 1 logs in
        $res1 = $this->actingAs($driver1->user)->get(route('driver.portal'));
        $res1->assertStatus(200);
        $res1->assertViewHas('plannedSeconds', 14400);
        $res1->assertViewHas('driverSequence', 1);

        // When Driver 2 logs in
        $res2 = $this->actingAs($driver2->user)->get(route('driver.portal'));
        $res2->assertStatus(200);
        $res2->assertViewHas('plannedSeconds', 7200);
        $res2->assertViewHas('driverSequence', 2);
    }

    public function test_overtime_violation_dispatches_notification_to_admin_director_and_driver(): void
    {
        $driver = $this->createDriver();

        $waybill = Waybill::create([
            'waybill_number'     => 'WB-OVERTIME-' . rand(100, 999),
            'vehicle_id'         => $this->vehicle->id,
            'driver_id'          => $driver->id,
            'status_id'          => $this->inTransit->id,
            'origin'             => 'Hub A',
            'destination'        => 'Hub B',
            'estimated_duration' => 120,
            'dispatch_date'      => now()->toDateString(),
        ]);

        WaybillDriverPlan::create([
            'waybill_id'      => $waybill->id,
            'driver_id'       => $driver->id,
            'sequence'        => 1,
            'planned_seconds' => 7200, // 2 hours
            'status'          => 'active',
        ]);

        // Sync timer with elapsed time exceeding planned (e.g. 7300s > 7200s)
        $res = $this->actingAs($driver->user)->postJson(route('driver.sync_timer'), [
            'driver_id'       => $driver->id,
            'waybill_id'      => $waybill->id,
            'elapsed_seconds' => 7300,
            'planned_seconds' => 7200,
            'latitude'        => 33.6844,
            'longitude'       => 73.0479,
        ]);

        $res->assertJson([
            'success'                    => true,
            'is_overtime'                => true,
            'overtime_notification_sent' => true,
        ]);

        // Assert overtime violation was logged in driver_eld_logs
        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'overtime_violation',
            'status'     => 'violation',
        ]);

        // Assert notification sent to Admin, Client Director, Driver
        Notification::assertSentTo($this->admin, DriverOvertimeNotification::class);
        Notification::assertSentTo($this->clientDirector, DriverOvertimeNotification::class);
        Notification::assertSentTo($driver->user, DriverOvertimeNotification::class);
    }

    public function test_movement_detection_recorded_when_timer_stopped(): void
    {
        $driver = $this->createDriver();

        $res = $this->actingAs($driver->user)->postJson(route('driver.movement_alert'), [
            'driver_id'  => $driver->id,
            'waybill_id' => null,
            'speed_kmh'  => 45.5,
            'latitude'   => 33.6844,
            'longitude'  => 73.0479,
        ]);

        $res->assertJson(['success' => true]);

        $this->assertDatabaseHas('driver_eld_logs', [
            'driver_id'  => $driver->id,
            'event_type' => 'movement_detected',
            'speed_kmh'  => 45.5,
        ]);
    }

    public function test_driver_user_is_strictly_restricted_from_backoffice(): void
    {
        $driver = $this->createDriver();

        // Trying to access dashboard / backoffice
        $res = $this->actingAs($driver->user)->get('/my-account');
        $res->assertRedirect(route('driver.portal'));

        // Trying to access driver management
        $res2 = $this->actingAs($driver->user)->get(route('driver.index'));
        $res2->assertRedirect(route('driver.portal'));
    }

    public function test_driver_can_logout_successfully(): void
    {
        $driver = $this->createDriver();

        $res = $this->actingAs($driver->user)->post(route('logout'));
        $res->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_driver_show_renders_eld_logs_and_waybill_breakdown(): void
    {
        $driver = $this->createDriver();

        $waybill = Waybill::create([
            'waybill_number'     => 'WB-SHOW-' . rand(100, 999),
            'vehicle_id'         => $this->vehicle->id,
            'driver_id'          => $driver->id,
            'status_id'          => $this->inTransit->id,
            'origin'             => 'Islamabad',
            'destination'        => 'Lahore',
            'estimated_duration' => 240,
            'dispatch_date'      => now()->toDateString(),
        ]);

        WaybillDriverPlan::create([
            'waybill_id'            => $waybill->id,
            'driver_id'             => $driver->id,
            'sequence'              => 1,
            'planned_seconds'       => 14400,
            'actual_driven_seconds' => 7200,
            'status'                => 'active',
        ]);

        DriverEldLog::create([
            'driver_id'        => $driver->id,
            'waybill_id'       => $waybill->id,
            'vehicle_id'       => $this->vehicle->id,
            'event_type'       => 'start_duty',
            'status'           => 'driving',
            'latitude'         => 33.6844,
            'longitude'        => 73.0479,
            'speed_kmh'        => 55.0,
            'elapsed_seconds'  => 0,
            'notes'            => 'Driver started trip from Islamabad hub.',
        ]);

        DriverEldLog::create([
            'driver_id'        => $driver->id,
            'waybill_id'       => $waybill->id,
            'vehicle_id'       => $this->vehicle->id,
            'event_type'       => 'manual_remark',
            'status'           => 'info',
            'latitude'         => 32.5000,
            'longitude'        => 73.5000,
            'notes'            => 'Fuel stop at Bhera service area.',
        ]);

        // When SuperAdmin views Driver Details
        $this->admin->assignRole(Constants::SUPERADMIN);

        $res = $this->actingAs($this->admin)->get(route('driver.show', $driver->id));
        $res->assertStatus(200);
        $res->assertSee('Total Driving Time');
        $res->assertSee('Assigned Waybill Trips');
        $res->assertSee('Overtime Violations');
        $res->assertSee('Waybill &amp; Route Operations', false);
        $res->assertSee('WB-SHOW-');
        $res->assertSee('Islamabad');
        $res->assertSee('Lahore');
        $res->assertSee('Driver started trip from Islamabad hub.');
        $res->assertSee('Fuel stop at Bhera service area.');
    }

    public function test_driver_can_sync_real_time_state_with_delivered_status(): void
    {
        $driver = $this->createDriver();

        $deliveredStatus = Status::firstOrCreate(
            ['value' => 3],
            ['name' => 'Delivered', 'badge_label' => 'Delivered', 'badge_class' => 'bg-success']
        );

        $waybill = Waybill::create([
            'waybill_number' => 'WB-SYNC-' . rand(100, 999),
            'vehicle_id'     => $this->vehicle->id,
            'driver_id'      => $driver->id,
            'status_id'      => $deliveredStatus->id,
            'origin'         => 'Islamabad',
            'destination'    => 'Karachi',
            'dispatch_date'  => now()->toDateString(),
        ]);

        $plan = WaybillDriverPlan::create([
            'waybill_id'            => $waybill->id,
            'driver_id'             => $driver->id,
            'sequence'              => 1,
            'planned_seconds'       => 14400,
            'actual_driven_seconds' => 744,
            'status'                => 'completed',
        ]);

        $res = $this->actingAs($driver->user)->getJson(route('driver.sync_state', [
            'driver_id'  => $driver->id,
            'waybill_id' => $waybill->id,
        ]));

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'waybill' => [
                'id'           => $waybill->id,
                'status_value' => 3,
                'status_label' => 'Delivered',
                'is_delivered' => true,
                'is_completed' => true,
            ],
            'driver_plan' => [
                'id'              => $plan->id,
                'planned_seconds' => 14400,
            ],
        ]);
    }

    public function test_driver_history_page_renders_successfully(): void
    {
        $driver = $this->createDriver();

        $waybill = Waybill::create([
            'waybill_number' => 'WB-HIST-01',
            'vehicle_id'     => $this->vehicle->id,
            'driver_id'      => $driver->id,
            'status_id'      => $this->inTransit->id,
            'origin'         => 'Lahore',
            'destination'    => 'Peshawar',
            'dispatch_date'  => now()->toDateString(),
        ]);

        WaybillDriverPlan::create([
            'waybill_id'            => $waybill->id,
            'driver_id'             => $driver->id,
            'sequence'              => 1,
            'planned_seconds'       => 14400,
            'actual_driven_seconds' => 3600,
            'status'                => 'completed',
        ]);

        $response = $this->actingAs($driver->user)->get(route('driver.history'));

        $response->assertStatus(200);
        $response->assertSee('Driver History & Activity Log', false);
        $response->assertSee('WB-HIST-01');
        $response->assertSee('Lahore');
        $response->assertSee('Peshawar');
        $response->assertSee('Total Trips');
    }

    public function test_driver_history_logs_endpoint_returns_json(): void
    {
        $driver = $this->createDriver();

        DriverEldLog::create([
            'driver_id'        => $driver->id,
            'vehicle_id'       => $this->vehicle->id,
            'event_type'       => 'start_duty',
            'status'           => 'driving',
            'latitude'         => 31.5204,
            'longitude'        => 74.3587,
            'speed_kmh'        => 45,
            'elapsed_seconds'  => 120,
            'notes'            => 'Trip started',
        ]);

        $response = $this->actingAs($driver->user)->getJson(route('driver.history_logs'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'notes' => 'Trip started',
        ]);
    }

    public function test_driver_portal_delivered_waybill_hides_duty_controls_and_start_modal(): void
    {
        $driver = $this->createDriver();

        $deliveredStatus = Status::firstOrCreate(
            ['value' => 3],
            ['name' => 'Delivered', 'badge_label' => 'Delivered', 'badge_class' => 'bg-success']
        );

        $waybill = Waybill::create([
            'waybill_number' => 'WB-DELIVERED-01',
            'vehicle_id'     => $this->vehicle->id,
            'driver_id'      => $driver->id,
            'status_id'      => $deliveredStatus->id,
            'origin'         => 'Islamabad',
            'destination'    => 'Karachi',
            'dispatch_date'  => now()->toDateString(),
        ]);

        WaybillDriverPlan::create([
            'waybill_id'            => $waybill->id,
            'driver_id'             => $driver->id,
            'sequence'              => 1,
            'planned_seconds'       => 14400,
            'actual_driven_seconds' => 7200,
            'status'                => 'completed',
        ]);

        $response = $this->actingAs($driver->user)->get(route('driver.portal'));

        $response->assertStatus(200);
        $response->assertSee('TRIP DELIVERED');
        $response->assertSee('WAYBILL DELIVERED');
        $response->assertSee('Delivered');
        // Initial start modal should NOT be rendered when waybill is delivered
        $response->assertDontSee('id="initialStartModal"', false);
        // Start duty button should have d-none class when waybill is delivered
        $response->assertSee('btn-duty-start d-none');
    }
}
