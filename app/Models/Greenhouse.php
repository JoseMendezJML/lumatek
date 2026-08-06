<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Greenhouse extends Model
{
    protected $fillable = [
        'responsible_user_id',
        'name',
        'code',
        'location',
        'crop_type',
        'status',
        'temperature_min',
        'temperature_max',
        'soil_humidity_min',
        'soil_humidity_max',
        'ambient_humidity_min',
        'ambient_humidity_max',
        'luminosity_min',
        'luminosity_max',
        'water_level_min',
        'automatic_irrigation',
    ];

    protected function casts(): array
    {
        return [
            'temperature_min' => 'decimal:2',
            'temperature_max' => 'decimal:2',
            'soil_humidity_min' => 'decimal:2',
            'soil_humidity_max' => 'decimal:2',
            'ambient_humidity_min' => 'decimal:2',
            'ambient_humidity_max' => 'decimal:2',
            'luminosity_min' => 'decimal:2',
            'luminosity_max' => 'decimal:2',
            'water_level_min' => 'decimal:2',
            'automatic_irrigation' => 'boolean',
        ];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(TelemetryReading::class);
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(TelemetryReading::class)->latestOfMany('recorded_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function irrigationEvents(): HasMany
    {
        return $this->hasMany(IrrigationEvent::class);
    }

    public function irrigationSchedules(): HasMany
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    public function simulationControl(): HasOne
    {
        return $this->hasOne(SimulationControl::class);
    }
}
