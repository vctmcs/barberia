<?php

declare(strict_types=1);

/*
 * Funciones globales pequenas que usan las vistas.
 */

use App\Core\Config;

/** Escapa texto para imprimirlo en HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Construye una URL absoluta a partir de una ruta interna. */
function url(string $path = '/'): string
{
    return rtrim((string) Config::get('app.url'), '/') . '/' . ltrim($path, '/');
}

/** URL de un archivo dentro de public/ (css, js, img). */
function asset(string $path): string
{
    $file = BASE_PATH . '/public/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('public/' . ltrim($path, '/')) . $version;
}

/** Marca como activo un enlace del menu si la ruta actual empieza igual. */
function active(string $prefix): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return str_contains($uri, '/' . trim($prefix, '/')) ? 'is-active' : '';
}
