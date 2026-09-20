<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\RecurringObligation;

/**
 * AgentService: Deterministic AI decision-support engine.
 *
 * All answers are grounded in the user's own financial data.
 * This service NEVER gives investment advice, tax advice, or loan approval recommendations.
 * Every response includes an explicit non-advisory disclaimer.
 */
class AgentService
{
    private const DISCLAIMER = "\n\n---\n*⚠️ FinPilot provides financial insights based on your recorded data only. "
        . "This is NOT financial advice. Please consult a qualified financial advisor before making significant financial decisions.*";

    private const REJECTION_TOPICS = [
        'stock', 'share', 'crypto', 'bitcoin', 'nifty', 'sensex',
        'invest in', 'buy this', 'sell this', 'tax return', 'loan approval',
        'should i buy', 'which fund', 'pick for me',
    ];

    public function __construct(
        private Transaction        $txModel        = new Transaction(),
        private Budget             $budgetModel    = new Budget(),
        private Goal               $goalModel      = new Goal(),
        private RecurringObligation $recurringModel = new RecurringObligation(),
        private AnalysisService    $analysis       = new AnalysisService(),
        private BudgetService      $budgetSvc      = new BudgetService(),
    ) {}

    /**
     * Process a natural-language query, return a grounded structured response.
     */
    public function processQuery(int $userId, string $query): array
    {
        $queryLower = strtolower(trim($query));

        // ── Safety: reject out-of-scope advisory requests ─────────────────────
        foreach (self::REJECTION_TOPICS as $topic) {
            if (str_contains($queryLower, $topic)) {
                return [
                    'intent'  => 'OUT_OF_SCOPE',
                    'answer'  => "I can only answer questions about your personal spending, budgets, goals, and recurring bills based on your recorded data. "
                               . "Questions about stock picking, crypto, specific investments, taxes, or loan approvals are outside FinPilot's scope." . self::DISCLAIMER,
                    'data'    => [],
                    'suggestions' => [
                        'Where did I spend the most this month?',
                        'How are my budgets doing?',
                        'What are my upcoming bills?',
                        'How far am I from my savings goals?',
                    ],
                ];
            }
        }

        $month = date('Y-m');

        // ── Intent classification ─────────────────────────────────────────────
        $intent = $this->classifyIntent($queryLower);

        return match ($intent) {
            'MONTHLY_SUMMARY'       => $this->toolMonthlySummary($userId, $month),
            'CATEGORY_SPENDING'     => $this->toolCategorySpending($userId, $month),
            'BUDGET_STATUS'         => $this->toolBudgetStatus($userId, $month),
            'RECURRING_OBLIGATIONS' => $this->toolRecurring($userId),
            'GOAL_PROGRESS'         => $this->toolGoalProgress($userId),
            'UNUSUAL_SPENDING'      => $this->toolUnusualSpending($userId, $month),
            default                 => $this->toolGeneral($userId, $month, $query),
        };
    }

    // ── Tool: Monthly Summary ─────────────────────────────────────────────────
    private function toolMonthlySummary(int $userId, string $month): array
    {
        $totals = $this->txModel->monthlyTotals($userId, $month);
        $rate   = $this->analysis->savingsRate($totals['income'], $totals['expense']);
        $formatted = date('F Y', strtotime($month . '-01'));

        $answer  = "**{$formatted} Summary:**\n\n";
        $answer .= "- 💰 **Income:** " . number_format($totals['income'], 2) . "\n";
        $answer .= "- 💸 **Expenses:** " . number_format($totals['expense'], 2) . "\n";
        $answer .= "- 📈 **Net Savings:** " . number_format($totals['net'], 2) . "\n";
        $answer .= "- 🎯 **Savings Rate:** {$rate}%\n";

        if ($rate >= 20) {
            $answer .= "\n✅ Great job! A savings rate above 20% is generally considered healthy.";
        } elseif ($rate > 0) {
            $answer .= "\n💡 Your savings rate is positive. Building an emergency fund could be a good next step.";
        } else {
            $answer .= "\n⚠️ Your expenses exceeded your income this month. Reviewing your budget categories may help identify areas to reduce.";
        }

        return ['intent' => 'MONTHLY_SUMMARY', 'answer' => $answer . self::DISCLAIMER, 'data' => $totals, 'suggestions' => ['Show my budget status', 'Where did I overspend?', 'Show my goals']];
    }

    // ── Tool: Category Spending ───────────────────────────────────────────────
    private function toolCategorySpending(int $userId, string $month): array
    {
        $breakdown = $this->txModel->categoryBreakdown($userId, $month);
        $formatted = date('F Y', strtotime($month . '-01'));

        if (empty($breakdown)) {
            return ['intent' => 'CATEGORY_SPENDING', 'answer' => "No expense transactions found for {$formatted}." . self::DISCLAIMER, 'data' => [], 'suggestions' => ['Add a transaction', 'Show my dashboard']];
        }

        $top    = $breakdown[0];
        $total  = array_sum(array_column($breakdown, 'total'));
        $pct    = $total > 0 ? round(((float)$top['total'] / $total) * 100, 1) : 0;

        $answer  = "**Your top spending categories in {$formatted}:**\n\n";
        foreach (array_slice($breakdown, 0, 5) as $i => $row) {
            $catPct   = $total > 0 ? round(((float)$row['total'] / $total) * 100, 1) : 0;
            $answer  .= ($i + 1) . ". **{$row['category']}**: " . number_format((float)$row['total'], 2) . " ({$catPct}%)\n";
        }
        $answer .= "\n🏆 You spent the most on **{$top['category']}** — " . number_format((float)$top['total'], 2) . " ({$pct}% of total expenses).";

        return ['intent' => 'CATEGORY_SPENDING', 'answer' => $answer . self::DISCLAIMER, 'data' => $breakdown, 'suggestions' => ['Show my budget status', 'Any unusual spending this month?']];
    }

    // ── Tool: Budget Status ───────────────────────────────────────────────────
    private function toolBudgetStatus(int $userId, string $month): array
    {
        $summary   = $this->budgetSvc->monthSummary($userId, $month);
        $formatted = date('F Y', strtotime($month . '-01'));

        $answer = "**Budget Status for {$formatted}:**\n\n";
        if (empty($summary['budgets'])) {
            return ['intent' => 'BUDGET_STATUS', 'answer' => "You haven't set any budgets for {$formatted}. Go to the Budgets section to set monthly spending limits." . self::DISCLAIMER, 'data' => [], 'suggestions' => ['Set a budget', 'Show spending categories']];
        }

        foreach ($summary['budgets'] as $b) {
            $icon  = (float)$b['pct'] >= 100 ? '🔴' : ((float)$b['pct'] >= 80 ? '🟡' : '🟢');
            $answer .= "{$icon} **{$b['category_name']}**: " . number_format((float)$b['spent'], 2) . " / " . number_format((float)$b['amount'], 2) . " ({$b['pct']}%)\n";
        }

        if ($summary['over_budget'] > 0) {
            $answer .= "\n⚠️ You have **{$summary['over_budget']} over-budget** categories this month. Consider reviewing those expenses.";
        } else {
            $answer .= "\n✅ All budgets are within limits this month.";
        }

        return ['intent' => 'BUDGET_STATUS', 'answer' => $answer . self::DISCLAIMER, 'data' => $summary, 'suggestions' => ['Where did I spend the most?', 'Show my goals']];
    }

    // ── Tool: Recurring Obligations ───────────────────────────────────────────
    private function toolRecurring(int $userId): array
    {
        $upcoming  = $this->recurringModel->upcomingForUser($userId, 30);
        $projected = $this->recurringModel->projectedMonthlyCost($userId);

        $answer  = "**Upcoming Bills & Subscriptions (next 30 days):**\n\n";
        if (empty($upcoming)) {
            $answer .= "No upcoming obligations in the next 30 days.";
        } else {
            foreach ($upcoming as $r) {
                $daysLeft = max(0, (int) ceil((strtotime($r['next_due_date']) - time()) / 86400));
                $answer  .= "- 📅 **{$r['merchant']}**: " . number_format((float)$r['expected_amount'], 2) . " due in {$daysLeft} day(s)\n";
            }
        }
        $answer .= "\n💳 **Projected monthly commitment:** " . number_format($projected, 2);

        return ['intent' => 'RECURRING_OBLIGATIONS', 'answer' => $answer . self::DISCLAIMER, 'data' => ['upcoming' => $upcoming, 'projected_monthly' => $projected], 'suggestions' => ['Show my budget status', 'Show my monthly summary']];
    }

    // ── Tool: Goal Progress ───────────────────────────────────────────────────
    private function toolGoalProgress(int $userId): array
    {
        $goals  = $this->goalModel->forUser($userId);
        $active = array_filter($goals, fn($g) => $g['status'] === 'active');

        $answer = "**Your Savings Goals:**\n\n";
        if (empty($active)) {
            return ['intent' => 'GOAL_PROGRESS', 'answer' => "You don't have any active savings goals. Head to Goals to create one!" . self::DISCLAIMER, 'data' => [], 'suggestions' => ['Show my budget status', 'Show my monthly summary']];
        }

        foreach ($active as $g) {
            $pct     = Goal::progressPct($g);
            $remaining = (float)$g['target_amount'] - (float)$g['current_amount'];
            $bar     = str_repeat('█', (int)($pct / 10)) . str_repeat('░', 10 - (int)($pct / 10));
            $answer .= "🎯 **{$g['name']}**: {$bar} {$pct}%\n";
            $answer .= "   " . number_format((float)$g['current_amount'], 2) . " / " . number_format((float)$g['target_amount'], 2) . " (need " . number_format($remaining, 2) . " more)\n\n";
        }

        return ['intent' => 'GOAL_PROGRESS', 'answer' => $answer . self::DISCLAIMER, 'data' => $goals, 'suggestions' => ['Show monthly summary', 'How are my budgets?']];
    }

    // ── Tool: Unusual Spending ────────────────────────────────────────────────
    private function toolUnusualSpending(int $userId, string $month): array
    {
        $anomalies = $this->analysis->detectUnusualSpending($userId, $month);
        $formatted = date('F Y', strtotime($month . '-01'));

        if (empty($anomalies)) {
            return ['intent' => 'UNUSUAL_SPENDING', 'answer' => "No unusual spending patterns detected in {$formatted} compared to your 3-month average. Your spending looks consistent." . self::DISCLAIMER, 'data' => [], 'suggestions' => ['Show spending categories', 'Show my budget status']];
        }

        $answer = "**Potentially Unusual Spending in {$formatted}:**\n\n";
        foreach ($anomalies as $a) {
            $answer .= "⚠️ **{$a['category']}**: " . number_format($a['current'], 2) . " this month vs avg " . number_format($a['avg_3m'], 2) . " (×{$a['ratio']})\n";
        }
        $answer .= "\n💡 These categories appear higher than your recent 3-month average. This may be due to seasonal expenses or one-time purchases.";

        return ['intent' => 'UNUSUAL_SPENDING', 'answer' => $answer . self::DISCLAIMER, 'data' => $anomalies, 'suggestions' => ['Show my budgets', 'Show spending categories']];
    }

    // ── Fallback: General ─────────────────────────────────────────────────────
    private function toolGeneral(int $userId, string $month, string $originalQuery): array
    {
        $totals    = $this->txModel->monthlyTotals($userId, $month);
        $formatted = date('F Y', strtotime($month . '-01'));

        $answer  = "I wasn't sure how to answer *\"{$originalQuery}\"* specifically, but here's your quick financial snapshot for {$formatted}:\n\n";
        $answer .= "- 💰 Income: " . number_format($totals['income'], 2) . "\n";
        $answer .= "- 💸 Expenses: " . number_format($totals['expense'], 2) . "\n";
        $answer .= "- 📈 Net: " . number_format($totals['net'], 2) . "\n\n";
        $answer .= "Try one of the suggested queries below for more specific insights.";

        return [
            'intent'      => 'GENERAL',
            'answer'      => $answer . self::DISCLAIMER,
            'data'        => $totals,
            'suggestions' => [
                'Where did I spend the most this month?',
                'How are my budgets doing?',
                'What are my upcoming subscriptions?',
                'How far am I from my savings goals?',
                'Any unusual spending this month?',
            ],
        ];
    }

    // ── Intent Classifier ─────────────────────────────────────────────────────
    private function classifyIntent(string $query): string
    {
        if ($this->matches($query, ['summary', 'overview', 'savings rate', 'income', 'how much did i earn', 'monthly total', 'cash flow'])) return 'MONTHLY_SUMMARY';
        if ($this->matches($query, ['spend the most', 'top category', 'most on', 'category breakdown', 'where did i spend'])) return 'CATEGORY_SPENDING';
        if ($this->matches($query, ['budget', 'over budget', 'budget status', 'spending limit', 'am i over'])) return 'BUDGET_STATUS';
        if ($this->matches($query, ['subscription', 'recurring', 'bill', 'upcoming payment', 'next payment', 'monthly commitment'])) return 'RECURRING_OBLIGATIONS';
        if ($this->matches($query, ['goal', 'savings goal', 'target', 'how far', 'progress'])) return 'GOAL_PROGRESS';
        if ($this->matches($query, ['unusual', 'spike', 'anomaly', 'higher than normal', 'overspend', 'above average'])) return 'UNUSUAL_SPENDING';
        return 'GENERAL';
    }

    private function matches(string $query, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($query, $kw)) return true;
        }
        return false;
    }
}
