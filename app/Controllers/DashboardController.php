<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Dashboard;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $hoy        = date('Y-m-d');
        $empleadoId = Auth::isAdmin() ? null : Auth::empleadoId();
        $dashboard  = new Dashboard();

        $citas = $dashboard->citasDelDia($hoy, $empleadoId);
        $pendientes = array_filter($citas, fn ($c) => $c['estado'] === 'programada');

        $top = $dashboard->serviciosTop();
        $max = $top ? max(array_column($top, 'total')) : 1;
        foreach ($top as &$s) {
            $s['pct'] = (int) round($s['total'] / $max * 100);
        }
        unset($s);

        $this->view('dashboard/index', [
            'title'       => 'Dashboard',
            'tour'        => 'dashboard',
            'fechaLarga'  => fecha_larga($hoy),
            'citas'       => $citas,
            'pendientes'  => count($pendientes),
            'ingresosMes' => $dashboard->ingresosDelMes($hoy, $empleadoId),
            'clientes'    => $dashboard->totalClientesActivos(),
            'topServicios'=> $top,
            'mesNombre'   => mes_nombre((int) date('n')),
        ]);
    }
}
