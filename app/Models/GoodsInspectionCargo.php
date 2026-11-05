<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsInspectionCargo extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inspection_id',
        'waybill_cargo_id',
        'cargo_description',
        'declared_quantity',
        'observed_quantity',
        'difference_quantity',
        'quantity_unit',
        'cargo_weight_kg',
        'weight_unit',
        'volume_unit',
        'goods_match_waybill',
        'illegal_shortage',
        'illegal_haulage',
    ];

    protected $casts = [
        'declared_quantity' => 'float',
        'observed_quantity' => 'float',
        'difference_quantity' => 'float',
        'cargo_weight_kg' => 'float',
    ];

    public function inspection()
    {
        return $this->belongsTo(GoodsInspection::class, 'inspection_id');
    }

    public function waybillCargo()
    {
        return $this->belongsTo(WaybillCargo::class, 'waybill_cargo_id');
    }
}
