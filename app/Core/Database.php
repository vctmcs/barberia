<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexion PDO unica (singleton) a MySQL.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                Config::get('db.host'),
                (int) Config::get('db.port', 3306),
                Config::get('db.name'),
                Config::get('db.charset', 'utf8mb4')
            );

            self::$pdo = new PDO($dsn, Config::get('db.user'), Config::get('db.password'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Mantener la base en la misma zona horaria que PHP
            self::$pdo->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
        }

        return self::$pdo;
    }
}
