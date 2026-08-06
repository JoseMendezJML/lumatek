<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_scenarios', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('values');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('simulation_controls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('stopped');
            $table->unsignedInteger('interval_seconds')->default(10);
            $table->decimal('variation_intensity', 8, 2)->default(1);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_controls');
        Schema::dropIfExists('simulation_scenarios');
    }
};
