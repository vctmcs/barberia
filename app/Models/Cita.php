<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Cita extends Model
{
    public const ESTADOS = [
        'programada' => 'Programada',
        'atendida'   => 'Atendida',
        'cancelada'  => 'Cancelada',
        'no_asistio' => 'No asistió',
    ];

    /** Estados que ocupan el horario del estilista. */
    public const OCUPAN = ['programada', 'atendida'];

    protected string $table = 'citas';
    protected array $fillable = [
        'cliente_id', 'empleado_id', 'servicio_id', 'fecha_hora', 'fecha_fin',
        'estado', 'precio', 'notas', 'actualizado_en',
    ];

    /** Cita con nombres de cliente, servicio y empleado. */
    public function findFull(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    cl.nombre AS cliente, cl.telefono AS cliente_telefono, cl.email AS cliente_email,
                    cl.foto AS cliente_foto, cl.preferencias AS cliente_preferencias, cl.notas AS cliente_notas,
                    s.nombre AS servicio, s.tipo AS servicio_tipo, s.duracion_min,
                    e.nombre AS empleado, e.color
               FROM citas c
               JOIN clientes  cl ON cl.id = c.cliente_id
               JOIN servicios s  ON s.id  = c.servicio_id
               JOIN empleados e  ON e.id  = c.empleado_id
              WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Citas en un rango para el calendario. */
    public function enRango(string $desde, string $hasta, ?int $empleadoId, bool $conCanceladas): array
    {
        $sql = 'SELECT c.id, c.fecha_hora, c.fecha_fin, c.estado, c.empleado_id,
                       cl.nombre AS cliente, s.nombre AS servicio, e.nombre AS empleado, e.color
                  FROM citas c
                  JOIN clientes  cl ON cl.id = c.cliente_id
                  JOIN servicios s  ON s.id  = c.servicio_id
                  JOIN empleados e  ON e.id  = c.empleado_id
                 WHERE c.fecha_hora < :hasta AND c.fecha_fin > :desde';
        $params = ['desde' => $desde, 'hasta' => $hasta];

        if ($empleadoId !== null) {
            $sql .= ' AND c.empleado_id = :emp';
            $params['emp'] = $empleadoId;
        }
        if (!$conCanceladas) {
            $sql .= " AND c.estado <> 'cancelada'";
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY c.fecha_hora');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Citas que se empalman con el intervalo dado.
     * Dos intervalos se cruzan cuando uno empieza antes de que el otro termine.
     */
    public function empalmes(string $columna, int $id, string $inicio, string $fin, ?int $excepto = null): array
    {
        if (!in_array($columna, ['empleado_id', 'cliente_id'], true)) {
            throw new \InvalidArgumentException('Columna no permitida');
        }

        $stmt = $this->db->prepare(
            "SELECT c.id, c.fecha_hora, c.fecha_fin, cl.nombre AS cliente, e.nombre AS empleado
               FROM citas c
               JOIN clientes cl ON cl.id = c.cliente_id
               JOIN empleados e ON e.id = c.empleado_id
              WHERE c.{$columna} = :id
                AND c.estado IN ('programada', 'atendida')
                AND c.fecha_hora < :fin
                AND c.fecha_fin  > :inicio
                AND c.id IS NOT :excepto
           ORDER BY c.fecha_hora"
        );
        $stmt->execute(['id' => $id, 'inicio' => $inicio, 'fin' => $fin, 'excepto' => $excepto]);
        return $stmt->fetchAll();
    }

    /** Intervalos ocupados de un empleado en un dia (para calcular huecos). */
    public function ocupadosDelDia(int $empleadoId, string $fecha, ?int $excepto = null): array
    {
        $stmt = $this->db->prepare(
            "SELECT fecha_hora, fecha_fin FROM citas
              WHERE empleado_id = :emp
                AND estado IN ('programada', 'atendida')
                AND date(fecha_hora) = :fecha
                AND id IS NOT :excepto
           ORDER BY fecha_hora"
        );
        $stmt->execute(['emp' => $empleadoId, 'fecha' => $fecha, 'excepto' => $excepto]);
        return $stmt->fetchAll();
    }

    /** Programadas que ya pasaron y siguen sin marcarse. */
    public function pendientesDeCerrar(?int $empleadoId): int
    {
        $sql = "SELECT COUNT(*) FROM citas WHERE estado = 'programada' AND fecha_fin < :ahora";
        $params = ['ahora' => self::now()];
        if ($empleadoId !== null) {
            $sql .= ' AND empleado_id = :emp';
            $params['emp'] = $empleadoId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function recordatorios(int $citaId): array
    {
        $stmt = $this->db->prepare('SELECT canal, enviado_en, estado, detalle FROM recordatorios WHERE cita_id = ? ORDER BY canal');
        $stmt->execute([$citaId]);
        return $stmt->fetchAll();
    }

    public function registrarRecordatorio(int $citaId, string $canal, string $estado, ?string $detalle = null): void
    {
        $this->db->prepare(
            'INSERT INTO recordatorios (cita_id, canal, enviado_en, estado, detalle) VALUES (?, ?, ?, ?, ?)
             ON CONFLICT (cita_id, canal) DO UPDATE SET
                enviado_en = excluded.enviado_en, estado = excluded.estado, detalle = excluded.detalle'
        )->execute([$citaId, $canal, self::now(), $estado, $detalle]);
    }

    /** Si la cita cambia de fecha, sus recordatorios deben volver a enviarse. */
    public function reiniciarRecordatorios(int $citaId): void
    {
        $this->db->prepare('DELETE FROM recordatorios WHERE cita_id = ?')->execute([$citaId]);
    }
}
