<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'value',
        'badge_class',
        'badge_label',
        'description',
    ];

    public function waybills()
    {
        return $this->hasMany(Waybill::class);
    }
}
