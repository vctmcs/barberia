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

/* Dependencias de Composer (PHPMailer), si existen */
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
