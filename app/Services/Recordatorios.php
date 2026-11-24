<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\View;
use App\Models\Cita;

/**
 * Recordatorio automatico por correo antes de cada cita.
 *
 * Como InfinityFree gratuito no tiene tareas programadas, la revision
 * se dispara con las visitas al sitio (como maximo una vez cada
 * reminders.check_interval minutos). En un hosting con cron real se
 * puede ejecutar cron/recordatorios.php.
 */
final class Recordatorios
{
    /** Maximo de correos por revision, para no alargar la peticion. */
    private const LOTE = 10;

    /** Espera antes de reintentar un envio fallido. */
    private const REINTENTO_MIN = 60;

    private static function archivoEstado(): string
    {
        return BASE_PATH . '/storage/recordatorios.json';
    }

    /**
     * Revisa si toca enviar. Se llama al terminar cada peticion web.
     * Usa un candado de archivo para que dos visitas simultaneas no
     * manden el mismo correo.
     */
    public static function quizas(): void
    {
        $intervalo = max(1, (int) Config::get('reminders.check_interval', 15)) * 60;
        $estado = self::estado();

        if (time() - (int) ($estado['ultima'] ?? 0) < $intervalo) {
            return;
        }

        $candado = @fopen(BASE_PATH . '/storage/recordatorios.lock', 'c');
        if (!$candado || !flock($candado, LOCK_EX | LOCK_NB)) {
            return; // otra peticion ya lo esta haciendo
        }

        try {
            // Releer dentro del candado por si otra peticion acaba de terminar
            $estado = self::estado();
            if (time() - (int) ($estado['ultima'] ?? 0) >= $intervalo) {
                self::procesar();
            }
        } catch (\Throwable $e) {
            error_log('Recordatorios: ' . $e->getMessage());
        } finally {
            flock($candado, LOCK_UN);
            fclose($candado);
        }
    }

    /**
     * Envia los recordatorios pendientes.
     *
     * @return array{revisadas:int, enviados:int, fallidos:int, sin_configurar:bool}
     */
    public static function procesar(): array
    {
        $res = ['revisadas' => 0, 'enviados' => 0, 'fallidos' => 0, 'sin_configurar' => !Correo::configurado()];

        if (!$res['sin_configurar']) {
            $citas = new Cita();
            foreach (self::pendientes(self::LOTE) as $c) {
                $res['revisadas']++;
                $r = self::enviarA($c);
                $citas->registrarRecordatorio((int) $c['id'], 'correo', $r['ok'] ? 'enviado' : 'fallido', $r['ok'] ? null : $r['error']);
                $r['ok'] ? $res['enviados']++ : $res['fallidos']++;
            }
        }

        self::guardarEstado($res);
        return $res;
    }

    /**
     * Citas programadas que empiezan dentro de la ventana de aviso, cuyo
     * cliente tiene correo y que aun no tienen recordatorio enviado.
     */
    public static function pendientes(int $limite = 50): array
    {
        $horas = max(1, (int) Config::get('reminders.hours_before', 24));
        $ahora = date('Y-m-d H:i:s');
        $hasta = date('Y-m-d H:i:s', time() + $horas * 3600);
        $reintento = date('Y-m-d H:i:s', time() - self::REINTENTO_MIN * 60);

        $stmt = Database::connection()->prepare(
            "SELECT c.id, c.fecha_hora, c.fecha_fin, c.precio,
                    cl.nombre AS cliente, cl.email,
                    s.nombre AS servicio, s.duracion_min, e.nombre AS empleado,
                    r.estado AS rec_estado, r.detalle AS rec_detalle
               FROM citas c
               JOIN clientes cl ON cl.id = c.cliente_id
               JOIN servicios s ON s.id = c.servicio_id
               JOIN empleados e ON e.id = c.empleado_id
          LEFT JOIN recordatorios r ON r.cita_id = c.id AND r.canal = 'correo'
              WHERE c.estado = 'programada'
                AND c.fecha_hora > :ahora AND c.fecha_hora <= :hasta
                AND cl.email IS NOT NULL AND cl.email <> ''
                AND (r.id IS NULL OR (r.estado = 'fallido' AND r.enviado_en < :reintento))
           ORDER BY c.fecha_hora
              LIMIT :lim"
        );
        $stmt->bindValue('ahora', $ahora);
        $stmt->bindValue('hasta', $hasta);
        $stmt->bindValue('reintento', $reintento);
        $stmt->bindValue('lim', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Ultimos recordatorios por correo, para el panel de notificaciones. */
    public static function recientes(int $limite = 8): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.estado, r.enviado_en, r.detalle, c.id AS cita_id, c.fecha_hora, cl.nombre AS cliente, cl.email
               FROM recordatorios r
               JOIN citas c ON c.id = r.cita_id
               JOIN clientes cl ON cl.id = c.cliente_id
              WHERE r.canal = 'correo'
           ORDER BY r.enviado_en DESC
              LIMIT " . (int) $limite
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array{ok:bool, error?:string} */
    public static function enviarA(array $c): array
    {
        $datos = self::datosCorreo($c);
        $html  = self::renderCorreo('emails/recordatorio', $datos);
        $texto = self::renderCorreo('emails/recordatorio-texto', $datos);

        return Correo::enviar(
            (string) $c['email'],
            (string) $c['cliente'],
            'Tu cita en ' . Config::get('app.name') . ' es ' . $datos['cuando'],
            $html,
            $texto
        );
    }

    /** Correo de prueba para verificar la configuracion SMTP. */
    public static function prueba(string $para, string $nombre): array
    {
        $manana = date('Y-m-d', strtotime('+1 day'));
        $datos = self::datosCorreo([
            'cliente'      => $nombre,
            'fecha_hora'   => "{$manana} 12:00:00",
            'fecha_fin'    => "{$manana} 13:00:00",
            'servicio'     => 'Corte de dama (correo de prueba)',
            'duracion_min' => 60,
            'empleado'     => 'Tu estilista',
            'precio'       => 280,
        ]);

        return Correo::enviar(
            $para,
            $nombre,
            'Prueba de recordatorio de ' . Config::get('app.name'),
            self::renderCorreo('emails/recordatorio', $datos),
            self::renderCorreo('emails/recordatorio-texto', $datos)
        );
    }

    public static function estado(): array
    {
        $file = self::archivoEstado();
        if (!is_file($file)) {
            return [];
        }
        return json_decode((string) file_get_contents($file), true) ?: [];
    }

    private static function guardarEstado(array $res): void
    {
        @file_put_contents(self::archivoEstado(), json_encode(['ultima' => time()] + $res), LOCK_EX);
    }

    private static function datosCorreo(array $c): array
    {
        $sitio = (array) Config::get('sitio', []);
        $dia = date('Y-m-d', strtotime($c['fecha_hora']));

        $cuando = match ($dia) {
            date('Y-m-d')                       => 'hoy a las ' . hora($c['fecha_hora']),
            date('Y-m-d', strtotime('+1 day'))  => 'mañana a las ' . hora($c['fecha_hora']),
            default                             => 'el ' . fecha_larga($c['fecha_hora']) . ' a las ' . hora($c['fecha_hora']),
        };

        return [
            'negocio'   => (string) Config::get('app.name'),
            'nombre'    => explode(' ', (string) $c['cliente'])[0],
            'cuando'    => $cuando,
            'fecha'     => ucfirst(fecha_larga($c['fecha_hora'])),
            'horario'   => hora($c['fecha_hora']) . ' a ' . hora($c['fecha_fin']),
            'servicio'  => (string) $c['servicio'],
            'empleado'  => (string) $c['empleado'],
            'precio'    => dinero($c['precio']),
            'direccion' => (string) ($sitio['direccion'] ?? ''),
            'referencia'=> (string) ($sitio['referencia'] ?? ''),
            'whatsapp'  => !empty($sitio['whatsapp'])
                ? whatsapp_url($sitio['whatsapp'], 'Hola, necesito cambiar mi cita del ' . fecha_larga($c['fecha_hora']) . ' a las ' . hora($c['fecha_hora']) . '.')
                : null,
            'mapa'      => !empty($sitio['direccion'])
                ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) $sitio['direccion'])
                : null,
            'sitioUrl'  => url('/'),
        ];
    }

    private static function renderCorreo(string $vista, array $datos): string
    {
        ob_start();
        (new View())->render($vista, $datos, '');
        return (string) ob_get_clean();
    }
}
