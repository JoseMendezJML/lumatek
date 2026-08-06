<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertRule extends Model
{
    protected $fillable = [
        'greenhouse_id',
        'name',
        'variable',
        'severity',
        'operator',
        'min_value',
        'max_value',
        'comparison_value',
        'title',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }
}
