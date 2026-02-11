@echo off
echo ========================================
echo Verificacion y Correccion del archivo .env
echo ========================================
echo.

if not exist ".env" (
    echo ✗ ERROR: El archivo .env no existe
    echo Por favor, crea el archivo .env basandote en .env.example
    pause
    exit /b 1
)

echo ✓ Archivo .env encontrado
echo.
echo Verificando APP_URL...
echo.

REM Buscar la linea APP_URL
findstr /C:"APP_URL" ".env" >nul
if %errorlevel% neq 0 (
    echo ✗ No se encontro APP_URL en el archivo .env
    echo Agregando APP_URL...
    echo APP_URL=http://sams:8000 >> .env
    echo ✓ APP_URL agregado correctamente
) else (
    echo ✓ APP_URL encontrado
    echo.
    echo Contenido actual de APP_URL:
    findstr /C:"APP_URL" ".env"
    echo.
    echo IMPORTANTE: Verifica que APP_URL tenga este formato exacto:
    echo   APP_URL=http://sams:8000
    echo.
    echo Si tiene espacios, caracteres raros, o esta mal formateado, corrigelo manualmente.
)

echo.
echo ========================================
echo Verificacion completada
echo ========================================
pause
