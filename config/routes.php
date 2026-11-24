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
use App\Controllers\CitaController;
use App\Controllers\AuthController;
use App\Controllers\ClienteController;
use App\Controllers\DashboardController;
use App\Controllers\EmpleadoController;
use App\Controllers\HistorialController;
use App\Controllers\HomeController;
use App\Controllers\NotificacionController;
use App\Controllers\RecorridoController;
use App\Controllers\ServicioController;
use App\Controllers\TareaController;

$router->get('/', [HomeController::class, 'index']);

/* Autenticacion */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

/* Dashboard */
$router->get('/dashboard', [DashboardController::class, 'index']);

/* Agenda de citas */
$router->get('/citas', [CitaController::class, 'index']);
$router->get('/citas/eventos', [CitaController::class, 'eventos']);
$router->get('/citas/disponibilidad', [CitaController::class, 'disponibilidad']);
$router->get('/citas/nueva', [CitaController::class, 'create']);
$router->post('/citas', [CitaController::class, 'store']);
$router->get('/citas/{id}', [CitaController::class, 'show']);
$router->get('/citas/{id}/editar', [CitaController::class, 'edit']);
$router->post('/citas/{id}/actualizar', [CitaController::class, 'update']);
$router->post('/citas/{id}/estado', [CitaController::class, 'estado']);
$router->post('/citas/{id}/mover', [CitaController::class, 'mover']);
$router->post('/citas/{id}/whatsapp', [CitaController::class, 'whatsapp']);

/* Clientes */
$router->get('/clientes', [ClienteController::class, 'index']);
$router->get('/clientes/nuevo', [ClienteController::class, 'create']);
$router->post('/clientes', [ClienteController::class, 'store']);
$router->get('/clientes/{id}', [ClienteController::class, 'show']);
$router->get('/clientes/{id}/editar', [ClienteController::class, 'edit']);
$router->post('/clientes/{id}/actualizar', [ClienteController::class, 'update']);
$router->post('/clientes/{id}/estado', [ClienteController::class, 'toggle']);
$router->post('/clientes/{id}/eliminar', [ClienteController::class, 'destroy']);

/* Historial del cliente */
$router->get('/historial/nuevo', [HistorialController::class, 'create']);
$router->post('/historial', [HistorialController::class, 'store']);
$router->get('/historial/{id}/editar', [HistorialController::class, 'edit']);
$router->post('/historial/{id}/actualizar', [HistorialController::class, 'update']);
$router->post('/historial/{id}/eliminar', [HistorialController::class, 'destroy']);

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

/* Notificaciones y recordatorios */
$router->get('/notificaciones', [NotificacionController::class, 'index']);
$router->post('/notificaciones/recordatorios', [NotificacionController::class, 'enviar']);
$router->post('/notificaciones/correo-prueba', [NotificacionController::class, 'prueba']);
$router->get('/tareas/recordatorios', [TareaController::class, 'recordatorios']);

/* Recorridos guiados */
$router->post('/recorridos/{clave}', [RecorridoController::class, 'visto']);

/* Fotos privadas */
$router->get('/archivos/{carpeta}/{archivo}', [ArchivoController::class, 'show']);
