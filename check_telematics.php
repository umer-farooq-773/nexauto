<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$locCols = Illuminate\Support\Facades\Schema::getColumnListing('vehicle_current_locations');
echo 'vehicle_current_locations columns: ' . implode(', ', $locCols) . "\n";

$vCols = Illuminate\Support\Facades\Schema::getColumnListing('vehicles');
echo 'vehicles columns: ' . implode(', ', $vCols) . "\n";

$sampleLoc = Illuminate\Support\Facades\DB::table('vehicle_current_locations')->latest('id')->first();
echo 'Sample loc: ' . json_encode($sampleLoc, JSON_PRETTY_PRINT) . "\n";

$vehicle = App\Models\Vehicle::first();
echo 'Sample vehicle: ' . json_encode($vehicle?->only(['id', 'fleet_no', 'plate_number', 'model', 'gps_unit_id']), JSON_PRETTY_PRINT) . "\n";

