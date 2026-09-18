import '../../../shared/models/json_utils.dart';

/// Horario de riego programado. Refleja App\Models\IrrigationSchedule.
class IrrigationSchedule {
  const IrrigationSchedule({
    required this.id,
    required this.greenhouseId,
    required this.time,
    required this.durationMinutes,
    required this.days,
    required this.active,
  });

  final int id;
  final int greenhouseId;

  /// Hora en formato HH:mm, como la valida el backend (date_format:H:i).
  final String time;

  final int durationMinutes;

  /// Días en inglés: monday..sunday. Vacío significa todos los días.
  final List<String> days;

  final bool active;

  bool get isEveryDay => days.isEmpty || days.length == 7;

  /// Normaliza "08:00:00" (como a veces lo devuelve MySQL) a "08:00".
  String get displayTime {
    final parts = time.split(':');
    if (parts.length >= 2) {
      return '${parts[0].padLeft(2, '0')}:${parts[1].padLeft(2, '0')}';
    }
    return time;
  }

  factory IrrigationSchedule.fromJson(Map<String, dynamic> json) {
    return IrrigationSchedule(
      id: asInt(json['id']),
      greenhouseId: asInt(json['greenhouse_id']),
      time: asStringOrNull(json['time']) ?? '00:00',
      durationMinutes: asInt(json['duration_minutes']),
      days: asStringList(json['days']),
      active: asBool(json['active'], fallback: true),
    );
  }
}
