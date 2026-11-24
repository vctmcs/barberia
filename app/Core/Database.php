<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexion PDO unica (singleton) a la base SQLite.
 */
final class Database
{
    /** Debe coincidir con el PRAGMA user_version al final de schema.sql */
    public const SCHEMA_VERSION = 3;

    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $file = self::path();

            if (!is_file($file)) {
                throw new \RuntimeException(
                    'No existe la base de datos. Ejecuta: php database/instalar.php'
                );
            }

            self::$pdo = self::open($file);
            self::migrar(self::$pdo);
        }

        return self::$pdo;
    }

    /** Ruta absoluta del archivo .sqlite segun la configuracion. */
    public static function path(): string
    {
        $path = (string) Config::get('db.path', 'database/barberia.sqlite');

        $isAbsolute = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path);

        return $isAbsolute ? $path : BASE_PATH . '/' . $path;
    }

    /**
     * Si la base es de una version anterior, vuelve a aplicar schema.sql.
     * Todo el esquema usa IF NOT EXISTS, asi que solo crea lo que falta.
     * Sirve en hosting sin linea de comandos, como InfinityFree.
     */
    private static function migrar(PDO $pdo): void
    {
        $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();

        if ($version < self::SCHEMA_VERSION) {
            $pdo->exec((string) file_get_contents(BASE_PATH . '/database/schema.sql'));
        }
    }

    public static function open(string $file): PDO
    {
        $pdo = new PDO('sqlite:' . $file, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 5,
        ]);

        // SQLite no revisa llaves foraneas si no se activa en cada conexion
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        return $pdo;
    }
}
