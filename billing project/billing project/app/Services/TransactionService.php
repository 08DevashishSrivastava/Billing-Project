<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;

/**
 * TransactionService: all ledger mutations + balance updates.
 */
class TransactionService
{
    public function __construct(
        private Transaction $txModel   = new Transaction(),
        private Account     $accModel  = new Account(),
    ) {}

    /**
     * Create a transaction and adjust the account balance accordingly.
     */
    public function create(int $userId, array $data): int
    {
        $data['user_id'] = $userId;
        $id = $this->txModel->create($data);

        $delta = $data['type'] === 'income'
            ? (float)$data['amount']
            : -(float)$data['amount'];
        $this->accModel->adjustBalance((int)$data['account_id'], $delta);

        return $id;
    }

    /**
     * Update a transaction, reversing the old balance effect first.
     */
    public function update(int $txId, int $userId, array $data): bool
    {
        $old = $this->txModel->findForUser($txId, $userId);
        if (!$old) return false;

        // Reverse old effect
        $oldDelta = $old['type'] === 'income' ? -(float)$old['amount'] : (float)$old['amount'];
        $this->accModel->adjustBalance((int)$old['account_id'], $oldDelta);

        // Apply new effect
        $newDelta = $data['type'] === 'income' ? (float)$data['amount'] : -(float)$data['amount'];
        $this->accModel->adjustBalance((int)($data['account_id'] ?? $old['account_id']), $newDelta);

        return $this->txModel->update($txId, $data);
    }

    /**
     * Delete a transaction and reverse its balance effect.
     */
    public function delete(int $txId, int $userId): bool
    {
        $tx = $this->txModel->findForUser($txId, $userId);
        if (!$tx) return false;

        $delta = $tx['type'] === 'income' ? -(float)$tx['amount'] : (float)$tx['amount'];
        $this->accModel->adjustBalance((int)$tx['account_id'], $delta);

        return $this->txModel->delete($txId);
    }
}
