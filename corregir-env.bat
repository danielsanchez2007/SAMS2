@echo off
echo ========================================
echo Correccion del archivo .env
echo ========================================
echo.

if not exist ".env" (
    echo ✗ ERROR: El archivo .env no existe
    pause
    exit /b 1
)

echo Corrigiendo APP_URL en el archivo .env...
echo.

REM Crear archivo temporal
set TEMP_FILE=%TEMP%\env_fixed_%RANDOM%.txt

REM Leer el archivo .env y corregir APP_URL
(
    for /f "usebackq delims=" %%a in (".env") do (
        set "linea=%%a"
        setlocal enabledelayedexpansion
        if "!linea:~0,8!"=="APP_URL=" (
            REM Si la linea empieza con APP_URL=, verificar si esta duplicado
            echo !linea! | findstr /C:"APP_URL=APP_URL" >nul
            if !errorlevel! equ 0 (
                REM Esta duplicado, corregirlo
                echo APP_URL=http://sams:8000
            ) else (
                REM Verificar si tiene el formato correcto
                echo !linea! | findstr /R "^APP_URL=http://" >nul
                if !errorlevel! equ 0 (
                    echo !linea!
                ) else (
                    REM No tiene formato correcto, corregirlo
                    echo APP_URL=http://sams:8000
                )
            )
        ) else (
            echo !linea!
        )
        endlocal
    )
) > "%TEMP_FILE%"

REM Reemplazar el archivo original
move /Y "%TEMP_FILE%" ".env" >nul

if %errorlevel% equ 0 (
    echo ✓ Archivo .env corregido correctamente
    echo.
    echo APP_URL ahora tiene el formato correcto:
    findstr /C:"APP_URL" ".env"
) else (
    echo ✗ Error al corregir el archivo
)

echo.
echo ========================================
pause
