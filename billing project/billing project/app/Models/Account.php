<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Account extends Model
{
    protected string $table = 'accounts';
    protected array $fillable = ['user_id','name','type','balance','institution','color','is_active'];

    public function forUser(int $userId): array
    {
        return $this->whereUser($userId, 'name', 'ASC');
    }

    /**
     * Adjust account balance by a signed delta (positive = credit, negative = debit).
     */
    public function adjustBalance(int $accountId, float $delta): void
    {
        $this->db->execute(
            "UPDATE accounts SET balance = balance + ? WHERE id = ?",
            [$delta, $accountId]
        );
    }

    /**
     * Recalculate balance from scratch based on all transactions.
     * Use after CSV import or bulk delete.
     */
    public function recalculateBalance(int $accountId): void
    {
        $result = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS net
             FROM transactions WHERE account_id = ?",
            [$accountId]
        );
        $this->db->execute(
            "UPDATE accounts SET balance = ? WHERE id = ?",
            [(float)($result['net'] ?? 0), $accountId]
        );
    }

    public function totalNetWorthForUser(int $userId): float
    {
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM(balance), 0) AS net FROM accounts WHERE user_id = ? AND is_active = 1",
            [$userId]
        );
        return (float)($result['net'] ?? 0);
    }
}
