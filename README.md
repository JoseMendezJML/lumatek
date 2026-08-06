# Lumatek

Plataforma web desarrollada con **Laravel 12**, compatible con **PHP 8.2 y PHP 8.3**, para simular, monitorear y gestionar las condiciones de uno o varios invernaderos.

La primera versión no utiliza sensores físicos. Los datos son generados por un simulador de telemetría y se procesan como lecturas reales para probar:

- dashboard dinámico;
- alertas configurables;
- simulación manual y automática;
- escenarios de prueba;
- riego manual y automático simulado;
- historial de lecturas y riegos;
- reportes imprimibles o guardables como PDF;
- usuarios, roles e invernaderos;
- API interna preparada para sustituir el simulador por un proveedor IoT.

## Tecnologías

- PHP 8.2 o 8.3
- Laravel 12
- MySQL / MariaDB
- HeidiSQL
- Blade
- CSS y JavaScript sin dependencias de Node
- Laragon en Windows

## Instalación rápida en Laragon

### 1. Extraer el proyecto

Extrae la carpeta como:

```text
C:\laragon\www\Lumatek
```

### 2. Abrir la terminal de Laragon

```bash
cd C:\laragon\www\Lumatek
```

### 3. Instalar dependencias

```bash
composer install
```

### 4. Crear el archivo de entorno

En CMD:

```bat
copy .env.example .env
```

En Git Bash:

```bash
cp .env.example .env
```

### 5. Crear la base de datos

En HeidiSQL abre una consulta y ejecuta:

```sql
CREATE DATABASE IF NOT EXISTS lumatek
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

También puedes abrir el archivo:

```text
database/heidisql_create_database.sql
```

### 6. Revisar la conexión en `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lumatek
DB_USERNAME=root
DB_PASSWORD=
```

En una instalación normal de Laragon, el usuario suele ser `root` y la contraseña está vacía. Ajusta esos valores si tu MySQL utiliza otras credenciales.

### 7. Generar la llave

```bash
php artisan key:generate
```

### 8. Crear tablas y datos iniciales

```bash
php artisan migrate --seed
```

Para reconstruir completamente la base de datos durante desarrollo:

```bash
php artisan migrate:fresh --seed
```

### 9. Limpiar cachés

```bash
php artisan optimize:clear
```

### 10. Abrir el proyecto

Con el botón **Start All** de Laragon activo, prueba:

```text
http://lumatek.test
```

También puedes ejecutar:

```bash
php artisan serve
```

Y abrir:

```text
http://127.0.0.1:8000
```

## Usuarios de demostración

### Administrador

```text
Correo: admin@lumatek.test
Contraseña: Lumatek123!
```

### Usuario

```text
Correo: usuario@lumatek.test
Contraseña: Lumatek123!
```

## Recuperación de contraseña

El prototipo utiliza:

```env
MAIL_MAILER=log
```

Por eso el enlace de recuperación se escribe en:

```text
storage/logs/laravel.log
```

Para enviar correos reales, configura SMTP en `.env`.

## Simulador

### Manual

Permite cambiar:

- temperatura;
- humedad del suelo;
- humedad ambiental;
- luminosidad;
- nivel de agua;
- estado del riego;
- estado de conexión.

Al enviar una lectura:

1. se valida;
2. se guarda en MySQL;
3. se evalúan las reglas;
4. se crean o resuelven alertas;
5. se actualizan las vistas;
6. se incluye en reportes.

### Automático

La simulación automática avanza cuando alguna vista consulta:

```text
GET /api/telemetry/current
```

Las vistas principales realizan esa consulta cada cinco segundos. Esta decisión evita requerir cron, colas o servicios externos durante la demostración.

### Escenarios

Incluye:

- condiciones normales;
- suelo seco;
- temperatura elevada;
- humedad ambiental elevada;
- depósito bajo;
- falla de riego;
- riego activo;
- lluvia próxima;
- pérdida de conexión.

## API interna

La API utiliza la sesión web y protección CSRF para las operaciones de escritura.

```text
GET    /api/telemetry/current
GET    /api/telemetry/history
POST   /api/simulator/readings
POST   /api/simulator/scenarios/{scenario}
POST   /api/simulator/start
POST   /api/simulator/stop
GET    /api/alerts
PATCH  /api/alerts/{alert}/resolve
GET    /api/irrigation/status
POST   /api/irrigation/start
POST   /api/irrigation/stop
GET    /api/irrigation/history
GET    /api/reports
```

## Preparación para IoT

El contrato se encuentra en:

```text
app/Contracts/TelemetryProvider.php
```

Proveedor actual:

```text
app/Services/Telemetry/SimulationTelemetryProvider.php
```

Punto de extensión:

```text
app/Services/Telemetry/IoTTelemetryProvider.php
```

Configuración:

```env
TELEMETRY_MODE=simulation
```

En el futuro:

```env
TELEMETRY_MODE=iot
```

El proveedor IoT deberá recibir o consultar lecturas reales y guardarlas con:

```text
source = iot
```

## Reportes PDF

La vista de reporte incluye el botón:

```text
Imprimir / Guardar PDF
```

El navegador abrirá el cuadro de impresión. En Windows selecciona:

```text
Microsoft Print to PDF
```

o:

```text
Guardar como PDF
```

No se requiere una librería externa.

## Pruebas automatizadas

Después de instalar Composer:

```bash
php artisan test
```

Se incluyen pruebas para:

- inicio de sesión;
- bloqueo de cuentas inactivas;
- permisos del simulador;
- generación de alertas;
- resolución mediante escenario normal;
- inicio del riego simulado;
- contrato del endpoint de telemetría.

## Comandos Git recomendados

```bash
git checkout -b feature/lum-01-configuracion-inicial
git add .
git commit -m "feat: agregar prototipo funcional inicial de Lumatek"
git push -u origin feature/lum-01-configuracion-inicial
```

## Nota de alcance

Lumatek no afirma que existan sensores conectados en esta versión. Toda pantalla con telemetría muestra la etiqueta **Datos simulados**. La integración IoT queda preparada como una fase posterior.
