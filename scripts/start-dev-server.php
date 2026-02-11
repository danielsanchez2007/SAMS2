<?php

declare(strict_types=1);

/**
 * Starts Laravel dev server on the first available port.
 *
 * Defaults are Windows-friendly and avoid forcing 0.0.0.0:8000.
 * You can override with:
 *   DEV_SERVER_HOST=127.0.0.1
 *   DEV_SERVER_PORTS=8000,8001,8080
 */
$host = getenv('DEV_SERVER_HOST') ?: '127.0.0.1';
$portsRaw = getenv('DEV_SERVER_PORTS') ?: '8000,8001,8080';

$ports = [];
foreach (explode(',', $portsRaw) as $portCandidate) {
    $portCandidate = trim($portCandidate);
    if ($portCandidate === '' || !ctype_digit($portCandidate)) {
        continue;
    }

    $port = (int) $portCandidate;
    if ($port > 0 && $port < 65536) {
        $ports[] = $port;
    }
}

if ($ports === []) {
    fwrite(STDERR, "[server] DEV_SERVER_PORTS no contiene puertos validos.\n");
    exit(1);
}

$artisanPath = realpath(__DIR__.'/../artisan');
if ($artisanPath === false) {
    fwrite(STDERR, "[server] No se encontro el archivo artisan.\n");
    exit(1);
}

foreach ($ports as $port) {
    $probe = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);

    if ($probe === false) {
        $reason = $errstr !== '' ? $errstr : 'puerto no disponible';
        fwrite(STDOUT, "[server] {$host}:{$port} no disponible ({$reason}). Probando siguiente...\n");
        continue;
    }

    fclose($probe);
    fwrite(STDOUT, "[server] Iniciando Laravel en http://{$host}:{$port}\n");

    $command = sprintf(
        '%s %s serve --host=%s --port=%d',
        escapeshellarg(PHP_BINARY),
        escapeshellarg($artisanPath),
        escapeshellarg($host),
        $port
    );

    passthru($command, $exitCode);
    exit($exitCode);
}

fwrite(
    STDERR,
    "[server] No fue posible iniciar el servidor. Puertos probados: {$portsRaw}\n"
);
exit(1);
