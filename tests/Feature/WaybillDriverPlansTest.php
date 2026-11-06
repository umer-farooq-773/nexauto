<?php

namespace Tests\Feature;

use App\Exceptions\WialonRouteSyncException;
use App\Models\DriverDetail;
use App\Models\Status;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMakeModel;
use App\Models\VehicleType;
use App\Models\Waybill;
use App\Models\WaybillDriverPlan;
use App\Services\WialonService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WaybillDriverPlansTest extends TestCase
{
    use DatabaseTransactions;

    private Vehicle $vehicle;
    private Status $awaiting;
    private Status $allocated;
    private Status $inTransit;
    private Status $returnTransit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->actingAs(new User(['first_name' => 'Waybill', 'last_name' => 'Test']));
        $this->awaiting = $this->makeStatus(0, 'Awaiting');
        $this->allocated = $this->makeStatus(1, 'Allocated');
        $this->inTransit = $this->makeStatus(2, 'In transit');
        $this->returnTransit = $this->makeStatus(5, 'Return transit');

        $make = VehicleMakeModel::create(['name' => 'Test make ' . Str::random(5)]);
        $type = VehicleType::create(['name' => 'Test type ' . Str::random(5)]);
        $this->vehicle = Vehicle::factory()->create([
            'vehicle_make_model_id' => $make->id,
            'vehicle_type_id' => $type->id,
            'gps_unit_id' => null,
        ]);
    }

    public function test_it_saves_three_drivers_in_order_and_keeps_legacy_driver_id_in_sync(): void
    {
        $drivers = [$this->newDriver(), $this->newDriver(), $this->newDriver()];
        $number = 'PLAN-' . $this->suffix();
        $this->post(route('waybill.store'), $this->payload([
            ['driver_id' => $drivers[0]->id, 'duration_minutes' => 30],
            ['driver_id' => $drivers[1]->id, 'duration_minutes' => 45],
            ['driver_id' => $drivers[2]->id, 'duration_minutes' => ''],
        ], $number))->assertRedirect(route('waybill.index'));

        $waybill = Waybill::where('waybill_number', $number)->firstOrFail();
        $plans = $waybill->driverPlans;
        $this->assertSame((string) $drivers[0]->id, (string) $waybill->driver_id);
        $this->assertSame([$drivers[0]->id, $drivers[1]->id, $drivers[2]->id], $plans->pluck('driver_id')->all());
        $this->assertSame([1800, 2700, null], $plans->pluck('planned_seconds')->all());
    }

    public function test_it_rejects_duplicate_drivers(): void
    {
        $driver = $this->newDriver();
        $this->post(route('waybill.store'), $this->payload([
            ['driver_id' => $driver->id, 'duration_minutes' => 10],
            ['driver_id' => $driver->id, 'duration_minutes' => ''],
        ]))->assertSessionHasErrors('drivers.1.driver_id');
    }

    public function test_it_requires_time_for_each_non_last_driver(): void
    {
        $this->post(route('waybill.store'), $this->payload([
            ['driver_id' => $this->newDriver()->id, 'duration_minutes' => ''],
            ['driver_id' => $this->newDriver()->id, 'duration_minutes' => ''],
        ]))->assertSessionHasErrors('drivers.0.duration_minutes');
    }

    public function test_it_allows_the_last_driver_duration_to_be_blank(): void
    {
        $drivers = [$this->newDriver(), $this->newDriver()];
        $this->post(route('waybill.store'), $this->payload([
            ['driver_id' => $drivers[0]->id, 'duration_minutes' => 25],
            ['driver_id' => $drivers[1]->id, 'duration_minutes' => ''],
        ]))->assertRedirect(route('waybill.index'));

        $this->assertDatabaseHas('waybill_driver_plans', [
            'driver_id' => $drivers[1]->id,
            'planned_seconds' => null,
        ]);
    }

    public function test_it_blocks_drivers_on_in_transit_waybills(): void
    {
        $driver = $this->newDriver();
        $busy = $this->existingWaybill($driver, $this->inTransit);
        $this->post(route('waybill.store'), $this->payload([['driver_id' => $driver->id, 'duration_minutes' => '']]))
            ->assertSessionHasErrors(['drivers']);

        $this->assertStringContainsString($busy->waybill_number, session('errors')->first('drivers'));
    }

    public function test_awaiting_waybill_conflict_is_a_warning_not_a_block(): void
    {
        $driver = $this->newDriver();
        $awaitingWaybill = $this->existingWaybill($driver, $this->awaiting);
        $number = 'PLAN-' . $this->suffix();

        $this->post(route('waybill.store'), $this->payload([['driver_id' => $driver->id, 'duration_minutes' => '']], $number))
            ->assertRedirect(route('waybill.index'))
            ->assertSessionHas('warning');

        $this->assertStringContainsString($awaitingWaybill->waybill_number, session('warning'));
        $this->assertDatabaseHas('waybills', ['waybill_number' => $number]);
    }

    public function test_update_syncs_pending_rows_and_legacy_driver_id(): void
    {
        $drivers = [$this->newDriver(), $this->newDriver(), $this->newDriver()];
        $waybill = $this->existingWaybill($drivers[0], $this->allocated);
        $waybill->driverPlans()->first()->update(['planned_seconds' => 1200]);
        $waybill->driverPlans()->create(['driver_id' => $drivers[1]->id, 'sequence' => 2, 'planned_seconds' => null, 'status' => 'pending']);

        $this->put(route('waybill.update', $waybill->id), $this->payload([
            ['driver_id' => $drivers[2]->id, 'duration_minutes' => 35],
        ], $waybill->waybill_number))->assertRedirect(route('waybill.index'));

        $waybill->refresh();
        $this->assertSame((string) $drivers[2]->id, (string) $waybill->driver_id);
        $this->assertSame([$drivers[2]->id], $waybill->driverPlans->pluck('driver_id')->all());
        $this->assertSame(2100, $waybill->driverPlans->first()->planned_seconds);
    }

    public function test_update_rejects_changes_to_non_pending_plans(): void
    {
        $drivers = [$this->newDriver(), $this->newDriver()];
        $waybill = $this->existingWaybill($drivers[0], $this->allocated);
        $plan = $waybill->driverPlans()->first();
        $plan->update(['status' => 'active']);

        $this->put(route('waybill.update', $waybill->id), $this->payload([
            ['driver_id' => $drivers[1]->id, 'duration_minutes' => ''],
        ], $waybill->waybill_number))->assertSessionHasErrors('drivers');

        $this->assertSame((string) $drivers[0]->id, (string) $plan->fresh()->driver_id);
    }

    public function test_legacy_single_driver_id_payload_creates_one_plan(): void
    {
        $driver = $this->newDriver();
        $payload = $this->payload([], 'LEGACY-' . $this->suffix());
        unset($payload['drivers']);
        $payload['driver_id'] = $driver->id;

        $this->post(route('waybill.store'), $payload)->assertRedirect(route('waybill.index'));
        $waybill = Waybill::where('waybill_number', $payload['waybill_number'])->firstOrFail();

        $this->assertSame(1, $waybill->driverPlans()->count());
        $this->assertSame((string) $driver->id, (string) $waybill->driver_id);
    }

    public function test_route_sync_failure_keeps_existing_route_id_and_reports_unassigned_unit(): void
    {
        $service = \Mockery::mock(WialonService::class)->makePartial();
        $service->shouldReceive('resolveUnitId')->once()->andReturn(601645958);
        $service->shouldReceive('getWialonResourceId')->once()->andReturn(1);
        $service->shouldReceive('createOrUpdateWialonRoute')
            ->once()
            ->andThrow(new WialonRouteSyncException('route/update_config failed', 987654321));

        $result = $service->syncWaybillRoute('601645958', [
            'waybill_number' => 'TEST-ROUTE',
            'origin' => 'Origin', 'origin_lat' => 1, 'origin_lng' => 1,
            'destination' => 'Destination', 'destination_lat' => 2, 'destination_lng' => 2,
            'wialon_route_id' => 987654321,
        ]);

        $this->assertFalse($result['success']);
        $this->assertFalse($result['unit_assigned']);
        $this->assertSame(987654321, $result['wialon_route_id']);
    }

    private function payload(array $drivers, ?string $number = null): array
    {
        return [
            'waybill_number' => $number ?? 'PLAN-' . $this->suffix(),
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $drivers[0]['driver_id'] ?? $this->newDriver()->id,
            'drivers' => $drivers,
            'status_id' => $this->allocated->id,
            'dispatch_date' => now()->toDateString(),
        ];
    }

    private function newDriver(): DriverDetail
    {
        $token = Str::upper(Str::random(10));
        return DriverDetail::create([
            'first_name' => 'Driver', 'last_name' => $token, 'address' => 'Test address',
            'phone_number' => '1234567890', 'national_id_passport_number' => 'NID-' . $token,
            'drivers_license_number' => 'DL-' . $token,
        ]);
    }

    private function existingWaybill(DriverDetail $driver, Status $status): Waybill
    {
        $waybill = Waybill::create([
            'waybill_number' => 'EXISTING-' . $this->suffix(),
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'status_id' => $status->id,
            'dispatch_date' => now()->toDateString(),
        ]);
        $waybill->driverPlans()->create([
            'driver_id' => $driver->id,
            'sequence' => 1,
            'planned_seconds' => null,
            'status' => 'pending',
        ]);

        return $waybill;
    }

    private function makeStatus(int $value, string $label): Status
    {
        return Status::firstOrCreate(
            ['value' => $value],
            ['name' => $label, 'badge_label' => $label, 'badge_class' => 'bg-secondary'],
        );
    }

    private function suffix(): string
    {
        return Str::upper(Str::random(8));
    }
}
