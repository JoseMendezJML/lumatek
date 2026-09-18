import 'json_utils.dart';

/// Lectura de sensores. Refleja App\Models\TelemetryReading.
class TelemetryReading {
  const TelemetryReading({
    required this.id,
    required this.temperature,
    required this.soilHumidity,
    required this.ambientHumidity,
    required this.luminosity,
    required this.waterLevel,
    this.irrigationStatus,
    this.deviceStatus,
    this.source,
    this.recordedAt,
  });

  final int id;
  final double temperature;
  final double soilHumidity;
  final double ambientHumidity;
  final double luminosity;
  final double waterLevel;
  final String? irrigationStatus;
  final String? deviceStatus;
  final String? source;
  final DateTime? recordedAt;

  bool get isSimulated => source?.startsWith('simulation_') ?? false;
  bool get isConnected => deviceStatus == 'connected';

  /// Acceso por clave, para recorrer las métricas junto con sus estados.
  double metric(String key) => switch (key) {
        'temperature' => temperature,
        'soil_humidity' => soilHumidity,
        'ambient_humidity' => ambientHumidity,
        'luminosity' => luminosity,
        'water_level' => waterLevel,
        _ => 0,
      };

  static String unitFor(String key) => switch (key) {
        'temperature' => '°C',
        'soil_humidity' => '%',
        'ambient_humidity' => '%',
        'luminosity' => 'lux',
        'water_level' => '%',
        _ => '',
      };

  factory TelemetryReading.fromJson(Map<String, dynamic> json) {
    return TelemetryReading(
      id: asInt(json['id']),
      temperature: asDouble(json['temperature']),
      soilHumidity: asDouble(json['soil_humidity']),
      ambientHumidity: asDouble(json['ambient_humidity']),
      luminosity: asDouble(json['luminosity']),
      waterLevel: asDouble(json['water_level']),
      irrigationStatus: asStringOrNull(json['irrigation_status']),
      deviceStatus: asStringOrNull(json['device_status']),
      source: asStringOrNull(json['source']),
      recordedAt: asDate(json['recorded_at']),
    );
  }
}
