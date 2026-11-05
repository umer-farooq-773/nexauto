<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WialonUnitGroup extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'wialon_group_id',
        'name',
        'units_count',
        'synced_at',
        'status',
    ];

    protected $casts = [
        'wialon_group_id' => 'integer',
        'units_count'     => 'integer',
        'synced_at'       => 'datetime',
    ];

    /**
     * Scope active unit groups
     */
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
