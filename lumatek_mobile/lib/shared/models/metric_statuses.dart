import 'json_utils.dart';

/// Resultado de App\Services\TelemetryStatusService::metricStatuses().
///
/// La clasificación (normal / warning / critical) se calcula en el backend,
/// igual que en la web, para que móvil y web nunca muestren estados
/// distintos con los mismos datos.
class MetricStatuses {
  const MetricStatuses(this._values);

  final Map<String, String> _values;

  static const metricKeys = [
    'temperature',
    'soil_humidity',
    'ambient_humidity',
    'luminosity',
    'water_level',
  ];

  String of(String metric) => _values[metric] ?? 'unknown';

  String get overall => of('overall');

  bool get hasCritical => _values.entries
      .any((entry) => entry.key != 'overall' && entry.value == 'critical');

  factory MetricStatuses.fromJson(dynamic json) {
    final map = asMap(json);
    return MetricStatuses({
      for (final entry in map.entries) entry.key: '${entry.value}',
    });
  }

  static const unknown = MetricStatuses({});
}
