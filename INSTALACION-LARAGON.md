# Instalación de Lumatek en Laragon

## Requisitos

Comprueba:

```bash
php -v
composer --version
php -m
```

Extensiones importantes:

```text
ctype
curl
dom
fileinfo
filter
hash
mbstring
openssl
pcre
PDO
pdo_mysql
session
tokenizer
xml
```

## Secuencia completa

```bash
cd C:\laragon\www\Lumatek
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan optimize:clear
```

Abre:

```text
http://lumatek.test
```

## Reiniciar la base de datos

```bash
php artisan migrate:fresh --seed
```

## Errores comunes

### `vendor/autoload.php` no existe

Ejecuta:

```bash
composer install
```

### `.env` no existe

Ejecuta:

```bat
copy .env.example .env
```

### `No application encryption key`

Ejecuta:

```bash
php artisan key:generate
```

### `Unknown database lumatek`

Crea la base desde HeidiSQL usando:

```text
database/heidisql_create_database.sql
```

### `Access denied for user`

Corrige `DB_USERNAME` y `DB_PASSWORD` en `.env`.

### Los cambios del `.env` no se reflejan

```bash
php artisan optimize:clear
```
