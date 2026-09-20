<?php
$currency = $user['currency'] ?? '$';
$isEdit = !empty($tx);
$formAction = $isEdit ? url('transactions/' . $tx['id'] . '/update') : url('transactions/store');
$currentType = $tx['type'] ?? 'expense';
?>

<div class="max-w-xl mx-auto py-4">
    <div class="fp-card p-6 lg:p-8">
        <!-- Card Header -->
        <div class="flex items-center justify-between pb-5 mb-6 border-b border-white/10">
            <div>
                <h2 class="fp-font-display text-xl font-bold text-white flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                        <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> text-base"></i>
                    </div>
                    <span><?= $isEdit ? 'Edit Ledger Entry' : 'Record Transaction' ?></span>
                </h2>
                <p class="text-xs text-white/50 mt-1">
                    <?= $isEdit ? 'Update transaction details and category mapping' : 'Manually record an inflow or outflow transaction' ?>
                </p>
            </div>
            <a href="<?= url('transactions') ?>" class="fp-btn fp-btn-ghost fp-btn-sm text-xs">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <!-- Form -->
        <form method="POST" action="<?= $formAction ?>" class="space-y-5">
            <?= csrfField() ?>

            <!-- Inflow / Outflow Type Toggle -->
            <div>
                <label class="fp-label mb-2 block">Transaction Nature <span class="text-rose-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Expense -->
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="expense"
                               <?= $currentType === 'expense' ? 'checked' : '' ?> class="sr-only peer">
                        <div class="py-3 px-4 rounded-xl border border-white/10 bg-white/[0.02] peer-checked:border-rose-500 peer-checked:bg-rose-500/15 peer-checked:shadow-[0_0_16px_rgba(244,63,94,0.25)] transition flex items-center justify-center gap-2 text-rose-400 font-semibold text-sm">
                            <i class="bi bi-arrow-up-right-circle text-base"></i>
                            <span>Outflow / Expense</span>
                        </div>
                    </label>

                    <!-- Income -->
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="income"
                               <?= $currentType === 'income' ? 'checked' : '' ?> class="sr-only peer">
                        <div class="py-3 px-4 rounded-xl border border-white/10 bg-white/[0.02] peer-checked:border-emerald-500 peer-checked:bg-emerald-500/15 peer-checked:shadow-[0_0_16px_rgba(16,185,129,0.25)] transition flex items-center justify-center gap-2 text-emerald-400 font-semibold text-sm">
                            <i class="bi bi-arrow-down-left-circle text-base"></i>
                            <span>Inflow / Income</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Amount Input -->
            <div class="fp-input-group">
                <label for="amount" class="fp-label flex items-center justify-between">
                    <span>Amount <span class="text-rose-400">*</span></span>
                    <span class="text-[10px] text-white/40 font-mono">CURRENCY: <?= e($currency) ?></span>
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 text-white/50 font-bold text-lg pointer-events-none fp-nums">
                        <?= e($currency) ?>
                    </span>
                    <input type="number"
                           id="amount"
                           name="amount"
                           step="0.01"
                           min="0.01"
                           required
                           value="<?= e($tx['amount'] ?? '') ?>"
                           placeholder="0.00"
                           class="fp-input pl-11 pr-4 py-3 text-xl font-bold text-white fp-nums tracking-wide">
                </div>
            </div>

            <!-- Description -->
            <div class="fp-input-group">
                <label for="description" class="fp-label">
                    Description <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <input type="text"
                           id="description"
                           name="description"
                           required
                           maxlength="200"
                           value="<?= e($tx['description'] ?? '') ?>"
                           placeholder="e.g. AWS Cloud Services, Grocery at Whole Foods..."
                           class="fp-input">
                </div>
            </div>

            <!-- Two Column: Date & Account -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Date -->
                <div class="fp-input-group">
                    <label for="transaction_date" class="fp-label">
                        Date <span class="text-rose-400">*</span>
                    </label>
                    <input type="date"
                           id="transaction_date"
                           name="transaction_date"
                           required
                           value="<?= e($tx['transaction_date'] ?? date('Y-m-d')) ?>"
                           class="fp-input">
                </div>

                <!-- Account -->
                <div class="fp-input-group">
                    <label for="account_id" class="fp-label">
                        Account <span class="text-rose-400">*</span>
                    </label>
                    <select id="account_id" name="account_id" required class="fp-select">
                        <option value="" disabled <?= empty($tx['account_id']) ? 'selected' : '' ?>>Select account…</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" <?= ((int)($tx['account_id'] ?? 0)) === (int)$acc['id'] ? 'selected' : '' ?>>
                                <?= e($acc['name']) ?> (<?= ucfirst(str_replace('_', ' ', $acc['type'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Two Column: Category & Merchant -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Category -->
                <div class="fp-input-group">
                    <label for="category_id" class="fp-label">Category</label>
                    <select id="category_id" name="category_id" class="fp-select">
                        <option value="">Uncategorised</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ((int)($tx['category_id'] ?? 0)) === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Merchant (Optional) -->
                <div class="fp-input-group">
                    <label for="merchant" class="fp-label">Merchant (Optional)</label>
                    <input type="text"
                           id="merchant"
                           name="merchant"
                           maxlength="100"
                           value="<?= e($tx['merchant'] ?? '') ?>"
                           placeholder="e.g. Amazon, Netflix, Stripe..."
                           class="fp-input">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-lg flex-1">
                    <i class="bi bi-check2"></i>
                    <span><?= $isEdit ? 'Update Entry' : 'Save Transaction' ?></span>
                </button>
                <a href="<?= url('transactions') ?>" class="fp-btn fp-btn-secondary fp-btn-lg px-6">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
