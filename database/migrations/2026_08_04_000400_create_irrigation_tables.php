<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained()->cascadeOnDelete();
            $table->time('time');
            $table->unsignedInteger('duration_minutes')->default(20);
            $table->json('days')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('irrigation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->string('status', 20)->default('running')->index();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(20);
            $table->decimal('humidity_before', 8, 2)->nullable();
            $table->decimal('humidity_after', 8, 2)->nullable();
            $table->string('source', 40)->default('simulation_manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['greenhouse_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_events');
        Schema::dropIfExists('irrigation_schedules');
    }
};
