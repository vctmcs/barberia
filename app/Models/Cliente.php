<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Cliente extends Model
{
    public const POR_PAGINA = 12;

    protected string $table = 'clientes';
    protected array $fillable = [
        'nombre', 'telefono', 'email', 'fecha_nacimiento',
        'preferencias', 'notas', 'foto', 'activo',
    ];

    /**
     * Listado paginado con ultima visita y proxima cita.
     *
     * @return array{items:array, total:int, paginas:int, pagina:int}
     */
    public function paginate(string $q = '', string $estado = 'activos', int $pagina = 1): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if ($q !== '') {
            $where[] = '(c.nombre LIKE :q OR c.telefono LIKE :q OR c.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($estado === 'activos') {
            $where[] = 'c.activo = 1';
        } elseif ($estado === 'inactivos') {
            $where[] = 'c.activo = 0';
        }
        $whereSql = implode(' AND ', $where);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes c WHERE {$whereSql}");
        $stmt->execute($params);
        $total   = (int) $stmt->fetchColumn();
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina  = min(max(1, $pagina), $paginas);

        $stmt = $this->db->prepare(
            "SELECT c.*,
                    (SELECT MAX(ci.fecha_hora) FROM citas ci
                      WHERE ci.cliente_id = c.id AND ci.estado = 'atendida') AS ultima_visita,
                    (SELECT MIN(ci.fecha_hora) FROM citas ci
                      WHERE ci.cliente_id = c.id AND ci.estado = 'programada'
                        AND ci.fecha_hora >= :ahora) AS proxima_cita
               FROM clientes c
              WHERE {$whereSql}
           ORDER BY c.activo DESC, c.nombre
              LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('ahora', self::now());
        $stmt->bindValue('lim', self::POR_PAGINA, \PDO::PARAM_INT);
        $stmt->bindValue('off', ($pagina - 1) * self::POR_PAGINA, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'   => $stmt->fetchAll(),
            'total'   => $total,
            'paginas' => $paginas,
            'pagina'  => $pagina,
        ];
    }

    public function citas(int $clienteId, int $limite = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT ci.id, ci.fecha_hora, ci.estado, ci.precio,
                    s.nombre AS servicio, e.nombre AS empleado, e.color
               FROM citas ci
               JOIN servicios s ON s.id = ci.servicio_id
               JOIN empleados e ON e.id = ci.empleado_id
              WHERE ci.cliente_id = ?
           ORDER BY ci.fecha_hora DESC
              LIMIT ?'
        );
        $stmt->bindValue(1, $clienteId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Resumen: visitas atendidas, gasto total, primera y ultima visita. */
    public function resumen(int $clienteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS visitas,
                    COALESCE(SUM(precio), 0) AS gastado,
                    MAX(fecha_hora) AS ultima
               FROM citas
              WHERE cliente_id = ? AND estado = 'atendida'"
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetch();
    }

    /** Activos para selectores (busqueda en el formulario de citas). */
    public function activos(): array
    {
        return $this->db->query(
            'SELECT id, nombre, telefono FROM clientes WHERE activo = 1 ORDER BY nombre'
        )->fetchAll();
    }

    public function tieneCitas(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM citas WHERE cliente_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
