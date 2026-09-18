<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesMobileGreenhouse;
use App\Models\Greenhouse;
use App\Models\IrrigationSchedule;
use App\Services\ActivityLogger;
use App\Services\IrrigationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileIrrigationController extends Controller
{
    use AuthorizesMobileGreenhouse;

    public function status(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $event = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        return response()->json([
            'active' => (bool) $event,
            'event' => $event,
            'automatic_irrigation' => $greenhouse->automatic_irrigation,
        ]);
    }

    public function start(Request $request, Greenhouse $greenhouse, IrrigationService $service): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $data = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        $event = $service->start(
            $greenhouse,
            (int) $data['duration_minutes'],
            $request->user()
        );

        return response()->json(['message' => 'Riego iniciado.', 'event' => $event], 201);
    }

    public function stop(Request $request, Greenhouse $greenhouse, IrrigationService $service): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $event = $service->stop($greenhouse, $request->user());

        return response()->json([
            'message' => $event ? 'Riego detenido.' : 'No había un riego activo.',
            'event' => $event,
        ]);
    }

    public function history(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        return response()->json(
            $greenhouse->irrigationEvents()
                ->latest('started_at')
                ->paginate(min(50, max(5, $request->integer('per_page', 15))))
        );
    }

    public function toggleAutomatic(Request $request, Greenhouse $greenhouse, ActivityLogger $logger): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $greenhouse->update([
            'automatic_irrigation' => ! $greenhouse->automatic_irrigation,
        ]);

        $logger->log(
            'irrigation.automatic_toggled',
            'Se cambió el estado del riego automático desde la app móvil.',
            $greenhouse,
            ['enabled' => $greenhouse->automatic_irrigation],
            $request->user()
        );

        return response()->json([
            'message' => 'Estado del riego automático actualizado.',
            'automatic_irrigation' => $greenhouse->automatic_irrigation,
        ]);
    }

    public function schedules(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        return response()->json([
            'schedules' => $greenhouse->irrigationSchedules()->orderBy('time')->get(),
        ]);
    }

    public function storeSchedule(Request $request, Greenhouse $greenhouse, ActivityLogger $logger): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $data = $request->validate([
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'days' => ['nullable', 'array'],
            'days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
        ]);

        $schedule = $greenhouse->irrigationSchedules()->create([
            'time' => $data['time'],
            'duration_minutes' => $data['duration_minutes'],
            'days' => $data['days'] ?? [],
            'active' => true,
        ]);

        $logger->log(
            'irrigation.schedule_created',
            'Se creó un horario de riego desde la app móvil.',
            $schedule,
            [],
            $request->user()
        );

        return response()->json(['message' => 'Horario agregado.', 'schedule' => $schedule], 201);
    }

    public function toggleSchedule(Request $request, IrrigationSchedule $schedule): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $schedule->greenhouse);

        $schedule->update(['active' => ! $schedule->active]);

        return response()->json(['message' => 'Horario actualizado.', 'schedule' => $schedule]);
    }

    public function destroySchedule(Request $request, IrrigationSchedule $schedule): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $schedule->greenhouse);

        $schedule->delete();

        return response()->json(['message' => 'Horario eliminado.']);
    }
}
