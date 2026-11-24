<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Usuario;

/**
 * Datos y contrasena del propio usuario. Cambiar el correo o la
 * contrasena exige escribir la contrasena actual.
 */
final class CuentaController extends Controller
{
    public function edit(): void
    {
        $this->requireAuth();
        $u = Auth::user();

        $this->view('cuenta/index', [
            'title'   => 'Mi cuenta',
            'tour'    => 'cuenta',
            'cuenta'  => $u,
            'ultimoAcceso' => (new Usuario())->find((int) $u['id'])['ultimo_acceso'] ?? null,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $model   = new Usuario();
        $usuario = $model->find((int) Auth::id());
        $nombre  = trim((string) ($_POST['nombre'] ?? ''));
        $email   = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $actual  = (string) ($_POST['password_actual'] ?? '');
        $nueva   = (string) ($_POST['password_nueva'] ?? '');
        $repetir = (string) ($_POST['password_repetir'] ?? '');

        $cambiaEmail = $email !== $usuario['email'];
        $cambiaPass  = $nueva !== '';
        $errores = [];

        if ($nombre === '' || mb_strlen($nombre) > 80) {
            $errores['nombre'] = 'Escribe tu nombre (hasta 80 caracteres).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Escribe un correo válido.';
        } elseif ($cambiaEmail && $model->emailUsado($email, (int) $usuario['id'])) {
            $errores['email'] = 'Ese correo ya lo usa otra cuenta.';
        }

        if ($cambiaPass) {
            if (mb_strlen($nueva) < 8) {
                $errores['password_nueva'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            } elseif ($nueva === Auth::PASSWORD_DEMO) {
                $errores['password_nueva'] = 'Elige una contraseña distinta a la de los datos de prueba.';
            } elseif (hash_equals($nueva, $actual)) {
                $errores['password_nueva'] = 'La nueva contraseña debe ser distinta a la actual.';
            } elseif (!hash_equals($nueva, $repetir)) {
                $errores['password_repetir'] = 'Las contraseñas nuevas no coinciden.';
            }
        }

        if (($cambiaEmail || $cambiaPass) && !password_verify($actual, $usuario['password_hash'])) {
            $errores['password_actual'] = 'Para cambiar el correo o la contraseña escribe tu contraseña actual correcta.';
        }

        if ($errores) {
            $this->back('/cuenta', $errores, ['nombre' => $nombre, 'email' => $email]);
        }

        $db = Database::connection();
        $db->beginTransaction();
        $model->update((int) $usuario['id'], ['nombre' => $nombre, 'email' => $email]);
        if ($cambiaPass) {
            $model->updatePassword((int) $usuario['id'], $nueva);
        }
        // Mantener el nombre del empleado ligado en sincronia
        $db->prepare('UPDATE empleados SET nombre = ? WHERE usuario_id = ?')->execute([$nombre, $usuario['id']]);
        $db->commit();

        if ($cambiaPass) {
            session_regenerate_id(true);
            Auth::marcarPasswordCambiada();
        }

        Session::flash('success', $cambiaPass ? 'Datos y contraseña actualizados.' : 'Datos actualizados.');
        $this->redirect('/cuenta');
    }
}
