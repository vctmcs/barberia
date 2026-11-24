<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Modelo base con operaciones comunes. Cada modelo define $table y
 * $fillable (columnas que se permiten escribir desde formularios).
 */
abstract class Model
{
    protected string $table = '';
    protected array $fillable = [];

    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $data = $this->only($data);
        $cols = array_keys($data);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $cols),
            implode(', ', array_map(fn ($c) => ':' . $c, $cols))
        );

        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data = $this->only($data);
        if (!$data) {
            return;
        }

        $sets = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($data)));
        $data['id'] = $id;

        $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE id = :id")->execute($data);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?")->execute([$id]);
    }

    public function count(string $where = '1', array $params = []): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Filtra un arreglo para dejar solo columnas permitidas. */
    protected function only(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
