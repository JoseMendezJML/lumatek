<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('variable', 50)->index();
            $table->string('severity', 20);
            $table->string('operator', 20);
            $table->decimal('min_value', 12, 2)->nullable();
            $table->decimal('max_value', 12, 2)->nullable();
            $table->string('comparison_value')->nullable();
            $table->string('title');
            $table->text('description');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('greenhouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telemetry_reading_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('alert_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('variable', 50);
            $table->string('title');
            $table->text('description');
            $table->string('severity', 20)->index();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->string('source', 40);
            $table->string('fingerprint')->index();
            $table->timestamp('last_triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['greenhouse_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_rules');
    }
};
