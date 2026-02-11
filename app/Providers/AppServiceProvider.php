<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Services\GeminiService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar GeminiService como singleton
        $this->app->singleton(GeminiService::class, function ($app) {
            return new GeminiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $key = config('temas_sistema.cache_key', 'sistema_tema_color');
            $temaKey = Cache::get($key, config('temas_sistema.default', 'indigo'));
            $temaConfig = \App\Helpers\TemaHelper::temaActual();
            $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
            $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));
            
            // Redes sociales
            $redesSociales = Cache::get('sistema_redes_sociales', []);
            if (!empty($redesSociales) && isset($redesSociales['facebook'])) {
                $redesSociales = [];
            }
            
            // Pie de página
            $piePagina = Cache::get('sistema_pie_pagina', [
                'descripcion' => 'Estamos construyendo la nueva experiencia en el trabajo. Sistema de gestión avanzado con tecnología moderna y diseño profesional.',
                'enlace_concepto' => '#',
                'texto_concepto' => 'Concepto',
                'enlace_quienes_somos' => '#',
                'texto_quienes_somos' => '¿Quiénes somos?',
                'enlace_faq' => '#',
                'texto_faq' => 'FAQ',
                'texto_aviso_legal' => 'Aviso legal',
                'enlace_aviso_legal' => '#',
                'texto_terminos' => 'Términos y condiciones de uso',
                'enlace_terminos' => '#',
                'texto_privacidad' => 'Política de privacidad',
                'enlace_privacidad' => '#',
                'texto_cookies' => 'Gestionar cookies',
                'enlace_cookies' => '#',
                'texto_copyright' => 'Todos los derechos reservados',
            ]);
            
            // Encabezado
            $encabezado = Cache::get('sistema_encabezado', [
                'texto_nombre_empresa' => config('app.name'),
            ]);
            
            $view->with('temaActual', $temaKey);
            $view->with('temaConfig', $temaConfig);
            $view->with('logoMain', $logoMain);
            $view->with('logoSecondary', $logoSecondary);
            $view->with('redesSociales', $redesSociales);
            $view->with('piePagina', $piePagina);
            $view->with('encabezado', $encabezado);
        });

        View::composer([
            'usuarios.index',
            'equipos.index',
            'tipo-equipos.index',
            'tipo-items.index',
            'uso-items.index',
            'estado-remision.index',
            'roles.index',
            'cargos.index',
            'proveedores.index',
            'fabricantes.index',
            'empresas.index',
            'grupos.index',
            'personalizacion.index',
        ], function ($view) {
            $logoMain = Cache::get(config('temas_sistema.logo_main_cache_key', 'sistema_logo_principal'));
            $logoSecondary = Cache::get(config('temas_sistema.logo_secondary_cache_key', 'sistema_logo_secundario'));
            $view->with('logoMain', $logoMain);
            $view->with('logoSecondary', $logoSecondary);
        });
    }
}
