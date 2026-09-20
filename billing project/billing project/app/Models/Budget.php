<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Budget extends Model
{
    protected string $table = 'budgets';
    protected array $fillable = ['user_id','category_id','amount','period_month'];

    /**
     * Budgets for a month with actual spending joined.
     */
    public function withSpendingForMonth(int $userId, string $month): array
    {
        return $this->db->fetchAll(
            "SELECT b.*, c.name AS category_name, c.icon, c.color,
                    COALESCE(t.spent, 0) AS spent,
                    b.amount - COALESCE(t.spent, 0) AS remaining,
                    CASE WHEN b.amount > 0 THEN ROUND((COALESCE(t.spent,0) / b.amount) * 100, 1) ELSE 0 END AS pct
             FROM budgets b
             JOIN categories c ON b.category_id = c.id
             LEFT JOIN (
                 SELECT category_id, SUM(amount) AS spent
                 FROM transactions
                 WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?
                 GROUP BY category_id
             ) t ON t.category_id = b.category_id
             WHERE b.user_id = ? AND b.period_month = ?
             ORDER BY pct DESC",
            [$userId, $month, $userId, $month]
        );
    }

    /**
     * Upsert: update existing budget or create new one.
     */
    public function upsert(int $userId, int $categoryId, float $amount, string $month): void
    {
        $existing = $this->rawFirst(
            "SELECT id FROM budgets WHERE user_id = ? AND category_id = ? AND period_month = ?",
            [$userId, $categoryId, $month]
        );
        if ($existing) {
            $this->update((int)$existing['id'], ['amount' => $amount]);
        } else {
            $this->create(['user_id' => $userId, 'category_id' => $categoryId, 'amount' => $amount, 'period_month' => $month]);
        }
    }
}
