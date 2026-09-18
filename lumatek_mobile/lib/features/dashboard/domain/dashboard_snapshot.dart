import '../../../shared/models/json_utils.dart';
import '../../../shared/models/metric_statuses.dart';
import '../../../shared/models/telemetry_reading.dart';
import '../../alerts/domain/alert.dart';
import '../../greenhouses/domain/greenhouse.dart';
import '../../irrigation/domain/irrigation_event.dart';
import '../../irrigation/domain/irrigation_schedule.dart';

/// Todo lo que la pantalla de inicio necesita, en una sola llamada:
/// GET /mobile/greenhouses/{id}/dashboard
class DashboardSnapshot {
  const DashboardSnapshot({
    required this.greenhouse,
    required this.statuses,
    this.reading,
    this.recentAlerts = const [],
    this.activeIrrigation,
    this.nextSchedule,
  });

  final Greenhouse greenhouse;
  final MetricStatuses statuses;
  final TelemetryReading? reading;
  final List<Alert> recentAlerts;
  final IrrigationEvent? activeIrrigation;
  final IrrigationSchedule? nextSchedule;

  bool get hasReading => reading != null;
  bool get isIrrigating => activeIrrigation != null;

  factory DashboardSnapshot.fromJson(Map<String, dynamic> json) {
    final rawReading = json['reading'];
    final rawAlerts = json['recent_alerts'];
    final rawIrrigation = json['active_irrigation'];
    final rawSchedule = json['next_schedule'];

    return DashboardSnapshot(
      greenhouse: Greenhouse.fromJson(asMap(json['greenhouse'])),
      statuses: MetricStatuses.fromJson(json['statuses']),
      reading: rawReading is Map
          ? TelemetryReading.fromJson(asMap(rawReading))
          : null,
      recentAlerts: rawAlerts is List
          ? rawAlerts.map((item) => Alert.fromJson(asMap(item))).toList()
          : const [],
      activeIrrigation: rawIrrigation is Map
          ? IrrigationEvent.fromJson(asMap(rawIrrigation))
          : null,
      nextSchedule: rawSchedule is Map
          ? IrrigationSchedule.fromJson(asMap(rawSchedule))
          : null,
    );
  }
}
