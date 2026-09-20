<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * AnalysisService: spending trends, anomaly detection, recurring detection.
 */
class AnalysisService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Detect categories where current month spending exceeds 1.5× the 3-month average.
     * Returns anomalies with cautious phrasing (not advisory).
     */
    public function detectUnusualSpending(int $userId, string $month): array
    {
        $threeMonthAvg = $this->db->fetchAll(
            "SELECT category_id, c.name AS category_name,
                    AVG(monthly_total) AS avg_3m
             FROM (
                 SELECT category_id, SUM(amount) AS monthly_total
                 FROM transactions
                 WHERE user_id = ? AND type = 'expense'
                   AND transaction_date < DATE_FORMAT(?, '%Y-%m-01')
                   AND transaction_date >= DATE_SUB(DATE_FORMAT(?, '%Y-%m-01'), INTERVAL 3 MONTH)
                 GROUP BY category_id, DATE_FORMAT(transaction_date, '%Y-%m')
             ) sub
             JOIN categories c ON c.id = sub.category_id
             GROUP BY category_id, c.name",
            [$userId, $month, $month]
        );

        $currentMonth = $this->db->fetchAll(
            "SELECT category_id, SUM(amount) AS current_total
             FROM transactions
             WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ?
             GROUP BY category_id",
            [$userId, $month]
        );
        $currentByCategory = array_column($currentMonth, 'current_total', 'category_id');

        $anomalies = [];
        foreach ($threeMonthAvg as $row) {
            $catId   = $row['category_id'];
            $avg     = (float)$row['avg_3m'];
            $current = (float)($currentByCategory[$catId] ?? 0);
            if ($avg > 0 && $current > ($avg * 1.5)) {
                $anomalies[] = [
                    'category'     => $row['category_name'],
                    'current'      => $current,
                    'avg_3m'       => round($avg, 2),
                    'ratio'        => round($current / $avg, 2),
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Compute month-over-month spending comparison for two months.
     */
    public function compareMonths(int $userId, string $month1, string $month2): array
    {
        $fetch = function (string $m) use ($userId): array {
            $rows = $this->db->fetchAll(
                "SELECT category_id, c.name AS category_name, SUM(amount) AS total
                 FROM transactions t
                 JOIN categories c ON t.category_id = c.id
                 WHERE t.user_id = ? AND t.type = 'expense'
                   AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
                 GROUP BY category_id, c.name",
                [$userId, $m]
            );
            return array_column($rows, null, 'category_id');
        };

        $m1data = $fetch($month1);
        $m2data = $fetch($month2);
        $allCats = array_unique(array_merge(array_keys($m1data), array_keys($m2data)));

        $comparison = [];
        foreach ($allCats as $catId) {
            $m1total = (float)($m1data[$catId]['total'] ?? 0);
            $m2total = (float)($m2data[$catId]['total'] ?? 0);
            $name    = $m1data[$catId]['category_name'] ?? $m2data[$catId]['category_name'] ?? 'Uncategorised';
            $comparison[] = [
                'category'   => $name,
                'month1'     => $m1total,
                'month2'     => $m2total,
                'delta'      => round($m2total - $m1total, 2),
                'pct_change' => $m1total > 0 ? round((($m2total - $m1total) / $m1total) * 100, 1) : null,
            ];
        }

        usort($comparison, fn($a, $b) => $b['delta'] <=> $a['delta']);
        return $comparison;
    }

    /**
     * Overall savings rate for a month.
     */
    public function savingsRate(float $income, float $expense): float
    {
        if ($income <= 0) return 0.0;
        return round((($income - $expense) / $income) * 100, 1);
    }
}
