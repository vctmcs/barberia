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

/** Nombre del mes en espanol. */
function mes_nombre(int $mes): string
{
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return $meses[$mes - 1] ?? '';
}

/** "lunes 23 de noviembre" */
function fecha_larga(string $fecha): string
{
    $dias = ['', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    $t = strtotime($fecha);
    return sprintf('%s %d de %s', $dias[(int) date('N', $t)], (int) date('j', $t), mes_nombre((int) date('n', $t)));
}

/** "23/11/2026" */
function fecha_corta(?string $fecha): string
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

/** "14:30" */
function hora(?string $fechaHora): string
{
    return $fechaHora ? date('H:i', strtotime($fechaHora)) : '';
}

/** "$1,250.00" */
function dinero(float|int|string|null $monto): string
{
    return '$' . number_format((float) $monto, 2);
}

/** Texto visible de cada estado de cita. */
function estado_cita(string $estado): string
{
    return [
        'programada' => 'Programada',
        'atendida'   => 'Atendida',
        'cancelada'  => 'Cancelada',
        'no_asistio' => 'No asistió',
    ][$estado] ?? $estado;
}

/** Iniciales para avatares: "Manuel Ortega" -> "MO" */
function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre)) ?: [];
    $ini = '';
    foreach (array_slice($partes, 0, 2) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $ini;
}

/** Valor previo de un campo despues de un error de validacion. */
function old(array $old, string $key, mixed $default = ''): string
{
    return e($old[$key] ?? $default);
}

/** URL de una foto guardada en storage/uploads/{carpeta}. */
function foto_url(?string $archivo, string $carpeta): ?string
{
    return $archivo ? url("/archivos/{$carpeta}/" . rawurlencode($archivo)) : null;
}

/** Telefono a 10 digitos con formato "55 1234 5678". */
function telefono(?string $tel): string
{
    if (!$tel || strlen($tel) !== 10) {
        return (string) $tel;
    }
    return substr($tel, 0, 2) . ' ' . substr($tel, 2, 4) . ' ' . substr($tel, 6);
}

/** Clase de error para un campo del formulario. */
function field_class(array $errors, string $campo): string
{
    return isset($errors[$campo]) ? 'field field--error' : 'field';
}

/** Mensaje de error de un campo, ya escapado. */
function field_error(array $errors, string $campo): string
{
    return isset($errors[$campo])
        ? '<p class="field__error" id="err-' . e($campo) . '">' . e($errors[$campo]) . '</p>'
        : '';
}

/** Valor de un campo: lo capturado tras un error, o el valor guardado. */
function val(array $old, array $registro, string $campo): string
{
    return e($old ? ($old[$campo] ?? '') : ($registro[$campo] ?? ''));
}

/** Checkbox marcado segun lo capturado o el valor guardado. */
function checked(array $old, array $registro, string $campo): string
{
    $on = $old ? isset($old[$campo]) : !empty($registro[$campo]);
    return $on ? 'checked' : '';
}

/** Construye la query string conservando los filtros actuales. */
function query(array $filtros, array $cambios = []): string
{
    $params = array_filter(array_merge($filtros, $cambios), fn ($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}

/** "hace 3 semanas" a partir de una fecha pasada. */
function hace(?string $fecha): string
{
    if (!$fecha) {
        return 'Sin visitas';
    }
    $dias = (int) (new DateTime(substr($fecha, 0, 10)))->diff(new DateTime('today'))->days;
    return match (true) {
        $dias === 0  => 'Hoy',
        $dias === 1  => 'Ayer',
        $dias < 14   => "Hace {$dias} días",
        $dias < 60   => 'Hace ' . intdiv($dias, 7) . ' semanas',
        $dias < 365  => 'Hace ' . intdiv($dias, 30) . ' meses',
        default      => 'Hace más de un año',
    };
}

/** Enlace de WhatsApp con mensaje prellenado (numeros de Mexico). */
function whatsapp_url(?string $telefono, string $mensaje = ''): string
{
    $tel = preg_replace('/\D/', '', (string) $telefono) ?? '';
    if (strlen($tel) === 10) {
        $tel = '52' . $tel;
    }
    return 'https://wa.me/' . $tel . ($mensaje !== '' ? '?text=' . rawurlencode($mensaje) : '');
}

/** "60 min" o "2 h 30 min" */
function duracion(int|string $minutos): string
{
    $m = (int) $minutos;
    if ($m < 60) {
        return "{$m} min";
    }
    $h = intdiv($m, 60);
    $r = $m % 60;
    return $r ? "{$h} h {$r} min" : "{$h} h";
}

/** Precio sin centavos cuando son cero: "$1,400" */
function precio(float|int|string $monto): string
{
    $monto = (float) $monto;
    return '$' . number_format($monto, fmod($monto, 1.0) == 0.0 ? 0 : 2);
}
