import '../../../shared/models/json_utils.dart';

/// Evento de riego. Refleja App\Models\IrrigationEvent.
class IrrigationEvent {
  const IrrigationEvent({
    required this.id,
    required this.greenhouseId,
    this.type,
    this.status,
    this.startedAt,
    this.endedAt,
    this.durationMinutes,
    this.humidityBefore,
    this.humidityAfter,
    this.source,
    this.notes,
  });

  final int id;
  final int greenhouseId;

  /// manual | automatic | scheduled
  final String? type;

  /// running | completed | cancelled
  final String? status;

  final DateTime? startedAt;
  final DateTime? endedAt;
  final int? durationMinutes;
  final double? humidityBefore;
  final double? humidityAfter;
  final String? source;
  final String? notes;

  bool get isRunning => status == 'running';

  /// Cuánto lleva corriendo, para la cuenta regresiva del panel de riego.
  Duration? get elapsed {
    if (startedAt == null) return null;
    final end = endedAt ?? DateTime.now();
    return end.difference(startedAt!);
  }

  /// Tiempo restante estimado según la duración solicitada.
  Duration? get remaining {
    if (!isRunning || startedAt == null || durationMinutes == null) return null;
    final total = Duration(minutes: durationMinutes!);
    final passed = DateTime.now().difference(startedAt!);
    final left = total - passed;
    return left.isNegative ? Duration.zero : left;
  }

  double? get humidityGain {
    if (humidityBefore == null || humidityAfter == null) return null;
    return humidityAfter! - humidityBefore!;
  }

  factory IrrigationEvent.fromJson(Map<String, dynamic> json) {
    return IrrigationEvent(
      id: asInt(json['id']),
      greenhouseId: asInt(json['greenhouse_id']),
      type: asStringOrNull(json['type']),
      status: asStringOrNull(json['status']),
      startedAt: asDate(json['started_at']),
      endedAt: asDate(json['ended_at']),
      durationMinutes: asIntOrNull(json['duration_minutes']),
      humidityBefore: asDoubleOrNull(json['humidity_before']),
      humidityAfter: asDoubleOrNull(json['humidity_after']),
      source: asStringOrNull(json['source']),
      notes: asStringOrNull(json['notes']),
    );
  }
}
