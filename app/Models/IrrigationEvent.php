<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationEvent extends Model
{
    protected $fillable = [
        'greenhouse_id',
        'initiated_by',
        'type',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'humidity_before',
        'humidity_after',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'humidity_before' => 'decimal:2',
            'humidity_after' => 'decimal:2',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
