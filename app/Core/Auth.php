<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

/**
 * Autenticacion por sesion con contrasenas password_hash y un
 * limite simple de intentos fallidos por sesion.
 */
final class Auth
{
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_SEG  = 300;

    private static ?array $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $usuario = (new Usuario())->findByEmail($email);

        if (!$usuario || !(int) $usuario['activo'] || !password_verify($password, $usuario['password_hash'])) {
            self::registrarFallo();
            return false;
        }

        if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
            (new Usuario())->updatePassword((int) $usuario['id'], $password);
        }

        session_regenerate_id(true);
        Session::set('auth_id', (int) $usuario['id']);
        Session::forget('login_fallos');
        Session::forget('login_bloqueo');

        (new Usuario())->touchAcceso((int) $usuario['id']);

        return true;
    }

    public static function user(): ?array
    {
        if (self::$user === null && Session::get('auth_id')) {
            $usuario = (new Usuario())->findWithEmpleado((int) Session::get('auth_id'));
            self::$user = ($usuario && (int) $usuario['activo']) ? $usuario : null;
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['rol'] ?? '') === 'admin';
    }

    /** Id del empleado ligado al usuario actual (barberos). */
    public static function empleadoId(): ?int
    {
        $id = self::user()['empleado_id'] ?? null;
        return $id !== null ? (int) $id : null;
    }

    public static function logout(): void
    {
        self::$user = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    /** Segundos restantes de bloqueo, 0 si puede intentar. */
    public static function bloqueoRestante(): int
    {
        $hasta = (int) Session::get('login_bloqueo', 0);
        return max(0, $hasta - time());
    }

    private static function registrarFallo(): void
    {
        $fallos = (int) Session::get('login_fallos', 0) + 1;
        Session::set('login_fallos', $fallos);

        if ($fallos >= self::MAX_INTENTOS) {
            Session::set('login_bloqueo', time() + self::BLOQUEO_SEG);
            Session::set('login_fallos', 0);
        }
    }
}
