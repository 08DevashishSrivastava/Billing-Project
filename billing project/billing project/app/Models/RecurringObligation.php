<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class RecurringObligation extends Model
{
    protected string $table = 'recurring_obligations';
    protected array $fillable = [
        'user_id','category_id','merchant','expected_amount','frequency',
        'is_subscription','last_payment_date','next_due_date','status','confidence_score'
    ];

    public function forUser(int $userId): array
    {
        return $this->activeForUser($userId);
    }

    public function activeForUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, c.name AS category_name, c.icon
             FROM recurring_obligations r
             LEFT JOIN categories c ON r.category_id = c.id
             WHERE r.user_id = ? AND r.status = 'active'
             ORDER BY r.next_due_date ASC",
            [$userId]
        );
    }

    public function upcomingForUser(int $userId, int $days = 30): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, c.name AS category_name, c.icon
             FROM recurring_obligations r
             LEFT JOIN categories c ON r.category_id = c.id
             WHERE r.user_id = ? AND r.status = 'active'
               AND r.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY r.next_due_date ASC",
            [$userId, $days]
        );
    }

    /**
     * Projected monthly cost of all active obligations.
     */
    public function projectedMonthlyCost(int $userId): float
    {
        $rows = $this->activeForUser($userId);
        $total = 0.0;
        foreach ($rows as $r) {
            $total += match ($r['frequency']) {
                'weekly'  => (float)$r['expected_amount'] * 4.33,
                'monthly' => (float)$r['expected_amount'],
                'yearly'  => (float)$r['expected_amount'] / 12,
                default   => (float)$r['expected_amount'],
            };
        }
        return round($total, 2);
    }
}
