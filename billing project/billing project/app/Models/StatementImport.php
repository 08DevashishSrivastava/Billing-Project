<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class StatementImport extends Model
{
    protected string $table = 'statement_imports';
    protected array $fillable = ['user_id','account_id','filename','total_rows','imported_rows','duplicate_rows'];

    public function historyForUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT si.*, a.name AS account_name
             FROM statement_imports si
             LEFT JOIN accounts a ON si.account_id = a.id
             WHERE si.user_id = ?
             ORDER BY si.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }
}
