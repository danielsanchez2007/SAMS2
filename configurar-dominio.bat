@echo off
echo ========================================
echo Configuracion de Dominio SAMS
echo ========================================
echo.

REM Verificar permisos de administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Este script debe ejecutarse como Administrador
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

echo [1/4] Copiando archivo del virtual host...
if not exist "C:\laragon\etc\apache2\sites-enabled" (
    mkdir "C:\laragon\etc\apache2\sites-enabled"
)

copy /Y "sams.conf" "C:\laragon\etc\apache2\sites-enabled\sams.conf"
if %errorlevel% equ 0 (
    echo OK: Archivo sams.conf copiado correctamente
) else (
    echo ERROR: No se pudo copiar sams.conf
    pause
    exit /b 1
)

echo.
echo [2/4] Configurando archivo hosts de Windows...

REM Verificar si ya existe la entrada principal
findstr /C:"127.0.0.1    sams" "C:\Windows\System32\drivers\etc\hosts" >nul
if %errorlevel% equ 0 (
    echo OK: La entrada de SAMS ya existe en hosts
) else (
    echo Agregando entradas al archivo hosts...
    (
        echo.
        echo # SAMS2 - acceso local por nombre
        echo 127.0.0.1    sams
        echo 127.0.0.1    www.sams
        echo 127.0.0.1    sams.test
        echo 127.0.0.1    www.sams.test
    ) >> "C:\Windows\System32\drivers\etc\hosts"
    if %errorlevel% equ 0 (
        echo OK: Entradas agregadas correctamente
    ) else (
        echo ERROR: No se pudo modificar el archivo hosts
        echo Agrega manualmente estas lineas en C:\Windows\System32\drivers\etc\hosts:
        echo   127.0.0.1    sams
        echo   127.0.0.1    www.sams
        echo   127.0.0.1    sams.test
        echo   127.0.0.1    www.sams.test
    )
)

echo.
echo [3/4] Verificando archivo .env...
if exist ".env" (
    echo OK: Archivo .env encontrado
    echo.
    echo IMPORTANTE: Debes validar esta linea en .env
    echo   APP_URL=http://sams:8000
    echo.
) else (
    echo ERROR: Archivo .env no encontrado
    echo Crea el archivo .env basandote en .env.example
)

echo.
echo [4/4] Instrucciones finales:
echo.
echo 1. Abre Laragon
echo 2. Deten todos los servicios (Stop All)
echo 3. Inicia todos los servicios (Start All)
echo 4. Si usas Apache, visita: http://sams
echo 5. Si usas composer dev, visita: http://sams:8000
echo.
echo ========================================
echo Configuracion completada
echo ========================================
pause
