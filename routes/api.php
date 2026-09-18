<?php

use App\Http\Controllers\MobileAlertController;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\MobileDashboardController;
use App\Http\Controllers\MobileGreenhouseController;
use App\Http\Controllers\MobileIrrigationController;
use App\Http\Controllers\MobileReportController;
use App\Http\Controllers\MobileTelemetryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API móvil (Lumatek)
|--------------------------------------------------------------------------
|
| Comparte la misma base de datos y los mismos modelos/servicios que la
| aplicación web, pero es completamente "stateless": se autentica con
| tokens Sanctum (Bearer) en lugar de sesión, y el invernadero activo
| siempre viaja explícito en la URL (no en sesión), validado en cada
| endpoint por el trait AuthorizesMobileGreenhouse.
|
*/

Route::prefix('mobile')->name('mobile.')->group(function (): void {
    Route::post('/login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [MobileAuthController::class, 'me'])->name('me');
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');

        Route::get('/greenhouses', [MobileGreenhouseController::class, 'index'])
            ->name('greenhouses.index');
        Route::get('/greenhouses/{greenhouse}', [MobileGreenhouseController::class, 'show'])
            ->name('greenhouses.show');

        Route::get('/greenhouses/{greenhouse}/dashboard', MobileDashboardController::class)
            ->name('greenhouses.dashboard');

        Route::get('/greenhouses/{greenhouse}/telemetry/current', [MobileTelemetryController::class, 'current'])
            ->name('greenhouses.telemetry.current');
        Route::get('/greenhouses/{greenhouse}/telemetry/history', [MobileTelemetryController::class, 'history'])
            ->name('greenhouses.telemetry.history');

        Route::get('/greenhouses/{greenhouse}/alerts', [MobileAlertController::class, 'index'])
            ->name('greenhouses.alerts.index');
        Route::patch('/alerts/{alert}/viewed', [MobileAlertController::class, 'viewed'])
            ->name('alerts.viewed');
        Route::patch('/alerts/{alert}/resolve', [MobileAlertController::class, 'resolve'])
            ->name('alerts.resolve');

        Route::get('/greenhouses/{greenhouse}/irrigation/status', [MobileIrrigationController::class, 'status'])
            ->name('greenhouses.irrigation.status');
        Route::post('/greenhouses/{greenhouse}/irrigation/start', [MobileIrrigationController::class, 'start'])
            ->name('greenhouses.irrigation.start');
        Route::post('/greenhouses/{greenhouse}/irrigation/stop', [MobileIrrigationController::class, 'stop'])
            ->name('greenhouses.irrigation.stop');
        Route::get('/greenhouses/{greenhouse}/irrigation/history', [MobileIrrigationController::class, 'history'])
            ->name('greenhouses.irrigation.history');
        Route::patch('/greenhouses/{greenhouse}/irrigation/automatic', [MobileIrrigationController::class, 'toggleAutomatic'])
            ->name('greenhouses.irrigation.automatic');

        Route::get('/greenhouses/{greenhouse}/irrigation/schedules', [MobileIrrigationController::class, 'schedules'])
            ->name('greenhouses.irrigation.schedules.index');
        Route::post('/greenhouses/{greenhouse}/irrigation/schedules', [MobileIrrigationController::class, 'storeSchedule'])
            ->name('greenhouses.irrigation.schedules.store');
        Route::patch('/irrigation/schedules/{schedule}', [MobileIrrigationController::class, 'toggleSchedule'])
            ->name('irrigation.schedules.toggle');
        Route::delete('/irrigation/schedules/{schedule}', [MobileIrrigationController::class, 'destroySchedule'])
            ->name('irrigation.schedules.destroy');

        Route::get('/greenhouses/{greenhouse}/reports', MobileReportController::class)
            ->name('greenhouses.reports');
    });
});
