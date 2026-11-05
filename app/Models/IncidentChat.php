<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentChat extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'incident_id',
        'incident_type',
        'user_id',
        'role_type',
        'message',
        'attachment',
    ];

    /**
     * The user who sent this message.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: messages for a specific incident.
     */
    public function scopeForIncident($query, string $incidentType, string $incidentId)
    {
        return $query->where('incident_type', $incidentType)
                     ->where('incident_id', $incidentId);
    }
}
