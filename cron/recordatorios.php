<?php

declare(strict_types=1);

// Envia los recordatorios por correo pendientes.
// Para hosting con cron real, por ejemplo cada 15 minutos:
//   */15 * * * * php /ruta/al/proyecto/cron/recordatorios.php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo linea de comandos.');
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$r = App\Services\Recordatorios::procesar();

if ($r['sin_configurar']) {
    fwrite(STDERR, "El correo no esta configurado en config/config.php.\n");
    exit(1);
}

printf("Revisadas: %d  Enviados: %d  Fallidos: %d\n", $r['revisadas'], $r['enviados'], $r['fallidos']);
