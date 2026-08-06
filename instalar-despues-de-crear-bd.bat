@echo off
title Instalacion de Lumatek
echo ==========================================
echo       INSTALACION DEL PROYECTO LUMATEK
echo ==========================================
echo.
echo Asegurate de haber creado la base de datos "lumatek" en HeidiSQL.
echo.
pause

where composer >nul 2>nul
if errorlevel 1 (
    echo ERROR: Composer no esta disponible en esta terminal.
    echo Abre la terminal desde Laragon e intenta nuevamente.
    pause
    exit /b 1
)

composer install
if errorlevel 1 goto error

if not exist .env copy .env.example .env

php artisan key:generate
if errorlevel 1 goto error

php artisan migrate --seed
if errorlevel 1 goto error

php artisan optimize:clear
if errorlevel 1 goto error

echo.
echo Instalacion terminada.
echo Abre http://lumatek.test
echo.
pause
exit /b 0

:error
echo.
echo La instalacion se detuvo por un error.
echo Revisa el mensaje anterior y consulta INSTALACION-LARAGON.md
pause
exit /b 1
