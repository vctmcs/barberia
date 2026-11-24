<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Historial de servicios de cada cliente: lo que se le hizo, la formula
 * de color o productos usados y las fotos de antes y despues.
 */
final class Historial extends Model
{
    /** Tipos de servicio donde la formula es lo mas importante. */
    public const TIPOS_CON_FORMULA = ['tinte', 'tratamiento', 'alaciado'];

    protected string $table = 'historial';
    protected array $fillable = [
        'cliente_id', 'cita_id', 'servicio_id', 'empleado_id',
        'formula_tinte', 'observaciones', 'foto_antes', 'foto_despues', 'fecha',
    ];

    public function findFull(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, cl.nombre AS cliente, s.nombre AS servicio, s.tipo AS servicio_tipo,
                    e.nombre AS empleado, e.color
               FROM historial h
               JOIN clientes cl ON cl.id = h.cliente_id
               JOIN servicios s ON s.id = h.servicio_id
          LEFT JOIN empleados e ON e.id = h.empleado_id
              WHERE h.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function porCita(int $citaId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM historial WHERE cita_id = ?');
        $stmt->execute([$citaId]);
        return $stmt->fetch() ?: null;
    }

    /** Historial completo del cliente, del mas reciente al mas antiguo. */
    public function porCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, s.nombre AS servicio, s.tipo AS servicio_tipo,
                    e.nombre AS empleado, e.color
               FROM historial h
               JOIN servicios s ON s.id = h.servicio_id
          LEFT JOIN empleados e ON e.id = h.empleado_id
              WHERE h.cliente_id = ?
           ORDER BY h.fecha DESC, h.id DESC'
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    /** La formula de color mas reciente del cliente. */
    public function ultimaFormula(int $clienteId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT h.formula_tinte, h.fecha, s.nombre AS servicio, e.nombre AS empleado
               FROM historial h
               JOIN servicios s ON s.id = h.servicio_id
          LEFT JOIN empleados e ON e.id = h.empleado_id
              WHERE h.cliente_id = ? AND h.formula_tinte IS NOT NULL AND h.formula_tinte <> ''
                AND s.tipo = 'tinte'
           ORDER BY h.fecha DESC, h.id DESC
              LIMIT 1"
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetch() ?: null;
    }

    /** Citas atendidas que aun no tienen historial (para recordarlo). */
    public function atendidasSinHistorial(?int $empleadoId, int $limite = 5): array
    {
        $sql = "SELECT c.id, c.fecha_hora, cl.nombre AS cliente, s.nombre AS servicio
                  FROM citas c
                  JOIN clientes cl ON cl.id = c.cliente_id
                  JOIN servicios s ON s.id = c.servicio_id
             LEFT JOIN historial h ON h.cita_id = c.id
                 WHERE c.estado = 'atendida' AND h.id IS NULL";
        $params = [];
        if ($empleadoId !== null) {
            $sql .= ' AND c.empleado_id = ?';
            $params[] = $empleadoId;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY c.fecha_hora DESC LIMIT ' . (int) $limite);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
