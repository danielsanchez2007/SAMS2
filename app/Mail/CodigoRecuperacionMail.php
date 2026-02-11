<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $appName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código para recuperar tu contraseña - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
        $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));
        $logoPrincipalUrl = $logoMain ? asset('storage/' . $logoMain) : null;
        $logoSecundarioUrl = $logoSecondary ? asset('storage/' . $logoSecondary) : null;

        return new Content(
            view: 'emails.codigo-recuperacion',
            with: [
                'logoPrincipalUrl' => $logoPrincipalUrl,
                'logoSecundarioUrl' => $logoSecundarioUrl,
            ],
        );
    }
}
