<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaybillCargo extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'waybill_id',
        'cargo_description',
        'quantity',
        'quantity_unit',
        'cargo_weight_kg',
        'weight_unit',
        'volume_unit',
    ];

    protected $casts = [
        'quantity' => 'float',
        'cargo_weight_kg' => 'float',
    ];

    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
}
