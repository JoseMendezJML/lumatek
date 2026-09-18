import '../../../shared/models/json_utils.dart';

/// Respuesta de GET /mobile/greenhouses/{id}/reports?days=N
class ReportSummary {
  const ReportSummary({
    required this.days,
    required this.from,
    required this.to,
    required this.readings,
    required this.irrigations,
    required this.alerts,
    this.temperatureAvg,
    this.soilHumidityAvg,
    this.ambientHumidityAvg,
    this.luminosityAvg,
    this.greenhouseName,
  });

  final int days;
  final DateTime? from;
  final DateTime? to;

  final int readings;
  final int irrigations;
  final int alerts;

  final double? temperatureAvg;
  final double? soilHumidityAvg;
  final double? ambientHumidityAvg;
  final double? luminosityAvg;

  final String? greenhouseName;

  bool get hasData => readings > 0;

  factory ReportSummary.fromJson(Map<String, dynamic> json, int days) {
    final range = asMap(json['range']);
    final telemetry = asMap(json['telemetry']);

    return ReportSummary(
      days: days,
      from: asDate(range['from']),
      to: asDate(range['to']),
      readings: asInt(telemetry['readings']),
      irrigations: asInt(json['irrigations']),
      alerts: asInt(json['alerts']),
      temperatureAvg: asDoubleOrNull(telemetry['temperature_avg']),
      soilHumidityAvg: asDoubleOrNull(telemetry['soil_humidity_avg']),
      ambientHumidityAvg: asDoubleOrNull(telemetry['ambient_humidity_avg']),
      luminosityAvg: asDoubleOrNull(telemetry['luminosity_avg']),
      greenhouseName: asStringOrNull(asMap(json['greenhouse'])['name']),
    );
  }
}
