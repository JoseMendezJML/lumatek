# Lumatek Mobile

App móvil en Flutter para el proyecto Laravel Lumatek. Consume `routes/api.php`
(grupo `mobile`, prefijo `/api/mobile`), autenticado con **Sanctum** por Bearer
token. No se conecta directo a la base de datos: toda la lógica sigue viviendo
en Laravel, igual que en la web.

## Requisitos

- Flutter 3.24 o superior (Dart 3.4+)
- Tu backend Laravel corriendo y accesible desde el dispositivo/emulador

## Instalación

```bash
flutter pub get
```

## Levantar el backend para que el móvil lo alcance

Por defecto `php artisan serve` solo escucha en `127.0.0.1`, que el emulador
y el celular no pueden alcanzar. Levántalo así:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Ejecutar la app

La URL del API se pasa en tiempo de compilación con `--dart-define`, así no
hay que tocar código para cambiar de servidor:

```bash
# Emulador Android (10.0.2.2 apunta al localhost del equipo host)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000

# Simulador iOS
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8000

# Dispositivo físico en la misma red Wi-Fi que tu máquina
flutter run --dart-define=API_BASE_URL=http://192.168.1.XX:8000
```

Si no pasas `API_BASE_URL`, usa `http://10.0.2.2:8000` (pensado para emulador
Android). Puedes cambiar ese valor por defecto en
`lib/core/config/app_config.dart`.

## Compilar para producción

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://tu-dominio.com
flutter build ios --release --dart-define=API_BASE_URL=https://tu-dominio.com
```

## Estructura del proyecto

```
lib/
  core/
    config/        URL base del API
    network/        Cliente Dio + manejo de errores de Laravel (422/401/403)
    storage/        Token de Sanctum en almacenamiento seguro (Keychain / EncryptedSharedPreferences)
    theme/          Colores, tipografía, etiquetas en español
    router/         go_router con redirecciones según sesión
    widgets/        AsyncView, StatusPill, AppCard (compartidos)
  features/
    auth/           Login, sesión, selección de invernadero activo
    greenhouses/    Listado y selector de invernadero
    dashboard/      Panel principal (endpoint agregado)
    telemetry/      Lectura actual + historial con gráfica
    alerts/         Listado, filtros, marcar vista, resolver
    irrigation/     Control manual, automático, horarios, historial
    reports/        Resumen por rango de días
  shared/
    models/         Utilidades de parseo JSON tolerante, paginación
    widgets/        HomeShell (navegación inferior de 5 pestañas)
```

Cada módulo sigue el mismo patrón: `domain` (modelos), `data` (repositorio
que llama al API) y `presentation` (providers de Riverpod + pantallas).

## Cómo se autentica

1. `POST /api/mobile/login` devuelve `token` + `user` + `greenhouses`.
2. El token se guarda cifrado en el dispositivo y se inyecta como
   `Authorization: Bearer <token>` en cada petición (interceptor de Dio).
3. Si el backend responde 401 en cualquier momento (token revocado desde la
   web, por ejemplo), la app cierra la sesión sola y vuelve al login.
4. Al reabrir la app, el token guardado se valida contra `GET /api/mobile/me`.

## El invernadero activo

La web lo resuelve por sesión de PHP; la API móvil es *stateless* y el id de
invernadero viaja explícito en cada URL (`/greenhouses/{id}/...`). Por eso la
app agrega una pantalla de selección que no existe en la web: con un solo
invernadero asignado se entra directo al panel; con varios, se elige y se
puede cambiar después desde el ícono de la barra superior o el menú de
cuenta.

## Módulos que la web tiene y la API móvil todavía no expone

`routes/api.php` no incluye endpoints para gestión de usuarios, CRUD de
invernaderos, reglas de alerta ni el simulador (los cuatro son de
administrador en la web). Si quieres esos módulos en la app, hay que agregar
antes los controladores y rutas correspondientes en Laravel, siguiendo el
mismo patrón que `Mobile*Controller` + `AuthorizesMobileGreenhouse`.

## Notas técnicas

- Los campos con cast `decimal:2` de Eloquent llegan del API como **cadenas**
  (`"24.50"`), no como números — `lib/shared/models/json_utils.dart` lo
  resuelve en un solo lugar para todos los modelos.
- Los estados de cada métrica (`normal` / `warning` / `critical`) los calcula
  `TelemetryStatusService` en el backend; la app solo los pinta, para que
  web y móvil nunca muestren clasificaciones distintas con los mismos datos.
- No se usa `build_runner` ni generación de código: todos los modelos
  parsean JSON a mano, así que `flutter pub get` es suficiente para arrancar.
