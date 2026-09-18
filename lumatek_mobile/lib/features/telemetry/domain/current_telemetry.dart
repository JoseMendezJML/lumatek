import '../../../shared/models/json_utils.dart';
import '../../../shared/models/metric_statuses.dart';
import '../../../shared/models/telemetry_reading.dart';

/// Respuesta de GET /mobile/greenhouses/{id}/telemetry/current
class CurrentTelemetry {
  const CurrentTelemetry({
    required this.reading,
    required this.statuses,
    required this.activeAlerts,
    this.greenhouseName,
  });

  final TelemetryReading reading;
  final MetricStatuses statuses;

  /// Alertas en estado new o viewed, para el badge del encabezado.
  final int activeAlerts;

  final String? greenhouseName;

  factory CurrentTelemetry.fromJson(Map<String, dynamic> json) {
    return CurrentTelemetry(
      reading: TelemetryReading.fromJson(asMap(json['reading'])),
      statuses: MetricStatuses.fromJson(json['statuses']),
      activeAlerts: asInt(json['active_alerts']),
      greenhouseName: asStringOrNull(asMap(json['greenhouse'])['name']),
    );
  }
}
