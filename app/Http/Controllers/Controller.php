<?php

namespace App\Http\Controllers;

use App\Models\Greenhouse;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function greenhouse(Request $request): Greenhouse
    {
        $requestedId = $request->integer('greenhouse_id');

        if ($requestedId && Greenhouse::query()->whereKey($requestedId)->exists()) {
            $request->session()->put('greenhouse_id', $requestedId);
        }

        $greenhouseId = $request->session()->get('greenhouse_id');

        return Greenhouse::query()
            ->when($greenhouseId, fn ($query) => $query->whereKey($greenhouseId))
            ->first()
            ?? Greenhouse::query()->firstOrFail();
    }
}
