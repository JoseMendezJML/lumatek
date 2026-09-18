/// Configuración de entorno.
///
/// La URL base se pasa en tiempo de compilación para no tener que tocar
/// código al cambiar de servidor:
///
///   flutter run --dart-define=API_BASE_URL=http://192.168.1.70:8000
///   flutter build apk --dart-define=API_BASE_URL=https://lumatek.tudominio.com
///
/// Valores por defecto útiles en desarrollo:
///   - Emulador Android: http://10.0.2.2:8000  (10.0.2.2 apunta al host)
///   - Simulador iOS:    http://127.0.0.1:8000
///   - Dispositivo real: la IP de tu máquina en la red local, y Laravel
///     levantado con `php artisan serve --host=0.0.0.0`
class AppConfig {
  const AppConfig._();

  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000',
  );

  /// Prefijo del grupo de rutas móviles definido en routes/api.php.
  static const String apiPrefix = '/api/mobile';

  static String get apiBaseUrl => '$baseUrl$apiPrefix';

  /// Cada cuánto se refresca la telemetría del panel.
  static const Duration telemetryRefreshInterval = Duration(seconds: 30);
}
