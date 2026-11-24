<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected array $fillable = ['nombre', 'email', 'password_hash', 'rol', 'activo'];

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([mb_strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    /** Usuario con el id de empleado ligado (si es barbero). */
    public function findWithEmpleado(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.nombre, u.email, u.rol, u.activo, e.id AS empleado_id, e.color
               FROM usuarios u
          LEFT JOIN empleados e ON e.usuario_id = u.id
              WHERE u.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updatePassword(int $id, string $password): void
    {
        $this->db->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function touchAcceso(int $id): void
    {
        $this->db->prepare('UPDATE usuarios SET ultimo_acceso = ? WHERE id = ?')
            ->execute([self::now(), $id]);
    }
}
