<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $incident   The incident record (Accident, Impoundment, etc.)
     * @param  string  $incidentType    e.g. 'accident', 'vehicle_breakdown'
     * @param  string  $incidentLabel   e.g. 'Accident', 'Vehicle Breakdown'
     * @param  string  $oldStatus       Status value before change
     * @param  string  $newStatus       Status value after change
     * @param  string  $showUrl         Direct URL to the incident show page
     * @param  \App\Models\Waybill|null  $waybill  Associated waybill (if any)
     */
    public function __construct(
        public readonly mixed  $incident,
        public readonly string $incidentType,
        public readonly string $incidentLabel,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        public readonly string $showUrl,
        public readonly mixed  $waybill = null,
    ) {}
}
