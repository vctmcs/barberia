<?php

/*
 * Tabla de rutas. Formato:
 * $router->get('/ruta/{param}', [Controlador::class, 'accion']);
 *
 * @var App\Core\Router $router
 */

use App\Controllers\HomeController;

$router->get('/', [HomeController::class, 'index']);
