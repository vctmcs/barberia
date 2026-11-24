<?php

declare(strict_types=1);

/*
 * Crea la base SQLite a partir de schema.sql y, opcionalmente, carga
 * los datos de prueba de seed.sql.
 *
 * Uso (desde la raiz del proyecto):
 *   php database/instalar.php            esquema + datos de prueba
 *   php database/instalar.php --vacia    solo esquema y un administrador
 *   php database/instalar.php --forzar   borra la base existente y la recrea
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo se ejecuta desde la linea de comandos.');
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;

$args   = array_slice($argv, 1);
$vacia  = in_array('--vacia', $args, true);
$forzar = in_array('--forzar', $args, true);

$file = Database::path();

if (is_file($file)) {
    if (!$forzar) {
        fwrite(STDERR, "La base ya existe en {$file}\nUsa --forzar para recrearla.\n");
        exit(1);
    }
    unlink($file);
}

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "Falta la extension pdo_sqlite. Activala en php.ini (extension=pdo_sqlite).\n");
    exit(1);
}

$pdo = Database::open($file);

$pdo->exec((string) file_get_contents(__DIR__ . '/schema.sql'));
echo "Esquema creado.\n";

if ($vacia) {
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
        'Administrador',
        'admin@barberia.test',
        password_hash('barberia123', PASSWORD_DEFAULT),
        'admin',
    ]);
    echo "Administrador creado: admin@barberia.test / barberia123\n";
} else {
    $pdo->exec((string) file_get_contents(__DIR__ . '/seed.sql'));
    echo "Datos de prueba cargados.\n";

    /* Fotos de ejemplo del historial */
    $destino = BASE_PATH . '/storage/uploads/historial';
    if (!is_dir($destino)) {
        mkdir($destino, 0755, true);
    }
    $copiadas = 0;
    foreach (glob(__DIR__ . '/demo/*.webp') ?: [] as $foto) {
        $copiadas += (int) copy($foto, $destino . '/' . basename($foto));
    }
    echo "Fotos de ejemplo copiadas: {$copiadas}\n";
    echo "Usuarios (contrasena barberia123):\n";
    foreach ($pdo->query('SELECT email, rol FROM usuarios ORDER BY id') as $u) {
        echo "  {$u['email']}  ({$u['rol']})\n";
    }
}

echo "Listo: {$file}\n";
echo "Cambia la contrasena del administrador antes de publicar el sitio.\n";
