@echo off
echo ========================================
echo Configuracion de Dominio PreventionWorld
echo ========================================
echo.

REM Verificar permisos de administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ✗ ERROR: Este script debe ejecutarse como Administrador
    echo.
    echo Por favor:
    echo 1. Haz clic derecho en este archivo
    echo 2. Selecciona "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

REM Verificar si Laragon esta instalado
if not exist "C:\laragon" (
    echo ERROR: Laragon no encontrado en C:\laragon
    echo Por favor, instala Laragon o ajusta la ruta en este script.
    pause
    exit /b 1
)

echo [1/4] Creando archivo de configuracion del virtual host...
if not exist "C:\laragon\etc\apache2\sites-enabled" (
    mkdir "C:\laragon\etc\apache2\sites-enabled"
)

copy /Y "preventionworld.conf" "C:\laragon\etc\apache2\sites-enabled\preventionworld.conf"
if %errorlevel% equ 0 (
    echo ✓ Archivo de configuracion copiado correctamente
) else (
    echo ✗ Error al copiar el archivo de configuracion
    pause
    exit /b 1
)

echo.
echo [2/4] Configurando archivo hosts de Windows...

REM Verificar si ya existe la entrada
findstr /C:"preventionworld.test" "C:\Windows\System32\drivers\etc\hosts" >nul
if %errorlevel% equ 0 (
    echo ✓ La entrada ya existe en el archivo hosts
) else (
    echo Agregando entrada al archivo hosts...
    (
        echo.
        echo # PreventionWorld - SAMS2
        echo 127.0.0.1    preventionworld.test
        echo 127.0.0.1    www.preventionworld.test
    ) >> "C:\Windows\System32\drivers\etc\hosts"
    if %errorlevel% equ 0 (
        echo ✓ Entrada agregada correctamente
    ) else (
        echo ✗ Error: No se pudo modificar el archivo hosts
        echo Por favor, agrega manualmente estas lineas a C:\Windows\System32\drivers\etc\hosts:
        echo   127.0.0.1    preventionworld.test
        echo   127.0.0.1    www.preventionworld.test
    )
)

echo.
echo [3/4] Verificando archivo .env...
if exist ".env" (
    echo ✓ Archivo .env encontrado
    echo.
    echo IMPORTANTE: Debes actualizar manualmente el archivo .env
    echo Cambia la linea APP_URL a:
    echo   APP_URL=http://preventionworld.test
    echo.
) else (
    echo ✗ Archivo .env no encontrado
    echo Por favor, crea el archivo .env basandote en .env.example
)

echo.
echo [4/4] Instrucciones finales:
echo.
echo 1. Abre Laragon
echo 2. Deten todos los servicios (Stop All)
echo 3. Inicia todos los servicios nuevamente (Start All)
echo 4. Abre tu navegador y visita: http://preventionworld.test
echo.
echo ========================================
echo Configuracion completada!
echo ========================================
pause
