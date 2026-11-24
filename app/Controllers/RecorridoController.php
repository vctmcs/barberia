<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Recorrido;

final class RecorridoController extends Controller
{
    /** Marca un recorrido como visto (al terminarlo o saltarlo). */
    public function visto(string $clave): void
    {
        if (!Auth::check()) {
            $this->json(['ok' => false], 401);
        }
        if (!Session::validCsrf($_POST['_csrf'] ?? null)) {
            $this->json(['ok' => false], 419);
        }
        if (!in_array($clave, Recorrido::CLAVES, true)) {
            $this->json(['ok' => false], 404);
        }

        (new Recorrido())->marcar((int) Auth::id(), $clave);
        $this->json(['ok' => true]);
    }
}
