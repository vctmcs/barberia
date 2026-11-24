<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Uploader;

/**
 * Entrega las fotos guardadas en storage/ solo a usuarios con sesion.
 * La carpeta storage/ esta bloqueada al acceso directo por .htaccess.
 */
final class ArchivoController extends Controller
{
    private const CARPETAS = ['clientes', 'historial'];

    private const MIME = [
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    public function show(string $carpeta, string $archivo): void
    {
        $this->requireAuth();

        $archivo = basename($archivo);
        $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $ruta = Uploader::dir($carpeta) . '/' . $archivo;

        if (!in_array($carpeta, self::CARPETAS, true)
            || !preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $archivo)
            || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . self::MIME[$ext]);
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: private, max-age=604800');
        header('X-Content-Type-Options: nosniff');
        readfile($ruta);
        exit;
    }
}
