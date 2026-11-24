<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Recorridos guiados: registra que usuario ya vio (o salto) el
 * recorrido de cada seccion para no mostrarlo automaticamente otra vez.
 */
final class Recorrido extends Model
{
    /** Secciones con recorrido: CRUDs y pantallas donde se capturan o consultan datos. */
    public const CLAVES = [
        'dashboard', 'notificaciones', 'agenda', 'cita-form', 'cita-detalle', 'clientes', 'cliente-form', 'cliente-ficha', 'historial-form',
        'servicios', 'servicio-form', 'empleados', 'empleado-form', 'cuenta',
    ];

    protected string $table = 'recorridos_vistos';

    public function visto(int $usuarioId, string $clave): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM recorridos_vistos WHERE usuario_id = ? AND clave = ?');
        $stmt->execute([$usuarioId, $clave]);
        return (bool) $stmt->fetchColumn();
    }

    public function marcar(int $usuarioId, string $clave): void
    {
        $this->db->prepare(
            'INSERT INTO recorridos_vistos (usuario_id, clave, visto_en) VALUES (?, ?, ?)
             ON CONFLICT (usuario_id, clave) DO UPDATE SET visto_en = excluded.visto_en'
        )->execute([$usuarioId, $clave, self::now()]);
    }
}
