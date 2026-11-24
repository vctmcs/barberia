<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Models\Historial;

/**
 * Notificaciones internas del personal. Las de citas se limitan al
 * estilista cuando no es administrador; cumpleanos y clientes que no
 * regresan son de toda la estetica.
 */
final class Avisos
{
    private \PDO $db;

    public function __construct(private ?int $empleadoId)
    {
        $this->db = Database::connection();
    }

    /** Citas programadas de hoy que aun no terminan. */
    public function citasHoy(): array
    {
        return $this->citas(
            "c.estado = 'programada' AND date(c.fecha_hora) = :hoy AND c.fecha_fin >= :ahora",
            ['hoy' => date('Y-m-d'), 'ahora' => date('Y-m-d H:i:s')],
            'c.fecha_hora'
        );
    }

    /** Citas cuya hora ya paso y siguen como programadas. */
    public function sinCerrar(): array
    {
        return $this->citas(
            "c.estado = 'programada' AND c.fecha_fin < :ahora",
            ['ahora' => date('Y-m-d H:i:s')],
            'c.fecha_hora DESC'
        );
    }

    public function sinHistorial(): array
    {
        return (new Historial())->atendidasSinHistorial($this->empleadoId, 20);
    }

    /** Clientes activos que cumplen anos en los proximos $dias dias (incluye hoy). */
    public function cumpleanos(int $dias = 7): array
    {
        $fechas = [];
        for ($i = 0; $i < $dias; $i++) {
            $t = strtotime("+{$i} day", strtotime('today'));
            $fechas[date('m-d', $t)] = ['en' => $i, 'fecha' => date('Y-m-d', $t)];
        }

        $marcadores = implode(',', array_fill(0, count($fechas), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, nombre, telefono, fecha_nacimiento, strftime('%m-%d', fecha_nacimiento) AS md
               FROM clientes
              WHERE activo = 1 AND fecha_nacimiento IS NOT NULL
                AND strftime('%m-%d', fecha_nacimiento) IN ({$marcadores})"
        );
        $stmt->execute(array_keys($fechas));

        $lista = [];
        foreach ($stmt->fetchAll() as $c) {
            $info = $fechas[$c['md']];
            $lista[] = $c + [
                'en'     => $info['en'],
                'dia'    => $info['fecha'],
                'cumple' => (int) substr($info['fecha'], 0, 4) - (int) substr($c['fecha_nacimiento'], 0, 4),
            ];
        }
        usort($lista, fn ($a, $b) => $a['en'] <=> $b['en'] ?: strcmp($a['nombre'], $b['nombre']));

        return $lista;
    }

    /**
     * Clientes activos cuya ultima visita atendida fue hace mas de N
     * semanas y que no tienen cita proxima.
     */
    public function inactivos(?int $semanas = null, int $limite = 15): array
    {
        $semanas ??= max(1, (int) Config::get('notifications.inactive_weeks', 6));
        $limiteFecha = date('Y-m-d H:i:s', strtotime("-{$semanas} weeks"));

        $stmt = $this->db->prepare(
            "SELECT cl.id, cl.nombre, cl.telefono, u.ultima,
                    (SELECT s.nombre FROM citas c2 JOIN servicios s ON s.id = c2.servicio_id
                      WHERE c2.cliente_id = cl.id AND c2.estado = 'atendida'
                   ORDER BY c2.fecha_hora DESC LIMIT 1) AS ultimo_servicio
               FROM clientes cl
               JOIN (SELECT cliente_id, MAX(fecha_hora) AS ultima
                       FROM citas WHERE estado = 'atendida' GROUP BY cliente_id) u ON u.cliente_id = cl.id
              WHERE cl.activo = 1
                AND u.ultima < :limite
                AND NOT EXISTS (SELECT 1 FROM citas f
                                 WHERE f.cliente_id = cl.id AND f.estado = 'programada' AND f.fecha_hora >= :ahora)
           ORDER BY u.ultima
              LIMIT :lim"
        );
        $stmt->bindValue('limite', $limiteFecha);
        $stmt->bindValue('ahora', date('Y-m-d H:i:s'));
        $stmt->bindValue('lim', $limite, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Numero para el indicador del menu: lo que requiere accion hoy
     * (citas sin cerrar, resultados sin registrar y cumpleanos de hoy).
     */
    public function conteo(): int
    {
        $filtro = $this->empleadoId !== null ? ' AND c.empleado_id = :emp' : '';
        $emp = $this->empleadoId !== null ? ['emp' => $this->empleadoId] : [];

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM citas c WHERE c.estado = 'programada' AND c.fecha_fin < :ahora" . $filtro
        );
        $stmt->execute(['ahora' => date('Y-m-d H:i:s')] + $emp);
        $sinCerrar = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM citas c LEFT JOIN historial h ON h.cita_id = c.id
              WHERE c.estado = 'atendida' AND h.id IS NULL" . $filtro
        );
        $stmt->execute($emp);
        $sinHistorial = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM clientes WHERE activo = 1 AND strftime('%m-%d', fecha_nacimiento) = ?"
        );
        $stmt->execute([date('m-d')]);

        return $sinCerrar + $sinHistorial + (int) $stmt->fetchColumn();
    }

    private function citas(string $where, array $params, string $orden): array
    {
        $sql = "SELECT c.id, c.fecha_hora, c.fecha_fin, c.estado,
                       cl.id AS cliente_id, cl.nombre AS cliente, cl.telefono,
                       s.nombre AS servicio, e.nombre AS empleado, e.color
                  FROM citas c
                  JOIN clientes cl ON cl.id = c.cliente_id
                  JOIN servicios s ON s.id = c.servicio_id
                  JOIN empleados e ON e.id = c.empleado_id
                 WHERE {$where}";

        if ($this->empleadoId !== null) {
            $sql .= ' AND c.empleado_id = :emp';
            $params['emp'] = $this->empleadoId;
        }

        $stmt = $this->db->prepare($sql . " ORDER BY {$orden} LIMIT 30");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
