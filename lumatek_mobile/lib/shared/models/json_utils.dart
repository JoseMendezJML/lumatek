/// Conversores tolerantes al JSON de Laravel.
///
/// Los casts `decimal:2` de Eloquent se serializan como **cadenas**
/// ("24.50"), no como números. Estas funciones evitan tener que recordarlo
/// en cada modelo.
double? asDoubleOrNull(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  return double.tryParse('$value');
}

double asDouble(dynamic value, {double fallback = 0}) {
  return asDoubleOrNull(value) ?? fallback;
}

int? asIntOrNull(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse('$value');
}

int asInt(dynamic value, {int fallback = 0}) {
  return asIntOrNull(value) ?? fallback;
}

bool asBool(dynamic value, {bool fallback = false}) {
  if (value == null) return fallback;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final text = '$value'.toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return fallback;
}

DateTime? asDate(dynamic value) {
  if (value == null) return null;
  if (value is DateTime) return value;
  return DateTime.tryParse('$value')?.toLocal();
}

String? asStringOrNull(dynamic value) {
  if (value == null) return null;
  final text = '$value';
  return text.isEmpty ? null : text;
}

List<String> asStringList(dynamic value) {
  if (value is List) return value.map((e) => '$e').toList();
  return const [];
}

Map<String, dynamic> asMap(dynamic value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) return Map<String, dynamic>.from(value);
  return <String, dynamic>{};
}
