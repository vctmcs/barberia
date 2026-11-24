<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Servicio extends Model
{
    public const TIPOS = [
        'corte'       => 'Cortes',
        'tinte'       => 'Tintes y efectos de color',
        'tratamiento' => 'Tratamientos especializados',
        'alaciado'    => 'Alaciado japonés / express',
        'split_ender' => 'Split ender',
        'otro'        => 'Otro',
    ];

    /** Nombre corto para etiquetas en tablas. */
    public const TIPOS_CORTOS = [
        'corte'       => 'Corte',
        'tinte'       => 'Color',
        'tratamiento' => 'Tratamiento',
        'alaciado'    => 'Alaciado',
        'split_ender' => 'Split ender',
        'otro'        => 'Otro',
    ];

    protected string $table = 'servicios';
    protected array $fillable = ['nombre', 'tipo', 'descripcion', 'precio', 'duracion_min', 'activo'];

    /** Listado con filtros de texto, tipo y estado. */
    public function search(string $q = '', string $tipo = '', string $estado = 'activos'): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if ($q !== '') {
            $where[] = '(s.nombre LIKE :q OR s.descripcion LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        if (isset(self::TIPOS[$tipo])) {
            $where[] = 's.tipo = :tipo';
            $params['tipo'] = $tipo;
        }
        if ($estado === 'activos') {
            $where[] = 's.activo = 1';
        } elseif ($estado === 'inactivos') {
            $where[] = 's.activo = 0';
        }

        $stmt = $this->db->prepare(
            'SELECT s.*, (SELECT COUNT(*) FROM citas c WHERE c.servicio_id = s.id) AS usos
               FROM servicios s
              WHERE ' . implode(' AND ', $where) . '
           ORDER BY s.activo DESC, s.tipo, s.nombre'
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Servicios activos para los selectores de citas e historial. */
    public function activos(): array
    {
        return $this->db->query(
            'SELECT id, nombre, tipo, descripcion, precio, duracion_min FROM servicios WHERE activo = 1 ORDER BY precio'
        )->fetchAll();
    }

    /** Activos agrupados por tipo, en el orden de TIPOS (pagina publica). */
    public function catalogo(): array
    {
        $grupos = [];
        foreach ($this->activos() as $s) {
            $grupos[$s['tipo']][] = $s;
        }
        return array_filter(array_replace(array_fill_keys(array_keys(self::TIPOS), []), $grupos));
    }

    public function enUso(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT (SELECT COUNT(*) FROM citas WHERE servicio_id = :id)
                  + (SELECT COUNT(*) FROM historial WHERE servicio_id = :id)'
        );
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
