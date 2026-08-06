<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationSchedule extends Model
{
    protected $fillable = [
        'greenhouse_id',
        'time',
        'duration_minutes',
        'days',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'active' => 'boolean',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }
}
