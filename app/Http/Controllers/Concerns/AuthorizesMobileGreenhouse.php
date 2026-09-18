<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Greenhouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * La app web resuelve el invernadero activo a través de la sesión
 * (ver App\Http\Controllers\Controller::greenhouse()). La API móvil es
 * "stateless" (autenticada por token Sanctum), así que el invernadero
 * siempre llega explícito en la ruta y aquí solo se valida que el
 * usuario autenticado tenga permiso para verlo/operarlo:
 * - Un admin puede acceder a cualquier invernadero.
 * - Un usuario normal solo a los invernaderos donde es responsable.
 */
trait AuthorizesMobileGreenhouse
{
    protected function authorizeGreenhouseAccess(Request $request, Greenhouse $greenhouse): void
    {
        $user = $request->user();

        abort_if(
            ! $user->isAdmin() && $greenhouse->responsible_user_id !== $user->id,
            Response::HTTP_FORBIDDEN,
            'No tienes acceso a este invernadero.'
        );
    }

    /**
     * @return Builder<Greenhouse>
     */
    protected function accessibleGreenhouses(Request $request): Builder
    {
        $user = $request->user();

        return Greenhouse::query()
            ->when(
                ! $user->isAdmin(),
                fn (Builder $query) => $query->where('responsible_user_id', $user->id)
            );
    }
}
