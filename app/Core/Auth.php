<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

/**
 * Autenticacion por sesion.
 *
 *  - Contrasenas con password_hash (bcrypt) y rehash automatico.
 *  - Limite de intentos guardado en la base, por IP y correo y por IP
 *    sola, para que no se pueda evadir borrando la cookie.
 *  - Tiempo de respuesta parejo exista o no el correo, para no revelar
 *    que cuentas existen.
 *  - La sesion se cierra tras un periodo sin actividad.
 */
final class Auth
{
    private const VENTANA_MIN       = 15;  // minutos que cuentan los intentos
    private const MAX_POR_CUENTA    = 5;   // misma IP y mismo correo
    private const MAX_POR_IP        = 20;  // misma IP, cualquier correo

    /** Contrasena de los datos de prueba: se avisa si sigue en uso. */
    public const PASSWORD_DEMO = 'barberia123';

    private static ?array $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email));
        $usuario = (new Usuario())->findByEmail($email);

        // Siempre se verifica un hash, aunque el correo no exista
        $hash = $usuario['password_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
        $valida = password_verify($password, $hash);

        if (!$usuario || !(int) $usuario['activo'] || !$valida) {
            self::registrarFallo($email);
            return false;
        }

        if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
            (new Usuario())->updatePassword((int) $usuario['id'], $password);
        }

        session_regenerate_id(true);
        Session::set('auth_id', (int) $usuario['id']);
        Session::set('auth_ultima', time());
        Session::set('auth_pwd_demo', hash_equals(self::PASSWORD_DEMO, $password));

        self::limpiarFallos($email);
        (new Usuario())->touchAcceso((int) $usuario['id']);

        return true;
    }

    public static function user(): ?array
    {
        if (self::$user !== null || !Session::get('auth_id')) {
            return self::$user;
        }

        /* Cierre por inactividad */
        $idle = max(5, (int) Config::get('app.session_idle', 120)) * 60;
        $ultima = (int) Session::get('auth_ultima', 0);
        if ($ultima && time() - $ultima > $idle) {
            // Se quitan solo los datos de acceso para conservar el aviso
            foreach (['auth_id', 'auth_ultima', 'auth_pwd_demo'] as $k) {
                Session::forget($k);
            }
            session_regenerate_id(true);
            Session::flash('info', 'Tu sesión se cerró por inactividad. Vuelve a entrar.');
            return null;
        }
        Session::set('auth_ultima', time());

        $usuario = (new Usuario())->findWithEmpleado((int) Session::get('auth_id'));
        self::$user = ($usuario && (int) $usuario['activo']) ? $usuario : null;

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

    /** True si entro con la contrasena de los datos de prueba. */
    public static function usaPasswordDemo(): bool
    {
        return (bool) Session::get('auth_pwd_demo', false);
    }

    public static function marcarPasswordCambiada(): void
    {
        Session::set('auth_pwd_demo', false);
        self::$user = null;
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

    /** Minutos que faltan para poder intentar de nuevo (0 si puede). */
    public static function bloqueoRestante(string $email = ''): int
    {
        $db = Database::connection();
        $desde = date('Y-m-d H:i:s', time() - self::VENTANA_MIN * 60);
        $ip = self::ip();

        $stmt = $db->prepare('SELECT COUNT(*), MIN(creado_en) FROM login_intentos WHERE ip = ? AND creado_en > ?');
        $stmt->execute([$ip, $desde]);
        [$porIp, $primeroIp] = $stmt->fetch(\PDO::FETCH_NUM);

        $porCuenta = 0;
        $primeroCuenta = null;
        if ($email !== '') {
            $stmt = $db->prepare('SELECT COUNT(*), MIN(creado_en) FROM login_intentos WHERE ip = ? AND email = ? AND creado_en > ?');
            $stmt->execute([$ip, mb_strtolower(trim($email)), $desde]);
            [$porCuenta, $primeroCuenta] = $stmt->fetch(\PDO::FETCH_NUM);
        }

        $desdeBloqueo = null;
        if ((int) $porCuenta >= self::MAX_POR_CUENTA) { $desdeBloqueo = $primeroCuenta; }
        if ((int) $porIp >= self::MAX_POR_IP)         { $desdeBloqueo = $primeroIp; }

        if ($desdeBloqueo === null) {
            return 0;
        }

        $libre = strtotime($desdeBloqueo) + self::VENTANA_MIN * 60;
        return max(1, (int) ceil(($libre - time()) / 60));
    }

    private static function registrarFallo(string $email): void
    {
        $db = Database::connection();
        $db->prepare('INSERT INTO login_intentos (ip, email, creado_en) VALUES (?, ?, ?)')
            ->execute([self::ip(), mb_substr($email, 0, 120), date('Y-m-d H:i:s')]);

        // Limpieza de registros viejos
        $db->prepare('DELETE FROM login_intentos WHERE creado_en < ?')
            ->execute([date('Y-m-d H:i:s', time() - 86400)]);
    }

    private static function limpiarFallos(string $email): void
    {
        Database::connection()
            ->prepare('DELETE FROM login_intentos WHERE ip = ? AND email = ?')
            ->execute([self::ip(), $email]);
    }

    private static function ip(): string
    {
        // REMOTE_ADDR es lo unico que el cliente no puede falsificar
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
    }
}
