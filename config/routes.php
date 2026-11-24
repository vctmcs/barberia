<?php

/*
 * Tabla de rutas. Formato:
 * $router->get('/ruta/{param}', [Controlador::class, 'accion']);
 *
 * Las rutas fijas (/clientes/nuevo) van antes que las de parametro
 * (/clientes/{id}) porque el enrutador toma la primera coincidencia.
 *
 * @var App\Core\Router $router
 */

use App\Controllers\ArchivoController;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\DashboardController;
use App\Controllers\EmpleadoController;
use App\Controllers\HomeController;
use App\Controllers\RecorridoController;
use App\Controllers\ServicioController;

$router->get('/', [HomeController::class, 'index']);

/* Autenticacion */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

/* Dashboard */
$router->get('/dashboard', [DashboardController::class, 'index']);

/* Clientes */
$router->get('/clientes', [ClienteController::class, 'index']);
$router->get('/clientes/nuevo', [ClienteController::class, 'create']);
$router->post('/clientes', [ClienteController::class, 'store']);
$router->get('/clientes/{id}', [ClienteController::class, 'show']);
$router->get('/clientes/{id}/editar', [ClienteController::class, 'edit']);
$router->post('/clientes/{id}/actualizar', [ClienteController::class, 'update']);
$router->post('/clientes/{id}/estado', [ClienteController::class, 'toggle']);
$router->post('/clientes/{id}/eliminar', [ClienteController::class, 'destroy']);

/* Servicios (solo administrador) */
$router->get('/servicios', [ServicioController::class, 'index']);
$router->get('/servicios/nuevo', [ServicioController::class, 'create']);
$router->post('/servicios', [ServicioController::class, 'store']);
$router->get('/servicios/{id}/editar', [ServicioController::class, 'edit']);
$router->post('/servicios/{id}/actualizar', [ServicioController::class, 'update']);
$router->post('/servicios/{id}/estado', [ServicioController::class, 'toggle']);
$router->post('/servicios/{id}/eliminar', [ServicioController::class, 'destroy']);

/* Empleados (solo administrador) */
$router->get('/empleados', [EmpleadoController::class, 'index']);
$router->get('/empleados/nuevo', [EmpleadoController::class, 'create']);
$router->post('/empleados', [EmpleadoController::class, 'store']);
$router->get('/empleados/{id}/editar', [EmpleadoController::class, 'edit']);
$router->post('/empleados/{id}/actualizar', [EmpleadoController::class, 'update']);
$router->post('/empleados/{id}/estado', [EmpleadoController::class, 'toggle']);
$router->post('/empleados/{id}/eliminar', [EmpleadoController::class, 'destroy']);

/* Recorridos guiados */
$router->post('/recorridos/{clave}', [RecorridoController::class, 'visto']);

/* Fotos privadas */
$router->get('/archivos/{carpeta}/{archivo}', [ArchivoController::class, 'show']);
