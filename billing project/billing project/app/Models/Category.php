<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';
    protected array $fillable = ['user_id','name','type','icon','color','is_default'];

    /**
     * Return all categories visible to a user: system defaults + their own.
     */
    public function forUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY is_default DESC, name ASC",
            [$userId]
        );
    }

    /**
     * Return categories by type ('income', 'expense', or 'both').
     */
    public function forUserByType(int $userId, string $type): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM categories WHERE (user_id IS NULL OR user_id = ?) AND (type = ? OR type = 'both') ORDER BY name ASC",
            [$userId, $type]
        );
    }
}
