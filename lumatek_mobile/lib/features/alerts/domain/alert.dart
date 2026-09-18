import '../../../shared/models/json_utils.dart';

/// Alerta generada por AlertEvaluationService. Refleja App\Models\Alert.
class Alert {
  const Alert({
    required this.id,
    required this.greenhouseId,
    required this.title,
    this.description,
    this.variable,
    required this.severity,
    required this.status,
    this.value,
    this.source,
    this.lastTriggeredAt,
    this.resolvedAt,
  });

  final int id;
  final int greenhouseId;
  final String title;
  final String? description;

  /// Métrica que disparó la alerta: temperature, soil_humidity, etc.
  final String? variable;

  /// critical | warning | info
  final String severity;

  /// new | viewed | resolved
  final String status;

  final double? value;
  final String? source;
  final DateTime? lastTriggeredAt;
  final DateTime? resolvedAt;

  bool get isNew => status == 'new';
  bool get isResolved => status == 'resolved';
  bool get isCritical => severity == 'critical';

  factory Alert.fromJson(Map<String, dynamic> json) {
    return Alert(
      id: asInt(json['id']),
      greenhouseId: asInt(json['greenhouse_id']),
      title: asStringOrNull(json['title']) ?? 'Alerta',
      description: asStringOrNull(json['description']),
      variable: asStringOrNull(json['variable']),
      severity: asStringOrNull(json['severity']) ?? 'info',
      status: asStringOrNull(json['status']) ?? 'new',
      value: asDoubleOrNull(json['value']),
      source: asStringOrNull(json['source']),
      lastTriggeredAt: asDate(json['last_triggered_at']),
      resolvedAt: asDate(json['resolved_at']),
    );
  }
}
