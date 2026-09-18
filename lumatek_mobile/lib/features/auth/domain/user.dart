import '../../../shared/models/json_utils.dart';

/// Usuario autenticado, tal como lo arma MobileAuthController::userPayload().
class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.role,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;

  /// Slug del rol: 'admin' u otro. Determina si ve todos los invernaderos.
  final String? role;

  bool get isAdmin => role == 'admin';

  String get initials {
    final parts = name.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty);
    if (parts.isEmpty) return '?';
    return parts.take(2).map((p) => p[0].toUpperCase()).join();
  }

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: asInt(json['id']),
      name: asStringOrNull(json['name']) ?? 'Usuario',
      email: asStringOrNull(json['email']) ?? '',
      phone: asStringOrNull(json['phone']),
      role: asStringOrNull(json['role']),
    );
  }
}
