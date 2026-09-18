import 'package:intl/intl.dart';

/// Formato de fechas y números en español, centralizado para que todas las
/// pantallas hablen igual.
class Fmt {
  const Fmt._();

  static final _dateTime = DateFormat("d 'de' MMMM, HH:mm", 'es');
  static final _shortDateTime = DateFormat('dd/MM HH:mm', 'es');
  static final _time = DateFormat('HH:mm', 'es');
  static final _date = DateFormat("d 'de' MMMM 'de' y", 'es');

  static String dateTime(DateTime? value) =>
      value == null ? '—' : _dateTime.format(value);

  static String shortDateTime(DateTime? value) =>
      value == null ? '—' : _shortDateTime.format(value);

  static String time(DateTime? value) =>
      value == null ? '—' : _time.format(value);

  static String date(DateTime? value) =>
      value == null ? '—' : _date.format(value);

  /// "hace 3 min", "hace 2 h": lo que el operador realmente quiere saber de
  /// una lectura es qué tan fresca es.
  static String relative(DateTime? value) {
    if (value == null) return 'sin fecha';

    final diff = DateTime.now().difference(value);

    if (diff.isNegative) return 'en instantes';
    if (diff.inSeconds < 60) return 'hace unos segundos';
    if (diff.inMinutes < 60) return 'hace ${diff.inMinutes} min';
    if (diff.inHours < 24) return 'hace ${diff.inHours} h';
    if (diff.inDays == 1) return 'ayer';
    if (diff.inDays < 30) return 'hace ${diff.inDays} días';
    return date(value);
  }

  static String number(double? value, {int decimals = 1}) {
    if (value == null) return '—';
    return value.toStringAsFixed(decimals);
  }

  static String metric(double? value, String unit, {int decimals = 1}) {
    if (value == null) return '—';
    if (unit == 'lux') return '${value.round()} $unit';
    return '${value.toStringAsFixed(decimals)} $unit';
  }

  /// Duración legible: "12 min", "1 h 05 min".
  static String duration(Duration? value) {
    if (value == null) return '—';
    final minutes = value.inMinutes;
    if (minutes < 60) return '$minutes min';
    final hours = minutes ~/ 60;
    final rest = minutes % 60;
    return '$hours h ${rest.toString().padLeft(2, '0')} min';
  }

  /// Cuenta regresiva mm:ss para el riego en curso.
  static String countdown(Duration? value) {
    if (value == null) return '--:--';
    final minutes = value.inMinutes.toString().padLeft(2, '0');
    final seconds = (value.inSeconds % 60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }

  static String range(double? min, double? max, String unit) {
    if (min == null && max == null) return 'sin rango definido';
    if (max == null) return 'mínimo ${number(min, decimals: 0)} $unit';
    if (min == null) return 'máximo ${number(max, decimals: 0)} $unit';
    return '${number(min, decimals: 0)} – ${number(max, decimals: 0)} $unit';
  }
}
