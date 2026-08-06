<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('greenhouses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->string('location')->nullable();
            $table->string('crop_type')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->decimal('temperature_min', 8, 2)->default(16);
            $table->decimal('temperature_max', 8, 2)->default(34);
            $table->decimal('soil_humidity_min', 8, 2)->default(30);
            $table->decimal('soil_humidity_max', 8, 2)->default(60);
            $table->decimal('ambient_humidity_min', 8, 2)->default(40);
            $table->decimal('ambient_humidity_max', 8, 2)->default(80);
            $table->decimal('luminosity_min', 12, 2)->default(500);
            $table->decimal('luminosity_max', 12, 2)->default(20000);
            $table->decimal('water_level_min', 8, 2)->default(25);
            $table->boolean('automatic_irrigation')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('greenhouses');
    }
};
