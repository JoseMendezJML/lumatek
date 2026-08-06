<?php

namespace Database\Seeders;

use App\Models\AlertRule;
use App\Models\Greenhouse;
use App\Models\IrrigationSchedule;
use App\Models\Role;
use App\Models\SimulationControl;
use App\Models\SimulationScenario;
use App\Models\TelemetryReading;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador']
        );

        $userRole = Role::query()->firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'Usuario']
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@lumatek.test'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Javier Guillén',
                'phone' => '9610000000',
                'password' => Hash::make('Lumatek123!'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'usuario@lumatek.test'],
            [
                'role_id' => $userRole->id,
                'name' => 'Usuario Demostración',
                'phone' => '9610000001',
                'password' => Hash::make('Lumatek123!'),
                'email_verified_at' => now(),
                'active' => true,
            ]
        );

        $greenhouse = Greenhouse::query()->updateOrCreate(
            ['code' => 'INV-001'],
            [
                'responsible_user_id' => $admin->id,
                'name' => 'Invernadero 1',
                'location' => 'Área de demostración',
                'crop_type' => 'Tomate',
                'status' => 'active',
                'temperature_min' => 16,
                'temperature_max' => 34,
                'soil_humidity_min' => 30,
                'soil_humidity_max' => 60,
                'ambient_humidity_min' => 40,
                'ambient_humidity_max' => 80,
                'luminosity_min' => 500,
                'luminosity_max' => 20000,
                'water_level_min' => 25,
                'automatic_irrigation' => true,
            ]
        );

        $rules = [
            [
                'name' => 'Temperatura críticamente baja',
                'variable' => 'temperature',
                'severity' => 'critical',
                'operator' => 'lt',
                'min_value' => 10,
                'max_value' => null,
                'title' => 'Temperatura crítica baja',
                'description' => 'La temperatura está por debajo del límite crítico.',
            ],
            [
                'name' => 'Temperatura baja',
                'variable' => 'temperature',
                'severity' => 'warning',
                'operator' => 'between',
                'min_value' => 10,
                'max_value' => 15,
                'title' => 'Temperatura baja',
                'description' => 'La temperatura está por debajo del rango óptimo.',
            ],
            [
                'name' => 'Temperatura alta',
                'variable' => 'temperature',
                'severity' => 'warning',
                'operator' => 'between',
                'min_value' => 35,
                'max_value' => 39.99,
                'title' => 'Temperatura elevada',
                'description' => 'La temperatura excede el rango óptimo.',
            ],
            [
                'name' => 'Temperatura crítica alta',
                'variable' => 'temperature',
                'severity' => 'critical',
                'operator' => 'gte',
                'min_value' => 40,
                'max_value' => null,
                'title' => 'Temperatura crítica',
                'description' => 'La temperatura alcanzó un nivel crítico.',
            ],
            [
                'name' => 'Humedad del suelo crítica',
                'variable' => 'soil_humidity',
                'severity' => 'critical',
                'operator' => 'lt',
                'min_value' => 20,
                'max_value' => null,
                'title' => 'Humedad del suelo crítica',
                'description' => 'La humedad del suelo está en un nivel crítico.',
            ],
            [
                'name' => 'Humedad del suelo baja',
                'variable' => 'soil_humidity',
                'severity' => 'warning',
                'operator' => 'between',
                'min_value' => 20,
                'max_value' => 29.99,
                'title' => 'Humedad del suelo baja',
                'description' => 'La humedad está por debajo del nivel ideal.',
            ],
            [
                'name' => 'Humedad del suelo elevada',
                'variable' => 'soil_humidity',
                'severity' => 'warning',
                'operator' => 'gt',
                'min_value' => 60,
                'max_value' => null,
                'title' => 'Humedad del suelo elevada',
                'description' => 'La humedad del suelo supera el rango recomendado.',
            ],
            [
                'name' => 'Nivel de agua crítico',
                'variable' => 'water_level',
                'severity' => 'critical',
                'operator' => 'lt',
                'min_value' => 10,
                'max_value' => null,
                'title' => 'Depósito de agua crítico',
                'description' => 'El depósito tiene muy poca agua.',
            ],
            [
                'name' => 'Nivel de agua bajo',
                'variable' => 'water_level',
                'severity' => 'warning',
                'operator' => 'between',
                'min_value' => 10,
                'max_value' => 25,
                'title' => 'Nivel de agua bajo',
                'description' => 'El depósito necesita ser abastecido pronto.',
            ],
            [
                'name' => 'Humedad ambiental alta',
                'variable' => 'ambient_humidity',
                'severity' => 'warning',
                'operator' => 'between',
                'min_value' => 80.01,
                'max_value' => 89.99,
                'title' => 'Humedad ambiental elevada',
                'description' => 'La humedad ambiental supera el rango recomendado.',
            ],
            [
                'name' => 'Humedad ambiental crítica',
                'variable' => 'ambient_humidity',
                'severity' => 'critical',
                'operator' => 'gte',
                'min_value' => 90,
                'max_value' => null,
                'title' => 'Humedad ambiental crítica',
                'description' => 'La humedad ambiental alcanzó un nivel crítico.',
            ],
            [
                'name' => 'Riego activo',
                'variable' => 'irrigation_status',
                'severity' => 'info',
                'operator' => 'equals',
                'min_value' => null,
                'max_value' => null,
                'comparison_value' => 'active',
                'title' => 'Riego simulado activo',
                'description' => 'El sistema de riego se encuentra en ejecución.',
            ],
            [
                'name' => 'Falla de riego',
                'variable' => 'irrigation_status',
                'severity' => 'critical',
                'operator' => 'equals',
                'min_value' => null,
                'max_value' => null,
                'comparison_value' => 'fault',
                'title' => 'Falla del sistema de riego',
                'description' => 'El sistema de riego simulado reportó una falla.',
            ],
            [
                'name' => 'Dispositivo desconectado',
                'variable' => 'device_status',
                'severity' => 'critical',
                'operator' => 'equals',
                'min_value' => null,
                'max_value' => null,
                'comparison_value' => 'disconnected',
                'title' => 'Pérdida de conexión',
                'description' => 'El dispositivo simulado se encuentra desconectado.',
            ],
        ];

        foreach ($rules as $rule) {
            AlertRule::query()->updateOrCreate(
                ['greenhouse_id' => null, 'name' => $rule['name']],
                array_merge($rule, ['greenhouse_id' => null, 'active' => true])
            );
        }

        $scenarios = [
            [
                'name' => 'Condiciones normales',
                'slug' => 'normal',
                'description' => 'Valores estables dentro de los rangos recomendados.',
                'values' => [
                    'temperature' => 28.5,
                    'soil_humidity' => 45,
                    'ambient_humidity' => 65,
                    'luminosity' => 1200,
                    'water_level' => 80,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Suelo seco',
                'slug' => 'dry-soil',
                'description' => 'Simula una caída crítica de la humedad del suelo.',
                'values' => [
                    'temperature' => 33,
                    'soil_humidity' => 18,
                    'ambient_humidity' => 43,
                    'luminosity' => 9000,
                    'water_level' => 70,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Temperatura elevada',
                'slug' => 'high-temperature',
                'description' => 'Simula calor crítico dentro del invernadero.',
                'values' => [
                    'temperature' => 42,
                    'soil_humidity' => 34,
                    'ambient_humidity' => 45,
                    'luminosity' => 15000,
                    'water_level' => 65,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Humedad ambiental elevada',
                'slug' => 'high-ambient-humidity',
                'description' => 'Simula exceso de humedad en el ambiente.',
                'values' => [
                    'temperature' => 27,
                    'soil_humidity' => 50,
                    'ambient_humidity' => 94,
                    'luminosity' => 700,
                    'water_level' => 74,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Depósito de agua bajo',
                'slug' => 'low-water',
                'description' => 'Simula un depósito cercano a quedar vacío.',
                'values' => [
                    'temperature' => 30,
                    'soil_humidity' => 32,
                    'ambient_humidity' => 55,
                    'luminosity' => 5000,
                    'water_level' => 8,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Falla del sistema de riego',
                'slug' => 'irrigation-failure',
                'description' => 'Simula un riego que no puede activarse.',
                'values' => [
                    'temperature' => 36,
                    'soil_humidity' => 22,
                    'ambient_humidity' => 42,
                    'luminosity' => 8500,
                    'water_level' => 70,
                    'irrigation_status' => 'fault',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Riego activo',
                'slug' => 'irrigation-active',
                'description' => 'Simula el sistema de riego en ejecución.',
                'values' => [
                    'temperature' => 29,
                    'soil_humidity' => 38,
                    'ambient_humidity' => 61,
                    'luminosity' => 3200,
                    'water_level' => 68,
                    'irrigation_status' => 'active',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Lluvia próxima',
                'slug' => 'rain-soon',
                'description' => 'Simula condiciones previas a lluvia.',
                'values' => [
                    'temperature' => 24,
                    'soil_humidity' => 44,
                    'ambient_humidity' => 84,
                    'luminosity' => 450,
                    'water_level' => 75,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'connected',
                ],
            ],
            [
                'name' => 'Pérdida de conexión',
                'slug' => 'disconnected',
                'description' => 'Simula un dispositivo sin comunicación.',
                'values' => [
                    'temperature' => 28,
                    'soil_humidity' => 45,
                    'ambient_humidity' => 65,
                    'luminosity' => 1200,
                    'water_level' => 80,
                    'irrigation_status' => 'inactive',
                    'device_status' => 'disconnected',
                ],
            ],
        ];

        foreach ($scenarios as $scenario) {
            SimulationScenario::query()->updateOrCreate(
                ['slug' => $scenario['slug']],
                array_merge($scenario, ['active' => true])
            );
        }

        SimulationControl::query()->firstOrCreate(
            ['greenhouse_id' => $greenhouse->id],
            [
                'status' => 'stopped',
                'interval_seconds' => 10,
                'variation_intensity' => 1,
            ]
        );

        IrrigationSchedule::query()->firstOrCreate(
            ['greenhouse_id' => $greenhouse->id, 'time' => '08:00:00'],
            [
                'duration_minutes' => 20,
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'active' => true,
            ]
        );

        IrrigationSchedule::query()->firstOrCreate(
            ['greenhouse_id' => $greenhouse->id, 'time' => '14:00:00'],
            [
                'duration_minutes' => 20,
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'active' => true,
            ]
        );

        if (! TelemetryReading::query()->where('greenhouse_id', $greenhouse->id)->exists()) {
            TelemetryReading::query()->create([
                'greenhouse_id' => $greenhouse->id,
                'temperature' => 28.5,
                'soil_humidity' => 45,
                'ambient_humidity' => 65,
                'luminosity' => 1200,
                'water_level' => 80,
                'irrigation_status' => 'inactive',
                'device_status' => 'connected',
                'source' => 'simulation_scenario',
                'recorded_at' => now(),
            ]);
        }
    }
}
