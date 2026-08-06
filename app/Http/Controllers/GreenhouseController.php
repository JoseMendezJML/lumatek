<?php

namespace App\Http\Controllers;

use App\Models\Greenhouse;
use App\Models\SimulationControl;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GreenhouseController extends Controller
{
    public function index(): View
    {
        $greenhouses = Greenhouse::query()
            ->with(['responsible', 'latestReading'])
            ->latest()
            ->paginate(12);

        return view('greenhouses.index', compact('greenhouses'));
    }

    public function create(): View
    {
        $users = User::query()->where('active', true)->orderBy('name')->get();

        return view('greenhouses.create', compact('users'));
    }

    public function store(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $greenhouse = Greenhouse::query()->create($request->validate($this->rules()));

        SimulationControl::query()->create([
            'greenhouse_id' => $greenhouse->id,
            'status' => 'stopped',
            'interval_seconds' => 10,
            'variation_intensity' => 1,
        ]);

        $logger->log(
            'greenhouse.created',
            'Se registró un invernadero.',
            $greenhouse,
            [],
            $request->user()
        );

        return redirect()->route('greenhouses.index')->with('success', 'Invernadero registrado.');
    }

    public function edit(Greenhouse $greenhouse): View
    {
        $users = User::query()->where('active', true)->orderBy('name')->get();

        return view('greenhouses.edit', compact('greenhouse', 'users'));
    }

    public function update(
        Request $request,
        Greenhouse $greenhouse,
        ActivityLogger $logger
    ): RedirectResponse {
        $greenhouse->update($request->validate($this->rules($greenhouse)));

        $logger->log(
            'greenhouse.updated',
            'Se actualizó un invernadero.',
            $greenhouse,
            [],
            $request->user()
        );

        return redirect()->route('greenhouses.index')->with('success', 'Invernadero actualizado.');
    }

    public function destroy(Request $request, Greenhouse $greenhouse): RedirectResponse
    {
        if (Greenhouse::query()->count() <= 1) {
            return back()->with('warning', 'Debe existir al menos un invernadero.');
        }

        $greenhouse->delete();

        if ($request->session()->get('greenhouse_id') === $greenhouse->id) {
            $request->session()->forget('greenhouse_id');
        }

        return back()->with('success', 'Invernadero eliminado.');
    }

    private function rules(?Greenhouse $greenhouse = null): array
    {
        return [
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('greenhouses', 'code')->ignore($greenhouse?->id),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'crop_type' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'temperature_min' => ['required', 'numeric', 'between:-20,80'],
            'temperature_max' => ['required', 'numeric', 'gt:temperature_min', 'between:-20,80'],
            'soil_humidity_min' => ['required', 'numeric', 'between:0,100'],
            'soil_humidity_max' => ['required', 'numeric', 'gt:soil_humidity_min', 'between:0,100'],
            'ambient_humidity_min' => ['required', 'numeric', 'between:0,100'],
            'ambient_humidity_max' => ['required', 'numeric', 'gt:ambient_humidity_min', 'between:0,100'],
            'luminosity_min' => ['required', 'numeric', 'min:0'],
            'luminosity_max' => ['required', 'numeric', 'gt:luminosity_min', 'max:100000'],
            'water_level_min' => ['required', 'numeric', 'between:0,100'],
            'automatic_irrigation' => ['nullable', 'boolean'],
        ];
    }
}
