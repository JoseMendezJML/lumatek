<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationControl extends Model
{
    protected $fillable = [
        'greenhouse_id',
        'status',
        'interval_seconds',
        'variation_intensity',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'variation_intensity' => 'decimal:2',
            'last_generated_at' => 'datetime',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }
}
