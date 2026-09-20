<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\StatementImport;
use App\Models\Account;

/**
 * ImportService: CSV parsing, column mapping, duplicate detection, auto-categorization.
 */
class ImportService
{
    // Merchant keyword → category_id mapping (system defaults from seed.sql)
    private const MERCHANT_CATEGORY_MAP = [
        'netflix'       => 9,
        'spotify'       => 9,
        'prime video'   => 9,
        'hotstar'       => 9,
        'uber'          => 6,
        'ola'           => 6,
        'rapido'        => 6,
        'swiggy'        => 5,
        'zomato'        => 5,
        'bigbazaar'     => 4,
        'dmart'         => 4,
        'reliance fresh'=> 4,
        'amazon'        => 11,
        'flipkart'      => 11,
        'myntra'        => 11,
        'bescom'        => 8,
        'bses'          => 8,
        'electricity'   => 8,
        'hdfc ergo'     => 14,
        'lic'           => 14,
    ];

    public function __construct(
        private Transaction     $txModel      = new Transaction(),
        private StatementImport $importModel  = new StatementImport(),
        private Account         $accModel     = new Account(),
    ) {}

    /**
     * Parse a CSV file and return a preview array (no DB writes yet).
     * Expects columns: date, description, amount, type (or debit/credit columns).
     */
    public function parseCsv(string $filepath, array $columnMap): array
    {
        $rows    = [];
        $handle  = fopen($filepath, 'r');
        $headers = fgetcsv($handle);
        if (!$headers) { fclose($handle); return []; }

        $headers = array_map('strtolower', array_map('trim', $headers));

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;
            $mapped = array_combine($headers, $row);

            $dateRaw    = trim($mapped[$columnMap['date']]          ?? '');
            $descRaw    = trim($mapped[$columnMap['description']]   ?? '');
            $amountRaw  = trim($mapped[$columnMap['amount']]        ?? '0');
            $typeRaw    = strtolower(trim($mapped[$columnMap['type']] ?? 'expense'));

            if (!strtotime($dateRaw) || !is_numeric(str_replace([',', ' '], '', $amountRaw))) continue;

            $amount = abs((float)str_replace([',', ' '], '', $amountRaw));
            $type   = in_array($typeRaw, ['income', 'credit', 'cr']) ? 'income' : 'expense';
            $hash   = hash('sha256', $dateRaw . '|' . $descRaw . '|' . $amount . '|' . $type);

            $rows[] = [
                'transaction_date' => date('Y-m-d', strtotime($dateRaw)),
                'description'      => substr($descRaw, 0, 200),
                'merchant'         => $this->extractMerchant($descRaw),
                'amount'           => $amount,
                'type'             => $type,
                'category_id'      => $this->guessCategory($descRaw),
                'import_hash'      => $hash,
                'is_duplicate'     => $this->txModel->hashExists($hash),
            ];
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Import confirmed (non-duplicate) rows into the database.
     */
    public function importRows(int $userId, int $accountId, array $rows): array
    {
        $imported  = 0;
        $duplicates = 0;

        foreach ($rows as $row) {
            if ($row['is_duplicate']) { $duplicates++; continue; }

            $this->txModel->create([
                'user_id'          => $userId,
                'account_id'       => $accountId,
                'category_id'      => $row['category_id'] ?: null,
                'transaction_date' => $row['transaction_date'],
                'amount'           => $row['amount'],
                'type'             => $row['type'],
                'description'      => $row['description'],
                'merchant'         => $row['merchant'],
                'source'           => 'csv_import',
                'import_hash'      => $row['import_hash'],
            ]);
            $delta = $row['type'] === 'income' ? $row['amount'] : -$row['amount'];
            $this->accModel->adjustBalance($accountId, $delta);
            $imported++;
        }

        $this->importModel->create([
            'user_id'       => $userId,
            'account_id'    => $accountId,
            'filename'      => 'statement_' . date('Y-m-d') . '.csv',
            'total_rows'    => count($rows),
            'imported_rows' => $imported,
            'duplicate_rows'=> $duplicates,
        ]);

        return ['imported' => $imported, 'duplicates' => $duplicates];
    }

    private function extractMerchant(string $description): string
    {
        // Take first meaningful word(s) from description
        $cleaned = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $description);
        $parts   = array_filter(explode(' ', $cleaned));
        return implode(' ', array_slice($parts, 0, 3));
    }

    private function guessCategory(string $description): ?int
    {
        $lower = strtolower($description);
        foreach (self::MERCHANT_CATEGORY_MAP as $keyword => $catId) {
            if (str_contains($lower, $keyword)) return $catId;
        }
        return null;
    }
}
