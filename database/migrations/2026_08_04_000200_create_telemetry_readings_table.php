<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature', 8, 2);
            $table->decimal('soil_humidity', 8, 2);
            $table->decimal('ambient_humidity', 8, 2);
            $table->decimal('luminosity', 12, 2);
            $table->decimal('water_level', 8, 2);
            $table->string('irrigation_status', 20)->default('inactive');
            $table->string('device_status', 20)->default('connected');
            $table->string('source', 40)->index();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['greenhouse_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_readings');
    }
};
