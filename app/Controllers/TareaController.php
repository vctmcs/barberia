<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Services\Recordatorios;

/**
 * Ejecucion de tareas por URL con token, para servicios externos de
 * cron en hostings que lo permitan:
 *   /tareas/recordatorios?token=...
 */
final class TareaController extends Controller
{
    public function recordatorios(): void
    {
        $token = (string) Config::get('reminders.cron_token', '');
        $dado  = (string) ($_GET['token'] ?? '');

        if ($token === '' || str_starts_with($token, 'cambia-este-token') || !hash_equals($token, $dado)) {
            $this->json(['ok' => false], 403);
        }

        $this->json(['ok' => true] + Recordatorios::procesar());
    }
}
