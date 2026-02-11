@echo off
echo ========================================
echo Agregar regla de firewall para puerto 8000
echo ========================================
echo.
echo Este script necesita ejecutarse como Administrador
echo.

netsh advfirewall firewall add rule name="Laravel Server Port 8000" dir=in action=allow protocol=TCP localport=8000

if %errorlevel% equ 0 (
    echo.
    echo ✓ Regla de firewall agregada correctamente
    echo.
    echo El puerto 8000 ahora esta permitido en el firewall
    echo Puedes acceder desde tu celular usando: http://192.168.2.59:8000
) else (
    echo.
    echo ✗ Error al agregar la regla
    echo.
    echo Por favor, ejecuta este script como Administrador:
    echo 1. Clic derecho en este archivo
    echo 2. Selecciona "Ejecutar como administrador"
)

echo.
pause
