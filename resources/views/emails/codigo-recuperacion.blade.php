<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Código de recuperación</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #334155; max-width: 560px; margin: 0 auto; padding: 24px;">
    <div style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 28px 24px; border-radius: 12px 12px 0 0; text-align: center;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 12px;">
            <img src="{{ $logoPrincipalUrl ?? rtrim(config('app.url'), '/') . '/img/logos/logoSams.png' }}" alt="SAMS" style="height: 56px; width: auto; display: block;">
            <img src="{{ $logoSecundarioUrl ?? rtrim(config('app.url'), '/') . '/img/logos/LOGO-INSTITUTO-PREVENTION-WORLD.png' }}" alt="Prevention World" style="height: 56px; width: auto; display: block;">
        </div>
        <h1 style="color: #fff; margin: 0; font-size: 1.25rem;">{{ $appName }}</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0; font-size: 0.9rem;">Recuperación de contraseña</p>
    </div>
    <div style="background: #f8fafc; padding: 28px 24px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px; min-height: 180px;">
        <p style="margin: 0 0 16px;">Hola,</p>
        <p style="margin: 0 0 16px;">Has solicitado restablecer tu contraseña. Utiliza el siguiente código en la página de recuperación:</p>
        <p style="text-align: center; margin: 24px 0; font-size: 1.75rem; font-weight: bold; letter-spacing: 0.25em; color: #4f46e5;">{{ $code }}</p>
        <p style="margin: 0 0 8px; font-size: 0.875rem; color: #64748b;">El código es válido durante 60 minutos.</p>
        <p style="margin: 16px 0 0; font-size: 0.875rem; color: #64748b;">Si no solicitaste este correo, puedes ignorarlo.</p>
    </div>
</body>
</html>
