<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Pantalla inicial temporal para comprobar que el enrutador y las
 * vistas funcionan. En la siguiente entrega se reemplaza por el
 * login y el dashboard.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title'   => 'Inicio',
            'phpVersion' => PHP_VERSION,
        ], 'layouts/blank');
    }
}
