import '../../../shared/models/json_utils.dart';
import '../../../shared/models/telemetry_reading.dart';

/// Invernadero. Refleja App\Models\Greenhouse.
///
/// El listado de /mobile/greenhouses trae además la última lectura y el
/// estado del riego automático; el payload reducido del login solo trae los
/// datos de identificación, por eso casi todo es opcional.
class Greenhouse {
  const Greenhouse({
    required this.id,
    required this.name,
    required this.code,
    this.location,
    this.cropType,
    this.status,
    this.automaticIrrigation = false,
    this.latestReading,
    this.responsibleName,
    this.temperatureMin,
    this.temperatureMax,
    this.soilHumidityMin,
    this.soilHumidityMax,
    this.ambientHumidityMin,
    this.ambientHumidityMax,
    this.luminosityMin,
    this.luminosityMax,
    this.waterLevelMin,
  });

  final int id;
  final String name;
  final String code;
  final String? location;
  final String? cropType;
  final String? status;
  final bool automaticIrrigation;
  final TelemetryReading? latestReading;
  final String? responsibleName;

  final double? temperatureMin;
  final double? temperatureMax;
  final double? soilHumidityMin;
  final double? soilHumidityMax;
  final double? ambientHumidityMin;
  final double? ambientHumidityMax;
  final double? luminosityMin;
  final double? luminosityMax;
  final double? waterLevelMin;

  bool get isActive => status == 'active';

  /// Rango configurado para una métrica, para mostrar "18 – 28 °C" junto al valor.
  (double?, double?) rangeFor(String metric) => switch (metric) {
        'temperature' => (temperatureMin, temperatureMax),
        'soil_humidity' => (soilHumidityMin, soilHumidityMax),
        'ambient_humidity' => (ambientHumidityMin, ambientHumidityMax),
        'luminosity' => (luminosityMin, luminosityMax),
        'water_level' => (waterLevelMin, null),
        _ => (null, null),
      };

  Greenhouse copyWith({bool? automaticIrrigation}) {
    return Greenhouse(
      id: id,
      name: name,
      code: code,
      location: location,
      cropType: cropType,
      status: status,
      automaticIrrigation: automaticIrrigation ?? this.automaticIrrigation,
      latestReading: latestReading,
      responsibleName: responsibleName,
      temperatureMin: temperatureMin,
      temperatureMax: temperatureMax,
      soilHumidityMin: soilHumidityMin,
      soilHumidityMax: soilHumidityMax,
      ambientHumidityMin: ambientHumidityMin,
      ambientHumidityMax: ambientHumidityMax,
      luminosityMin: luminosityMin,
      luminosityMax: luminosityMax,
      waterLevelMin: waterLevelMin,
    );
  }

  factory Greenhouse.fromJson(Map<String, dynamic> json) {
    final rawReading = json['latest_reading'];
    final rawResponsible = json['responsible'];

    return Greenhouse(
      id: asInt(json['id']),
      name: asStringOrNull(json['name']) ?? 'Invernadero',
      code: asStringOrNull(json['code']) ?? '',
      location: asStringOrNull(json['location']),
      cropType: asStringOrNull(json['crop_type']),
      status: asStringOrNull(json['status']),
      automaticIrrigation: asBool(json['automatic_irrigation']),
      latestReading: rawReading is Map
          ? TelemetryReading.fromJson(asMap(rawReading))
          : null,
      responsibleName: rawResponsible is Map
          ? asStringOrNull(asMap(rawResponsible)['name'])
          : null,
      temperatureMin: asDoubleOrNull(json['temperature_min']),
      temperatureMax: asDoubleOrNull(json['temperature_max']),
      soilHumidityMin: asDoubleOrNull(json['soil_humidity_min']),
      soilHumidityMax: asDoubleOrNull(json['soil_humidity_max']),
      ambientHumidityMin: asDoubleOrNull(json['ambient_humidity_min']),
      ambientHumidityMax: asDoubleOrNull(json['ambient_humidity_max']),
      luminosityMin: asDoubleOrNull(json['luminosity_min']),
      luminosityMax: asDoubleOrNull(json['luminosity_max']),
      waterLevelMin: asDoubleOrNull(json['water_level_min']),
    );
  }
}
