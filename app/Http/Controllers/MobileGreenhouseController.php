<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesMobileGreenhouse;
use App\Models\Greenhouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileGreenhouseController extends Controller
{
    use AuthorizesMobileGreenhouse;

    public function index(Request $request): JsonResponse
    {
        $greenhouses = $this->accessibleGreenhouses($request)
            ->with('latestReading')
            ->orderBy('name')
            ->get()
            ->map(fn (Greenhouse $greenhouse): array => $this->summary($greenhouse));

        return response()->json(['greenhouses' => $greenhouses]);
    }

    public function show(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        return response()->json([
            'greenhouse' => $greenhouse->load('latestReading', 'responsible'),
        ]);
    }

    private function summary(Greenhouse $greenhouse): array
    {
        return [
            'id' => $greenhouse->id,
            'name' => $greenhouse->name,
            'code' => $greenhouse->code,
            'location' => $greenhouse->location,
            'crop_type' => $greenhouse->crop_type,
            'status' => $greenhouse->status,
            'automatic_irrigation' => $greenhouse->automatic_irrigation,
            'latest_reading' => $greenhouse->latestReading,
        ];
    }
}
