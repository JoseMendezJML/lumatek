<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('lumatek:about', function (): void {
    $this->info('Lumatek - simulador de telemetría para invernaderos.');
})->purpose('Muestra información del proyecto Lumatek');
