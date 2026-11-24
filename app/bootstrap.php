<?php

declare(strict_types=1);

/*
 * Arranque de la aplicacion: configuracion, autoload, sesion y
 * manejo de errores. Lo usan index.php y los scripts de cron/.
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$configFile = BASE_PATH . '/config/config.php';

if (!is_file($configFile)) {
    http_response_code(500);
    exit('Falta config/config.php. Copia config/config.example.php y ajusta tus datos.');
}

$config = require $configFile;

date_default_timezone_set($config['app']['timezone']);

/* Autoload PSR-4 propio: App\Core\Router -> app/Core/Router.php */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

/* PHPMailer incluido en lib/ (sin Composer, para subir por FTP) */
spl_autoload_register(function (string $class): void {
    $prefix = 'PHPMailer\\PHPMailer\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = BASE_PATH . '/lib/PHPMailer/' . substr($class, strlen($prefix)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

/* Dependencias de Composer, si en algun momento se agregan */
$vendor = BASE_PATH . '/vendor/autoload.php';
if (is_file($vendor)) {
    require $vendor;
}

$config['sitio'] = is_file(BASE_PATH . '/config/sitio.php') ? require BASE_PATH . '/config/sitio.php' : [];

App\Core\Config::load($config);

require BASE_PATH . '/app/helpers.php';

if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
}

/*
 * Errores no atrapados: se registran en storage/logs y se muestra una
 * pagina 500 sin detalles (con detalles solo cuando debug esta activo).
 */
set_exception_handler(static function (\Throwable $e) use ($config): void {
    error_log(sprintf('[%s] %s en %s:%d', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        exit(1);
    }

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($config['app']['debug']) {
        echo '<pre style="padding:1rem;white-space:pre-wrap">' . e($e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>';
        return;
    }

    try {
        (new App\Core\View())->render('errors/500', ['title' => 'Error'], 'layouts/blank');
    } catch (\Throwable) {
        echo 'Ocurrió un error. Intenta de nuevo en unos minutos.';
    }
});

/*
 * Encabezados de seguridad para el navegador.
 * La politica de contenido solo permite scripts, estilos, fuentes e
 * imagenes del propio sitio; los atributos style en linea si se
 * permiten porque las vistas los usan para colores dinamicos.
 * form-action incluye wa.me porque el recordatorio redirige a WhatsApp.
 */
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), payment=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; "
        . "frame-ancestors 'self'; form-action 'self' https://wa.me https://api.whatsapp.com");
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name($config['app']['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}
