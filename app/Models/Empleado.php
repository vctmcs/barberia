<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Empleado extends Model
{
    public const ESPECIALIDADES = [
        'barbero'   => 'Barbero',
        'estilista' => 'Estilista',
        'colorista' => 'Colorista',
    ];

    public const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    protected string $table = 'empleados';
    protected array $fillable = ['usuario_id', 'nombre', 'telefono', 'email', 'especialidad', 'color', 'activo'];

    public function search(string $q = '', string $estado = 'activos'): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if ($q !== '') {
            $where[] = '(e.nombre LIKE :q OR e.telefono LIKE :q OR e.email LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if ($estado === 'activos') {
            $where[] = 'e.activo = 1';
        } elseif ($estado === 'inactivos') {
            $where[] = 'e.activo = 0';
        }

        $stmt = $this->db->prepare(
            'SELECT e.*, u.email AS usuario_email, u.activo AS usuario_activo,
                    (SELECT COUNT(*) FROM horarios h WHERE h.empleado_id = e.id) AS dias,
                    (SELECT COUNT(*) FROM citas c
                      WHERE c.empleado_id = e.id AND c.estado = \'programada\'
                        AND c.fecha_hora >= :ahora) AS proximas
               FROM empleados e
          LEFT JOIN usuarios u ON u.id = e.usuario_id
              WHERE ' . implode(' AND ', $where) . '
           ORDER BY e.activo DESC, e.nombre'
        );
        $stmt->execute($params + ['ahora' => self::now()]);
        return $stmt->fetchAll();
    }

    public function findFull(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, u.email AS usuario_email, u.activo AS usuario_activo
               FROM empleados e
          LEFT JOIN usuarios u ON u.id = e.usuario_id
              WHERE e.id = ?'
        );
        $stmt->execute([$id]);
        $empleado = $stmt->fetch();

        if (!$empleado) {
            return null;
        }

        $empleado['horario'] = $this->horario($id);
        return $empleado;
    }

    /** Horario indexado por dia: [1 => ['hora_inicio' => '10:00', ...], ...] */
    public function horario(int $empleadoId): array
    {
        $stmt = $this->db->prepare('SELECT dia_semana, hora_inicio, hora_fin FROM horarios WHERE empleado_id = ?');
        $stmt->execute([$empleadoId]);

        $horario = [];
        foreach ($stmt->fetchAll() as $h) {
            $horario[(int) $h['dia_semana']] = $h;
        }
        return $horario;
    }

    /** Reemplaza el horario completo del empleado. */
    public function guardarHorario(int $empleadoId, array $dias): void
    {
        $this->db->prepare('DELETE FROM horarios WHERE empleado_id = ?')->execute([$empleadoId]);

        $stmt = $this->db->prepare(
            'INSERT INTO horarios (empleado_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)'
        );
        foreach ($dias as $dia => $h) {
            $stmt->execute([$empleadoId, $dia, $h['inicio'], $h['fin']]);
        }
    }

    /** Empleados activos para selectores. */
    public function activos(): array
    {
        return $this->db->query(
            'SELECT id, nombre, especialidad, color FROM empleados WHERE activo = 1 ORDER BY nombre'
        )->fetchAll();
    }

    public function enUso(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM citas WHERE empleado_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function emailUsado(string $email, ?int $exceptoUsuario = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ? AND id IS NOT ?');
        $stmt->execute([mb_strtolower($email), $exceptoUsuario]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function transaction(callable $fn): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $fn();
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
