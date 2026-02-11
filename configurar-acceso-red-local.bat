@echo off
echo ========================================
echo Configuracion de acceso desde red local
echo ========================================
echo.

echo [1/4] Obteniendo direccion IP local...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    set "ip=%%a"
    setlocal enabledelayedexpansion
    set "ip=!ip:~1!"
    echo Tu direccion IP local es: !ip!
    endlocal
)
echo.

echo [2/4] Verificando configuracion de Apache...
echo.
echo IMPORTANTE: Para que funcione desde tu celular, necesitas:
echo.
echo 1. Verificar que Apache este escuchando en todas las interfaces
echo    - Abre Laragon
echo    - Menu -^> Apache -^> httpd.conf
echo    - Busca la linea: Listen 127.0.0.1:80
echo    - Cambiala por: Listen 0.0.0.0:80
echo    - O simplemente: Listen 80
echo.
echo 2. Verificar el Virtual Host
echo    - Asegurate de que el VirtualHost use *:80 en lugar de 127.0.0.1:80
echo    - El archivo sams.conf ya esta configurado correctamente
echo.
echo 3. Configurar el Firewall de Windows
echo    - Abre el Firewall de Windows
echo    - Permite Apache HTTP Server a traves del firewall
echo    - O ejecuta este comando como Administrador:
echo      netsh advfirewall firewall add rule name="Apache HTTP Server" dir=in action=allow protocol=TCP localport=80
echo.
echo [3/4] Configurando Firewall...
echo.
echo Intentando agregar regla de firewall...
netsh advfirewall firewall add rule name="Apache HTTP Server" dir=in action=allow protocol=TCP localport=80 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ Regla de firewall agregada correctamente
) else (
    echo ⚠ No se pudo agregar la regla automaticamente
    echo   Por favor, ejecuta este script como Administrador
    echo   O agrega manualmente la regla en el Firewall de Windows
)
echo.

echo [4/4] Instrucciones finales:
echo.
echo 1. Abre Laragon
echo 2. Deten todos los servicios (Stop All)
echo 3. Edita la configuracion de Apache:
echo    - Menu -^> Apache -^> httpd.conf
echo    - Busca: Listen 127.0.0.1:80
echo    - Cambia a: Listen 0.0.0.0:80
echo 4. Guarda el archivo
echo 5. Inicia todos los servicios (Start All)
echo.
echo 6. Desde tu celular (conectado a la misma red WiFi):
echo    - Abre el navegador
echo    - Visita: http://192.168.2.59
echo    - O si usas dominio local: http://sams
echo.
echo NOTA: Si tu IP cambia, ejecuta: ipconfig ^| findstr IPv4
echo       para obtener tu nueva direccion IP
echo.
echo ========================================
pause
