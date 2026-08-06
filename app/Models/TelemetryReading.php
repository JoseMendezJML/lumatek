<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelemetryReading extends Model
{
    public const SOURCES = [
        'simulation_auto',
        'simulation_manual',
        'simulation_scenario',
        'iot',
    ];

    protected $fillable = [
        'greenhouse_id',
        'temperature',
        'soil_humidity',
        'ambient_humidity',
        'luminosity',
        'water_level',
        'irrigation_status',
        'device_status',
        'source',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'soil_humidity' => 'decimal:2',
            'ambient_humidity' => 'decimal:2',
            'luminosity' => 'decimal:2',
            'water_level' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function isSimulated(): bool
    {
        return str_starts_with($this->source, 'simulation_');
    }
}
