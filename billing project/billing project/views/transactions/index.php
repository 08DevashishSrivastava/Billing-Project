<?php $currency = $user['currency'] ?? '$'; ?>

<!-- Filter & Search Toolbar -->
<div class="fp-card p-5 mb-6">
    <form method="GET" action="<?= url('transactions') ?>" class="flex flex-wrap gap-4 items-end">
        <!-- Month Filter -->
        <div class="w-full sm:w-auto">
            <label class="fp-label flex items-center gap-1.5 mb-1.5">
                <i class="bi bi-calendar3 text-indigo-400"></i> Month
            </label>
            <input type="month"
                   name="month"
                   value="<?= e($filters['month'] ?? date('Y-m')) ?>"
                   class="fp-input text-xs py-2 px-3">
        </div>

        <!-- Type Filter -->
        <div class="w-full sm:w-auto">
            <label class="fp-label flex items-center gap-1.5 mb-1.5">
                <i class="bi bi-arrow-left-right text-indigo-400"></i> Type
            </label>
            <select name="type" class="fp-select text-xs py-2 pl-3 pr-8 min-w-[130px]">
                <option value="">All Transactions</option>
                <option value="income"  <?= ($filters['type'] ?? '') === 'income'  ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
        </div>

        <!-- Account Filter -->
        <div class="w-full sm:w-auto">
            <label class="fp-label flex items-center gap-1.5 mb-1.5">
                <i class="bi bi-bank text-indigo-400"></i> Account
            </label>
            <select name="account_id" class="fp-select text-xs py-2 pl-3 pr-8 min-w-[150px]">
                <option value="">All Accounts</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= ((int)($filters['account_id'] ?? 0)) === (int)$acc['id'] ? 'selected' : '' ?>>
                        <?= e($acc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Search Input -->
        <div class="w-full sm:w-auto flex-1 min-w-[200px]">
            <label class="fp-label flex items-center gap-1.5 mb-1.5">
                <i class="bi bi-search text-indigo-400"></i> Search
            </label>
            <div class="relative">
                <input type="text"
                       name="search"
                       value="<?= e($filters['search'] ?? '') ?>"
                       placeholder="Merchant, keyword or note…"
                       class="fp-input text-xs py-2 pl-8 pr-3">
                <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-white/30 text-xs pointer-events-none"></i>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto pt-1 sm:pt-0">
            <button type="submit" class="fp-btn fp-btn-primary fp-btn-sm py-2 px-4">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="<?= url('transactions') ?>" class="fp-btn fp-btn-secondary fp-btn-sm py-2 px-3">
                Reset
            </a>
            <a href="<?= url('transactions/create') ?>" class="fp-btn fp-btn-primary fp-btn-sm py-2 px-4 ml-auto sm:ml-2">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden sm:inline">Add Transaction</span>
                <span class="sm:hidden">Add</span>
            </a>
        </div>
    </form>
</div>

<!-- Ledger Table Card -->
<div class="fp-table-container">
    <table class="fp-table">
        <thead>
            <tr>
                <th class="w-28">Date</th>
                <th>Description</th>
                <th class="hidden md:table-cell">Category</th>
                <th class="hidden lg:table-cell">Account</th>
                <th class="text-right">Amount</th>
                <th class="text-center w-24">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result['data'])): ?>
                <tr>
                    <td colspan="6" class="py-16 text-center text-white/30">
                        <i class="bi bi-arrow-left-right text-4xl block mb-3 opacity-40"></i>
                        <p class="text-sm font-medium text-white/60 mb-1">No transactions found</p>
                        <p class="text-xs text-white/40 mb-4">Try adjusting your filters or record a new entry.</p>
                        <a href="<?= url('transactions/create') ?>" class="fp-btn fp-btn-primary fp-btn-sm">
                            <i class="bi bi-plus-lg"></i> Add First Transaction
                        </a>
                    </td>
                </tr>
            <?php else: foreach ($result['data'] as $tx): ?>
                <tr class="group">
                    <!-- Date -->
                    <td class="text-white/60 text-xs fp-nums whitespace-nowrap">
                        <?= formatDate($tx['transaction_date'], 'M d, Y') ?>
                    </td>

                    <!-- Description -->
                    <td>
                        <p class="text-sm font-medium text-white truncate max-w-xs md:max-w-md">
                            <?= e($tx['description']) ?>
                        </p>
                        <?php if (!empty($tx['merchant'])): ?>
                            <p class="text-[11px] text-white/40 flex items-center gap-1 mt-0.5">
                                <i class="bi bi-shop text-[10px]"></i> <?= e($tx['merchant']) ?>
                            </p>
                        <?php endif; ?>
                    </td>

                    <!-- Category -->
                    <td class="hidden md:table-cell">
                        <span class="fp-badge fp-badge-neutral text-xs" style="border-color: <?= e($tx['category_color'] ?? '#6366f1') ?>33;">
                            <i class="<?= e($tx['category_icon'] ?? 'bi-tag') ?>" style="color: <?= e($tx['category_color'] ?? '#818cf8') ?>"></i>
                            <?= e($tx['category_name'] ?? 'Uncategorised') ?>
                        </span>
                    </td>

                    <!-- Account -->
                    <td class="hidden lg:table-cell text-xs text-white/60">
                        <span class="flex items-center gap-1.5">
                            <i class="bi bi-wallet2 text-white/30 text-xs"></i>
                            <?= e($tx['account_name'] ?? 'Default') ?>
                        </span>
                    </td>

                    <!-- Amount -->
                    <td class="text-right font-semibold fp-nums text-sm whitespace-nowrap <?= $tx['type'] === 'income' ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= formatAmountSigned((float)$tx['amount'], $tx['type'], $currency) ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-center">
                        <div class="inline-flex items-center gap-1 opacity-80 group-hover:opacity-100 transition">
                            <a href="<?= url('transactions/' . $tx['id'] . '/edit') ?>"
                               class="p-1.5 rounded-lg hover:bg-white/10 text-white/40 hover:text-indigo-300 transition"
                               title="Edit Entry">
                                <i class="bi bi-pencil text-xs"></i>
                            </a>
                            <form method="POST" action="<?= url('transactions/' . $tx['id'] . '/delete') ?>" class="inline"
                                  onsubmit="return confirm('Delete this transaction permanently?')">
                                <?= csrfField() ?>
                                <button class="p-1.5 rounded-lg hover:bg-rose-500/20 text-white/40 hover:text-rose-400 transition"
                                        type="submit"
                                        title="Delete Entry">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Pagination Toolbar -->
    <?php if ($result['last_page'] > 1): ?>
        <div class="px-6 py-4 border-t border-white/10 flex items-center justify-between text-xs text-white/50 bg-white/[0.01]">
            <span>
                Showing page <strong class="text-white fp-nums"><?= $result['current_page'] ?></strong> of <strong class="text-white fp-nums"><?= $result['last_page'] ?></strong>
            </span>
            <div class="flex items-center gap-2">
                <?php if ($result['current_page'] > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $result['current_page'] - 1])) ?>"
                       class="fp-btn fp-btn-secondary fp-btn-sm">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                <?php if ($result['current_page'] < $result['last_page']): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $result['current_page'] + 1])) ?>"
                       class="fp-btn fp-btn-secondary fp-btn-sm">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
