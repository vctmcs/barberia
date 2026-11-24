<?php

declare(strict_types=1);

/*
 * Punto de entrada unico de la aplicacion (front controller).
 * Todas las peticiones llegan aqui gracias al .htaccess y el
 * enrutador decide que controlador y que accion ejecutar.
 */

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/bootstrap.php';

$router = new App\Core\Router();

require BASE_PATH . '/config/routes.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
