<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'greenhouse_id',
        'telemetry_reading_id',
        'alert_rule_id',
        'variable',
        'title',
        'description',
        'severity',
        'value',
        'status',
        'source',
        'fingerprint',
        'last_triggered_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'last_triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(TelemetryReading::class, 'telemetry_reading_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'alert_rule_id');
    }
}
