<?php
declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql);
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    public function whereFirst(string $column, mixed $value, string $operator = '='): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ? LIMIT 1";
        return $this->db->fetch($sql, [$value]);
    }

    public function create(array $data): int
    {
        $filtered = $this->filterFillable($data);
        return $this->db->insert($this->table, $filtered);
    }

    public function update(int $id, array $data): bool
    {
        $filtered = $this->filterFillable($data);
        $affected = $this->db->update($this->table, $filtered, "{$this->primaryKey} = ?", [$id]);
        return $affected > 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
        return $affected > 0;
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetch($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    public function sum(string $column, string $where = '1=1', array $params = []): float
    {
        $sql = "SELECT COALESCE(SUM({$column}), 0) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetch($sql, $params);
        return (float) ($result['total'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 10, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count();
        
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        $data = $this->db->fetchAll($sql, [$perPage, $offset]);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    public function search(string $column, string $term, int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} LIKE ? LIMIT ?";
        return $this->db->fetchAll($sql, ["%{$term}%", $limit]);
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    public function rawFirst(string $sql, array $params = []): ?array
    {
        return $this->db->fetch($sql, $params);
    }

    public function exists(string $column, mixed $value, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($exceptId !== null) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $exceptId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return (int) $result['count'] > 0;
    }

    // ── User-Scoping Helpers ──────────────────────────────────────────────────

    /**
     * Fetch all rows belonging to a specific user.
     */
    public function whereUser(int $userId, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Fetch first row matching a column, scoped to user.
     */
    public function whereUserFirst(int $userId, string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND {$column} = ? LIMIT 1";
        return $this->db->fetch($sql, [$userId, $value]);
    }

    /**
     * Find a row by PK, enforcing user ownership (prevents IDOR).
     */
    public function findForUser(int $id, int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND user_id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id, $userId]);
    }

    /**
     * SUM a column scoped to a user and optional WHERE clause.
     */
    public function sumUser(string $column, int $userId, string $extraWhere = '', array $extraParams = []): float
    {
        $where = "user_id = ?";
        $params = [$userId];
        if ($extraWhere !== '') {
            $where .= " AND {$extraWhere}";
            $params = array_merge($params, $extraParams);
        }
        $sql = "SELECT COALESCE(SUM({$column}), 0) as total FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetch($sql, $params);
        return (float) ($result['total'] ?? 0.0);
    }

    /**
     * COUNT rows scoped to a user and optional WHERE clause.
     */
    public function countUser(int $userId, string $extraWhere = '', array $extraParams = []): int
    {
        $where = "user_id = ?";
        $params = [$userId];
        if ($extraWhere !== '') {
            $where .= " AND {$extraWhere}";
            $params = array_merge($params, $extraParams);
        }
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->fetch($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Paginate rows scoped to a user.
     */
    public function paginateUser(int $userId, int $page = 1, int $perPage = 15, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->countUser($userId);
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        $data = $this->db->fetchAll($sql, [$userId, $perPage, $offset]);
        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
