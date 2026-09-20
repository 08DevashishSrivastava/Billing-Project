<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;

/**
 * BudgetService: budget vs actual calculations.
 */
class BudgetService
{
    public function __construct(
        private Budget      $budgetModel = new Budget(),
        private Transaction $txModel     = new Transaction(),
    ) {}

    /**
     * Budget utilization summary for a month: includes over-budget alerts.
     */
    public function monthSummary(int $userId, string $month): array
    {
        $budgets  = $this->budgetModel->withSpendingForMonth($userId, $month);
        $alerts   = [];
        $overBudget  = 0;
        $totalBudget = 0.0;
        $totalSpent  = 0.0;

        foreach ($budgets as &$b) {
            $totalBudget += (float)$b['amount'];
            $totalSpent  += (float)$b['spent'];
            if ((float)$b['pct'] >= 100) {
                $overBudget++;
                $alerts[] = [
                    'type'     => 'over',
                    'category' => $b['category_name'],
                    'pct'      => $b['pct'],
                ];
            } elseif ((float)$b['pct'] >= 80) {
                $alerts[] = [
                    'type'     => 'near',
                    'category' => $b['category_name'],
                    'pct'      => $b['pct'],
                ];
            }
        }

        return [
            'budgets'      => $budgets,
            'alerts'       => $alerts,
            'over_budget'  => $overBudget,
            'total_budget' => $totalBudget,
            'total_spent'  => $totalSpent,
            'pct_overall'  => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0.0,
        ];
    }
}
