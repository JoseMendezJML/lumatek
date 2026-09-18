import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

/// Cliente único de la API móvil.
///
/// Dos interceptores hacen todo el trabajo repetitivo:
///   1. Inyectan el Bearer token de Sanctum en cada petición.
///   2. Convierten cualquier DioException en ApiException, y si el backend
///      responde 401 (token revocado desde la web o expirado), avisan para
///      que la app cierre sesión sola.
class ApiClient {
  ApiClient({
    required TokenStorage tokenStorage,
    required this.onUnauthorized,
    Dio? dio,
  })  : _tokenStorage = tokenStorage,
        _dio = dio ?? Dio() {
    _dio.options = BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      // Laravel responde 401/403/422 con cuerpo JSON útil: se deja pasar
      // para poder leerlo en lugar de que Dio lance antes de tiempo.
      validateStatus: (status) => status != null && status < 500,
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _tokenStorage.read();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onResponse: (response, handler) {
          final status = response.statusCode ?? 0;

          if (status >= 400) {
            final exception = ApiException.fromDio(
              DioException(
                requestOptions: response.requestOptions,
                response: response,
              ),
            );

            if (exception.isUnauthorized) {
              onUnauthorized();
            }

            return handler.reject(
              DioException(
                requestOptions: response.requestOptions,
                response: response,
                error: exception,
              ),
            );
          }

          handler.next(response);
        },
        onError: (error, handler) {
          final exception = error.error is ApiException
              ? error.error as ApiException
              : ApiException.fromDio(error);

          if (exception.isUnauthorized) {
            onUnauthorized();
          }

          handler.reject(
            DioException(
              requestOptions: error.requestOptions,
              response: error.response,
              error: exception,
            ),
          );
        },
      ),
    );
  }

  final Dio _dio;
  final TokenStorage _tokenStorage;
  final void Function() onUnauthorized;

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? query,
  }) async {
    return _unwrap(() => _dio.get(path, queryParameters: query));
  }

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    return _unwrap(() => _dio.post(path, data: body));
  }

  Future<Map<String, dynamic>> patch(
    String path, {
    Map<String, dynamic>? body,
  }) async {
    return _unwrap(() => _dio.patch(path, data: body));
  }

  Future<Map<String, dynamic>> delete(String path) async {
    return _unwrap(() => _dio.delete(path));
  }

  Future<Map<String, dynamic>> _unwrap(
    Future<Response<dynamic>> Function() request,
  ) async {
    try {
      final response = await request();
      final data = response.data;

      if (data is Map<String, dynamic>) return data;
      if (data is Map) return Map<String, dynamic>.from(data);

      // Respuestas sin cuerpo (por ejemplo un 204) se normalizan a mapa vacío.
      return <String, dynamic>{};
    } on DioException catch (error) {
      throw error.error is ApiException
          ? error.error as ApiException
          : ApiException.fromDio(error);
    }
  }
}

/// Notificador simple que dispara el cierre de sesión forzado.
/// El AuthController lo escucha para limpiar el token y mandar al login.
class UnauthorizedSignal extends ChangeNotifier {
  void trigger() => notifyListeners();
}

final unauthorizedSignalProvider = Provider<UnauthorizedSignal>((ref) {
  final signal = UnauthorizedSignal();
  ref.onDispose(signal.dispose);
  return signal;
});

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(
    tokenStorage: ref.watch(tokenStorageProvider),
    onUnauthorized: ref.watch(unauthorizedSignalProvider).trigger,
  );
});
