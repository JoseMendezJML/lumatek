<?php

use App\Http\Controllers\AlertApiController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GreenhouseController;
use App\Http\Controllers\IrrigationApiController;
use App\Http\Controllers\IrrigationController;
use App\Http\Controllers\ReportApiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulatorApiController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\TelemetryApiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/control', [ControlController::class, 'index'])->name('control.index');
    Route::patch('/control/automatic-irrigation', [ControlController::class, 'toggleAutomatic'])
        ->name('control.automatic');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::patch('/alerts/{alert}/viewed', [AlertController::class, 'viewed'])->name('alerts.viewed');
    Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

    Route::get('/irrigation', [IrrigationController::class, 'index'])->name('irrigation.index');
    Route::post('/irrigation/start', [IrrigationController::class, 'start'])->name('irrigation.start');
    Route::post('/irrigation/stop', [IrrigationController::class, 'stop'])->name('irrigation.stop');
    Route::post('/irrigation/schedules', [IrrigationController::class, 'storeSchedule'])
        ->name('irrigation.schedules.store');
    Route::patch('/irrigation/schedules/{schedule}', [IrrigationController::class, 'toggleSchedule'])
        ->name('irrigation.schedules.toggle');
    Route::delete('/irrigation/schedules/{schedule}', [IrrigationController::class, 'destroySchedule'])
        ->name('irrigation.schedules.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    Route::middleware('admin')->group(function (): void {
        Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator.index');
        Route::post('/simulator/manual', [SimulatorController::class, 'manual'])->name('simulator.manual');
        Route::post('/simulator/scenarios/{scenario}', [SimulatorController::class, 'scenario'])
            ->name('simulator.scenario');
        Route::post('/simulator/start', [SimulatorController::class, 'start'])->name('simulator.start');
        Route::post('/simulator/pause', [SimulatorController::class, 'pause'])->name('simulator.pause');
        Route::post('/simulator/stop', [SimulatorController::class, 'stop'])->name('simulator.stop');
        Route::post('/simulator/reset', [SimulatorController::class, 'reset'])->name('simulator.reset');

        Route::resource('users', UserController::class)->except('show');
        Route::resource('greenhouses', GreenhouseController::class)->except('show');
        Route::get('/alert-rules', [AlertRuleController::class, 'index'])->name('alert-rules.index');
        Route::put('/alert-rules/{alertRule}', [AlertRuleController::class, 'update'])
            ->name('alert-rules.update');
    });

    // API interna: usa la sesión web y mantiene protección CSRF para operaciones de escritura.
    Route::prefix('api')->name('api.')->group(function (): void {
        Route::get('/telemetry/current', [TelemetryApiController::class, 'current'])
            ->name('telemetry.current');
        Route::get('/telemetry/history', [TelemetryApiController::class, 'history'])
            ->name('telemetry.history');
        Route::get('/alerts', [AlertApiController::class, 'index'])->name('alerts.index');
        Route::patch('/alerts/{alert}/resolve', [AlertApiController::class, 'resolve'])
            ->name('alerts.resolve');
        Route::get('/irrigation/status', [IrrigationApiController::class, 'status'])
            ->name('irrigation.status');
        Route::post('/irrigation/start', [IrrigationApiController::class, 'start'])
            ->name('irrigation.start');
        Route::post('/irrigation/stop', [IrrigationApiController::class, 'stop'])
            ->name('irrigation.stop');
        Route::get('/irrigation/history', [IrrigationApiController::class, 'history'])
            ->name('irrigation.history');
        Route::get('/reports', ReportApiController::class)->name('reports');

        Route::middleware('admin')->group(function (): void {
            Route::post('/simulator/readings', [SimulatorApiController::class, 'reading'])
                ->name('simulator.readings');
            Route::post('/simulator/scenarios/{scenario}', [SimulatorApiController::class, 'scenario'])
                ->name('simulator.scenarios');
            Route::post('/simulator/start', [SimulatorApiController::class, 'start'])
                ->name('simulator.start');
            Route::post('/simulator/stop', [SimulatorApiController::class, 'stop'])
                ->name('simulator.stop');
        });
    });
});
