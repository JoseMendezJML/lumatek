import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_client.dart';
import '../../greenhouses/domain/greenhouse.dart';
import '../data/auth_repository.dart';
import '../domain/user.dart';

enum AuthStatus {
  /// Todavía no se sabe si hay token guardado (pantalla de arranque).
  unknown,
  authenticated,
  unauthenticated,
}

@immutable
class AuthState {
  const AuthState({
    this.status = AuthStatus.unknown,
    this.user,
    this.greenhouses = const [],
    this.selectedGreenhouseId,
    this.isSubmitting = false,
  });

  final AuthStatus status;
  final AppUser? user;

  /// Invernaderos que el usuario puede operar. La web los resuelve por
  /// sesión; aquí se eligen en la app y el id viaja en cada URL.
  final List<Greenhouse> greenhouses;
  final int? selectedGreenhouseId;

  final bool isSubmitting;

  bool get isAuthenticated => status == AuthStatus.authenticated;

  Greenhouse? get selectedGreenhouse {
    if (selectedGreenhouseId == null) return null;
    for (final greenhouse in greenhouses) {
      if (greenhouse.id == selectedGreenhouseId) return greenhouse;
    }
    return null;
  }

  /// Hay sesión pero todavía no se eligió invernadero: el router manda al
  /// selector en lugar de al panel.
  bool get needsGreenhouseSelection =>
      isAuthenticated && selectedGreenhouseId == null;

  AuthState copyWith({
    AuthStatus? status,
    AppUser? user,
    List<Greenhouse>? greenhouses,
    int? selectedGreenhouseId,
    bool clearSelection = false,
    bool? isSubmitting,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      greenhouses: greenhouses ?? this.greenhouses,
      selectedGreenhouseId: clearSelection
          ? null
          : (selectedGreenhouseId ?? this.selectedGreenhouseId),
      isSubmitting: isSubmitting ?? this.isSubmitting,
    );
  }
}

class AuthController extends StateNotifier<AuthState> {
  AuthController(this._repository, this._unauthorizedSignal)
      : super(const AuthState()) {
    // Si el backend responde 401 en cualquier petición (token revocado desde
    // la web, por ejemplo), la sesión se cierra sola.
    _unauthorizedSignal.addListener(_onUnauthorized);
    restoreSession();
  }

  final AuthRepository _repository;
  final UnauthorizedSignal _unauthorizedSignal;

  @override
  void dispose() {
    _unauthorizedSignal.removeListener(_onUnauthorized);
    super.dispose();
  }

  void _onUnauthorized() {
    if (state.status == AuthStatus.authenticated) {
      _repository.clearToken();
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  /// Al abrir la app: si hay token guardado se valida contra /me.
  Future<void> restoreSession() async {
    if (!await _repository.hasToken()) {
      state = const AuthState(status: AuthStatus.unauthenticated);
      return;
    }

    try {
      final payload = await _repository.me();
      state = AuthState(
        status: AuthStatus.authenticated,
        user: payload.user,
        greenhouses: payload.greenhouses,
        selectedGreenhouseId: _defaultSelection(payload.greenhouses),
      );
    } catch (_) {
      await _repository.clearToken();
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  /// Lanza ApiException si las credenciales no son válidas; la pantalla de
  /// login la atrapa y pinta el mensaje del backend.
  Future<void> login({required String email, required String password}) async {
    state = state.copyWith(isSubmitting: true);

    try {
      final payload = await _repository.login(email: email, password: password);
      state = AuthState(
        status: AuthStatus.authenticated,
        user: payload.user,
        greenhouses: payload.greenhouses,
        selectedGreenhouseId: _defaultSelection(payload.greenhouses),
      );
    } catch (_) {
      state = state.copyWith(isSubmitting: false);
      rethrow;
    }
  }

  Future<void> logout() async {
    state = state.copyWith(isSubmitting: true);
    try {
      await _repository.logout();
    } catch (_) {
      // Aunque falle la revocación remota, localmente se cierra igual.
    }
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  void selectGreenhouse(int greenhouseId) {
    state = state.copyWith(selectedGreenhouseId: greenhouseId);
  }

  void clearGreenhouseSelection() {
    state = state.copyWith(clearSelection: true);
  }

  /// Refresca la lista de invernaderos sin cerrar sesión (pull to refresh).
  Future<void> refreshGreenhouses() async {
    try {
      final payload = await _repository.me();
      state = state.copyWith(
        user: payload.user,
        greenhouses: payload.greenhouses,
        selectedGreenhouseId:
            state.selectedGreenhouseId ?? _defaultSelection(payload.greenhouses),
      );
    } catch (_) {
      // El error ya se muestra en la pantalla que dispara la recarga.
    }
  }

  /// Con un solo invernadero no tiene sentido pedirle al operador que elija.
  int? _defaultSelection(List<Greenhouse> greenhouses) {
    if (greenhouses.length == 1) return greenhouses.first.id;
    return null;
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(
    ref.watch(authRepositoryProvider),
    ref.watch(unauthorizedSignalProvider),
  );
});

/// Id del invernadero activo. Todos los repositorios de módulos lo leen de
/// aquí, igual que la web lee el invernadero de la sesión.
final selectedGreenhouseIdProvider = Provider<int?>((ref) {
  return ref.watch(authControllerProvider).selectedGreenhouseId;
});

final selectedGreenhouseProvider = Provider<Greenhouse?>((ref) {
  return ref.watch(authControllerProvider).selectedGreenhouse;
});

final currentUserProvider = Provider<AppUser?>((ref) {
  return ref.watch(authControllerProvider).user;
});
