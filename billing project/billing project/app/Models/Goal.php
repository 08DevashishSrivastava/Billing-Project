<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Goal extends Model
{
    protected string $table = 'goals';
    protected array $fillable = ['user_id','name','target_amount','current_amount','target_date','status','notes'];

    public function forUser(int $userId): array
    {
        return $this->whereUser($userId, 'created_at', 'DESC');
    }

    /**
     * Add funds toward a goal. Marks as 'achieved' if target is met.
     */
    public function deposit(int $goalId, int $userId, float $amount): bool
    {
        $goal = $this->findForUser($goalId, $userId);
        if (!$goal) return false;

        $newAmount = min((float)$goal['current_amount'] + $amount, (float)$goal['target_amount']);
        $status = $newAmount >= (float)$goal['target_amount'] ? 'achieved' : $goal['status'];

        $this->db->execute(
            "UPDATE goals SET current_amount = ?, status = ? WHERE id = ? AND user_id = ?",
            [$newAmount, $status, $goalId, $userId]
        );
        return true;
    }

    /**
     * Progress percentage for a goal.
     */
    public static function progressPct(array $goal): float
    {
        if ((float)$goal['target_amount'] <= 0) return 0.0;
        return min(100.0, round(((float)$goal['current_amount'] / (float)$goal['target_amount']) * 100, 1));
    }
}
