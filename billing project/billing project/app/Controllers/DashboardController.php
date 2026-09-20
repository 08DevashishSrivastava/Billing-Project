<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Goal;
use App\Models\RecurringObligation;
use App\Services\BudgetService;
use App\Services\AnalysisService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $month  = date('Y-m');

        $txModel        = new Transaction();
        $accModel       = new Account();
        $goalModel      = new Goal();
        $recurringModel = new RecurringObligation();
        $budgetSvc      = new BudgetService();

        // ── Core Metrics ──────────────────────────────────────────────────────
        $totals      = $txModel->monthlyTotals($userId, $month);
        $netWorth    = $accModel->totalNetWorthForUser($userId);
        $accounts    = $accModel->forUser($userId);

        // ── Budget Summary ────────────────────────────────────────────────────
        $budgetSummary = $budgetSvc->monthSummary($userId, $month);

        // ── Recent Transactions ───────────────────────────────────────────────
        $recentTx  = $txModel->recentForUser($userId, 8);

        // ── Charts: Last 6 Months ─────────────────────────────────────────────
        $chartData = $txModel->last6MonthsSummary($userId);

        // ── Category Breakdown ────────────────────────────────────────────────
        $categoryBreakdown = $txModel->categoryBreakdown($userId, $month);

        // ── Goals ─────────────────────────────────────────────────────────────
        $goals = array_filter($goalModel->forUser($userId), fn($g) => $g['status'] === 'active');

        // ── Upcoming Bills ────────────────────────────────────────────────────
        $upcomingBills = $recurringModel->upcomingForUser($userId, 14);

        $this->view('dashboard.index', [
            'title'             => 'Dashboard — FinPilot',
            'layout'            => 'app',
            'totals'            => $totals,
            'netWorth'          => $netWorth,
            'accounts'          => $accounts,
            'budgetSummary'     => $budgetSummary,
            'recentTx'          => $recentTx,
            'chartData'         => $chartData,
            'categoryBreakdown' => $categoryBreakdown,
            'goals'             => array_values($goals),
            'upcomingBills'     => $upcomingBills,
            'currentMonth'      => $month,
        ]);
    }
}
