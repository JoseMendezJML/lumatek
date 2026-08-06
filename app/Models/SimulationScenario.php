<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationScenario extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'values',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'values' => 'array',
            'active' => 'boolean',
        ];
    }
}
