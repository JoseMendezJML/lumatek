import 'package:dio/dio.dart';

/// Error de API ya traducido a algo que la interfaz puede mostrar.
///
/// Laravel responde con formas muy predecibles y aquí se normalizan todas:
///   - 422: {"message": "...", "errors": {"campo": ["mensaje"]}}
///   - 401: token inválido o expirado
///   - 403: abort_if del trait AuthorizesMobileGreenhouse
///   - 404: recurso inexistente
class ApiException implements Exception {
  ApiException({
    required this.message,
    this.statusCode,
    this.errors = const {},
  });

  final String message;
  final int? statusCode;

  /// Errores de validación por campo, tal como los manda Laravel.
  final Map<String, List<String>> errors;

  bool get isUnauthorized => statusCode == 401;
  bool get isForbidden => statusCode == 403;
  bool get isNotFound => statusCode == 404;
  bool get isValidation => statusCode == 422;

  /// Primer mensaje de validación de un campo, para pintarlo bajo el input.
  String? errorFor(String field) => errors[field]?.first;

  factory ApiException.fromDio(DioException error) {
    final response = error.response;

    if (response == null) {
      return ApiException(
        message: switch (error.type) {
          DioExceptionType.connectionTimeout ||
          DioExceptionType.sendTimeout ||
          DioExceptionType.receiveTimeout =>
            'El servidor tardó demasiado en responder. Revisa tu conexión.',
          DioExceptionType.connectionError =>
            'No se pudo conectar con el servidor. Verifica la red y la dirección configurada.',
          _ => 'Ocurrió un error inesperado de conexión.',
        },
      );
    }

    final data = response.data;
    final statusCode = response.statusCode;

    if (data is Map) {
      final rawErrors = data['errors'];
      final parsedErrors = <String, List<String>>{};

      if (rawErrors is Map) {
        rawErrors.forEach((key, value) {
          if (value is List) {
            parsedErrors['$key'] = value.map((e) => '$e').toList();
          } else if (value != null) {
            parsedErrors['$key'] = ['$value'];
          }
        });
      }

      final message = data['message'];

      return ApiException(
        statusCode: statusCode,
        errors: parsedErrors,
        message: message is String && message.isNotEmpty
            ? message
            : _defaultMessageFor(statusCode),
      );
    }

    return ApiException(
      statusCode: statusCode,
      message: _defaultMessageFor(statusCode),
    );
  }

  static String _defaultMessageFor(int? statusCode) => switch (statusCode) {
        401 => 'Tu sesión expiró. Inicia sesión de nuevo.',
        403 => 'No tienes acceso a este invernadero.',
        404 => 'No se encontró la información solicitada.',
        422 => 'Revisa los datos e inténtalo otra vez.',
        429 => 'Demasiados intentos. Espera un momento.',
        int s when s >= 500 => 'El servidor tuvo un problema. Inténtalo más tarde.',
        _ => 'Ocurrió un error inesperado.',
      };

  @override
  String toString() => message;
}
