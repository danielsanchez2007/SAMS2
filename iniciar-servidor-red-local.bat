@echo off
echo ========================================
echo Iniciar servidor para acceso desde red local
echo ========================================
echo.

echo Verificando si el puerto 8000 esta en uso...
netstat -ano | findstr :8000 >nul
if %errorlevel% equ 0 (
    echo.
    echo ⚠ El puerto 8000 ya esta en uso
    echo.
    echo Opciones:
    echo 1. Usar el servidor que ya esta corriendo (Apache)
    echo    Accede desde tu celular: http://192.168.2.59:8000
    echo.
    echo 2. Usar otro puerto (8001)
    echo.
    set /p opcion="Selecciona opcion (1 o 2): "
    
    if "!opcion!"=="2" (
        echo.
        echo Iniciando servidor en puerto 8001...
        php artisan serve --host=0.0.0.0 --port=8001
        echo.
        echo ✓ Servidor iniciado en: http://192.168.2.59:8001
    ) else (
        echo.
        echo El servidor Apache ya esta corriendo en el puerto 8000
        echo Accede desde tu celular: http://192.168.2.59:8000
        pause
        exit /b 0
    )
) else (
    echo.
    echo Iniciando servidor en puerto 8000...
    php artisan serve --host=0.0.0.0 --port=8000
    echo.
    echo ✓ Servidor iniciado en: http://192.168.2.59:8000
)

pause
