<?php

declare(strict_types=1);

/*
 * Punto de entrada unico de la aplicacion (front controller).
 * Todas las peticiones llegan aqui gracias al .htaccess y el
 * enrutador decide que controlador y que accion ejecutar.
 */

define('BASE_PATH', __DIR__);

/* Con el servidor integrado (php -S) los archivos de public/ se sirven tal cual */
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (str_starts_with(realpath($file) ?: '', __DIR__ . DIRECTORY_SEPARATOR . 'public') && is_file($file)) {
        return false;
    }
}

require BASE_PATH . '/app/bootstrap.php';

$router = new App\Core\Router();

require BASE_PATH . '/config/routes.php';

/*
 * Recordatorios por correo: se revisan al terminar la peticion, despues
 * de entregar la respuesta (si el servidor lo permite), para no hacer
 * esperar al visitante. Corre aunque el controlador haga exit().
 */
register_shutdown_function(static function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    App\Services\Recordatorios::quizas();
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
