<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Transaction;
use App\Services\AnalysisService;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId   = $this->currentUserId();
        $month    = $this->input('month', date('Y-m'));
        $txModel  = new Transaction();
        $analysis = new AnalysisService();

        $totals    = $txModel->monthlyTotals($userId, $month);
        $breakdown = $txModel->categoryBreakdown($userId, $month);
        $chartData = $txModel->last6MonthsSummary($userId);
        $anomalies = $analysis->detectUnusualSpending($userId, $month);

        // Compare with previous month
        [$year, $mo] = explode('-', $month);
        $prevMonth = date('Y-m', mktime(0, 0, 0, (int)$mo - 1, 1, (int)$year));
        $comparison = $analysis->compareMonths($userId, $prevMonth, $month);
        $savingsRate = $analysis->savingsRate($totals['income'], $totals['expense']);

        $this->view('reports.index', [
            'title'       => 'Reports — FinPilot',
            'layout'      => 'app',
            'month'       => $month,
            'totals'      => $totals,
            'breakdown'   => $breakdown,
            'chartData'   => $chartData,
            'anomalies'   => $anomalies,
            'comparison'  => $comparison,
            'savingsRate' => $savingsRate,
            'prevMonth'   => $prevMonth,
        ]);
    }

    public function export(): void
    {
        $this->requireAuth();
        $userId = $this->currentUserId();
        $month  = $this->input('month', date('Y-m'));
        $txModel = new Transaction();

        $result = $txModel->listForUser($userId, ['month' => $month], 1, 10000);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="finpilot_' . $month . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date','Type','Category','Account','Merchant','Description','Amount']);
        foreach ($result['data'] as $row) {
            fputcsv($out, [
                $row['transaction_date'],
                $row['type'],
                $row['category_name'] ?? 'Uncategorised',
                $row['account_name']  ?? '',
                $row['merchant']      ?? '',
                $row['description'],
                number_format((float)$row['amount'], 2),
            ]);
        }
        fclose($out);
        exit;
    }
}
