<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$waybillCols = Schema::getColumnListing('waybills');
$inspectionCols = Schema::getColumnListing('goods_inspections');
$hasWaybillCargos = Schema::hasTable('waybill_cargos');
$hasInspectionCargos = Schema::hasTable('goods_inspection_cargos');

echo "waybill_cargos table exists: " . ($hasWaybillCargos ? "YES" : "NO") . "\n";
echo "goods_inspection_cargos table exists: " . ($hasInspectionCargos ? "YES" : "NO") . "\n";
echo "waybills columns:\n" . implode(', ', $waybillCols) . "\n";
echo "goods_inspections columns:\n" . implode(', ', $inspectionCols) . "\n";
