<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Transaction extends Model
{
    protected string $table = 'transactions';
    protected array $fillable = [
        'user_id','account_id','category_id','transaction_date',
        'amount','type','description','merchant','notes','source','import_hash'
    ];

    /**
     * Recent transactions for a user with category & account names joined.
     */
    public function recentForUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color,
                    a.name AS account_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN accounts   a ON t.account_id  = a.id
             WHERE t.user_id = ?
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Paginated, filtered transaction list.
     */
    public function listForUser(int $userId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = "t.user_id = ?";
        $params = [$userId];

        if (!empty($filters['type'])) {
            $where   .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['category_id'])) {
            $where   .= " AND t.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['account_id'])) {
            $where   .= " AND t.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }
        if (!empty($filters['month'])) {
            $where   .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
            $params[] = $filters['month'];
        }
        if (!empty($filters['search'])) {
            $where   .= " AND (t.description LIKE ? OR t.merchant LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $countResult = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM transactions t WHERE {$where}", $params
        );
        $total  = (int)($countResult['cnt'] ?? 0);
        $offset = ($page - 1) * $perPage;

        $data = $this->db->fetchAll(
            "SELECT t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color,
                    a.name AS account_name
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             LEFT JOIN accounts   a ON t.account_id  = a.id
             WHERE {$where}
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Monthly income and expense totals for a user.
     */
    public function monthlyTotals(int $userId, string $month): array
    {
        $result = $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END), 0) AS income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
             FROM transactions WHERE user_id = ? AND DATE_FORMAT(transaction_date, '%Y-%m') = ?",
            [$userId, $month]
        );
        $income  = (float)($result['income']  ?? 0);
        $expense = (float)($result['expense'] ?? 0);
        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }

    /**
     * Spending by category for a given month (expense transactions only).
     */
    public function categoryBreakdown(int $userId, string $month): array
    {
        return $this->db->fetchAll(
            "SELECT c.name AS category, c.icon, c.color,
                    COALESCE(SUM(t.amount), 0) AS total
             FROM transactions t
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.user_id = ? AND t.type = 'expense'
               AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?
             GROUP BY t.category_id, c.name, c.icon, c.color
             ORDER BY total DESC",
            [$userId, $month]
        );
    }

    /**
     * Last 6 months income/expense summary for charts.
     */
    public function last6MonthsSummary(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS month,
                    COALESCE(SUM(CASE WHEN type='income'  THEN amount ELSE 0 END), 0) AS income,
                    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) AS expense
             FROM transactions
             WHERE user_id = ? AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
             ORDER BY month ASC",
            [$userId]
        );
    }

    /**
     * Check if import_hash already exists (duplicate detection).
     */
    public function hashExists(string $hash): bool
    {
        $r = $this->db->fetch("SELECT id FROM transactions WHERE import_hash = ? LIMIT 1", [$hash]);
        return (bool) $r;
    }
}
