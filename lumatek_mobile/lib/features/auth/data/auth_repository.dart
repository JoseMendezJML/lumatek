import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../../core/storage/token_storage.dart';
import '../../../shared/models/json_utils.dart';
import '../../greenhouses/domain/greenhouse.dart';
import '../domain/user.dart';

/// Lo que devuelven /mobile/login y /mobile/me: el usuario más los
/// invernaderos que puede operar.
class SessionPayload {
  const SessionPayload({required this.user, required this.greenhouses});

  final AppUser user;
  final List<Greenhouse> greenhouses;

  factory SessionPayload.fromJson(Map<String, dynamic> json) {
    final raw = json['greenhouses'];
    return SessionPayload(
      user: AppUser.fromJson(asMap(json['user'])),
      greenhouses: raw is List
          ? raw.map((item) => Greenhouse.fromJson(asMap(item))).toList()
          : const [],
    );
  }
}

class AuthRepository {
  AuthRepository(this._client, this._tokenStorage);

  final ApiClient _client;
  final TokenStorage _tokenStorage;

  /// POST /api/mobile/login — guarda el token de Sanctum al vuelo.
  Future<SessionPayload> login({
    required String email,
    required String password,
  }) async {
    final json = await _client.post('/login', body: {
      'email': email,
      'password': password,
    });

    final token = asStringOrNull(json['token']);
    if (token != null) {
      await _tokenStorage.save(token);
    }

    return SessionPayload.fromJson(json);
  }

  /// GET /api/mobile/me — revalida el token guardado al abrir la app.
  Future<SessionPayload> me() async {
    final json = await _client.get('/me');
    return SessionPayload.fromJson(json);
  }

  /// POST /api/mobile/logout — revoca el token en el servidor.
  Future<void> logout() async {
    try {
      await _client.post('/logout');
    } finally {
      await _tokenStorage.clear();
    }
  }

  Future<void> clearToken() => _tokenStorage.clear();

  Future<bool> hasToken() async {
    final token = await _tokenStorage.read();
    return token != null && token.isNotEmpty;
  }
}

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.watch(apiClientProvider),
    ref.watch(tokenStorageProvider),
  );
});
