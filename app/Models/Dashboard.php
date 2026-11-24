<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Consultas de resumen para el dashboard. Si se pasa un empleado,
 * los datos se limitan a sus citas (vista del barbero).
 */
final class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function citasDelDia(string $fecha, ?int $empleadoId = null): array
    {
        $sql = 'SELECT c.id, c.fecha_hora, c.fecha_fin, c.estado,
                       cl.nombre AS cliente, s.nombre AS servicio,
                       e.nombre AS empleado, e.color
                  FROM citas c
                  JOIN clientes  cl ON cl.id = c.cliente_id
                  JOIN servicios s  ON s.id  = c.servicio_id
                  JOIN empleados e  ON e.id  = c.empleado_id
                 WHERE date(c.fecha_hora) = :fecha';
        $params = ['fecha' => $fecha];

        if ($empleadoId !== null) {
            $sql .= ' AND c.empleado_id = :emp';
            $params['emp'] = $empleadoId;
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY c.fecha_hora');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Ingresos de citas atendidas en el mes de la fecha dada. */
    public function ingresosDelMes(string $fecha, ?int $empleadoId = null): float
    {
        $sql = "SELECT COALESCE(SUM(precio), 0) FROM citas
                 WHERE estado = 'atendida'
                   AND strftime('%Y-%m', fecha_hora) = strftime('%Y-%m', :fecha)";
        $params = ['fecha' => $fecha];

        if ($empleadoId !== null) {
            $sql .= ' AND empleado_id = :emp';
            $params['emp'] = $empleadoId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    /** Servicios mas solicitados (citas no canceladas) en los ultimos 90 dias. */
    public function serviciosTop(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.nombre, s.tipo, COUNT(*) AS total
               FROM citas c
               JOIN servicios s ON s.id = c.servicio_id
              WHERE c.estado <> 'cancelada'
                AND date(c.fecha_hora) >= date(:hoy, '-90 days')
           GROUP BY s.id
           ORDER BY total DESC, s.nombre
              LIMIT :lim"
        );
        $stmt->bindValue('hoy', date('Y-m-d'));
        $stmt->bindValue('lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function totalClientesActivos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM clientes WHERE activo = 1')->fetchColumn();
    }
}
