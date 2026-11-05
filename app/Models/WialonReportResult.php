<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WialonReportResult extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'wialon_unit_id',
        'wialon_report_template_id',
        'interval_from',
        'interval_to',
        'status',
        'error_message',
        'export_path',
        'export_format',
    ];

    protected $casts = [
        'interval_from' => 'datetime',
        'interval_to'   => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(WialonReportTemplate::class, 'wialon_report_template_id');
    }
}
