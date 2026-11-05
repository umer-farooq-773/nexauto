<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WialonReportTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'wialon_resource_id',
        'wialon_template_id',
        'name',
        'type',
        'synced_at',
        'status'
    ];

    protected $casts = [
        'wialon_resource_id' => 'integer',
        'wialon_template_id' => 'integer',
        'synced_at' => 'datetime',
    ];

    protected $appends = [
        'normalized_type',
        'object_type',
    ];

    /**
     * Scope active templates
     */
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    /**
     * Get normalized template type ('unit', 'unit_group', 'resource', 'user', 'driver', etc.)
     */
    public function getNormalizedTypeAttribute(): string
    {
        $raw = strtolower(trim((string) $this->type));

        return match ($raw) {
            'avl_unit_group', 'unit_group', 'group' => 'unit_group',
            'avl_unit', 'unit', 'vehicle' => 'unit',
            'avl_resource', 'resource', 'account' => 'resource',
            'avl_user', 'user' => 'user',
            'avl_driver', 'driver' => 'driver',
            'avl_route', 'route' => 'route',
            default => $raw !== '' ? $raw : 'unit',
        };
    }

    /**
     * Alias for normalized type to clearly describe the target object type
     */
    public function getObjectTypeAttribute(): string
    {
        return $this->normalized_type;
    }

    /**
     * Determine if template is a unit/vehicle template
     */
    public function getIsUnitTypeAttribute(): bool
    {
        return $this->normalized_type === 'unit';
    }

    /**
     * Determine if template is a unit group template
     */
    public function getIsGroupTypeAttribute(): bool
    {
        return $this->normalized_type === 'unit_group';
    }
}
