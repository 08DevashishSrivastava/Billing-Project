<?php $currency = $user['currency'] ?? '$'; ?>

<!-- Net Worth & Actions Banner -->
<div class="fp-card p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
    
    <div>
        <span class="fp-label flex items-center gap-1.5">
            <i class="bi bi-bank2 text-indigo-400"></i> Total Net Worth
        </span>
        <p class="fp-font-display text-3xl font-extrabold tracking-tight mt-1 fp-nums <?= $netWorth >= 0 ? 'text-white' : 'text-rose-400' ?>">
            <?= ($netWorth < 0 ? '-' : '') . $currency . number_format(abs($netWorth), 2) ?>
        </p>
        <p class="text-xs text-white/50 mt-1">
            Aggregated across <?= count($accounts) ?> registered financial account<?= count($accounts) !== 1 ? 's' : '' ?>
        </p>
    </div>

    <button onclick="document.getElementById('add-account-modal').classList.remove('hidden')"
            class="fp-btn fp-btn-primary fp-btn-md shadow-lg">
        <i class="bi bi-plus-lg"></i>
        <span>Add Account</span>
    </button>
</div>

<!-- Accounts Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
    <?php if (empty($accounts)): ?>
        <div class="col-span-3 fp-card p-12 text-center text-white/40">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-3 border border-white/10">
                <i class="bi bi-bank text-3xl text-indigo-400"></i>
            </div>
            <h3 class="font-semibold text-base text-white mb-1">No Accounts Connected</h3>
            <p class="text-xs text-white/50 max-w-sm mx-auto mb-4">
                Add checking, savings, investment or credit accounts to track balances and net worth.
            </p>
            <button onclick="document.getElementById('add-account-modal').classList.remove('hidden')"
                    class="fp-btn fp-btn-primary fp-btn-sm">
                <i class="bi bi-plus-lg"></i> Connect First Account
            </button>
        </div>
    <?php else: foreach ($accounts as $acc):
        $accType = $acc['type'] ?? 'checking';
        $accColor = $acc['color'] ?? '#6366f1';
        $iconClass = match($accType) {
            'savings'     => 'bi-piggy-bank',
            'credit_card' => 'bi-credit-card-2-front',
            'cash'        => 'bi-cash-stack',
            'investment'  => 'bi-graph-up-arrow',
            default       => 'bi-bank',
        };
    ?>
        <div class="fp-card fp-card-interactive p-5 flex flex-col justify-between group relative overflow-hidden">
            <!-- Subtle Radial Card Glow -->
            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full opacity-15 pointer-events-none blur-xl"
                 style="background: <?= e($accColor) ?>;"></div>

            <div>
                <!-- Top Row: Type & Icon -->
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="fp-badge fp-badge-neutral text-[11px] mb-1.5" style="border-color: <?= e($accColor) ?>33;">
                            <span class="fp-badge-dot" style="background: <?= e($accColor) ?>;"></span>
                            <?= ucfirst(str_replace('_', ' ', $accType)) ?>
                        </span>
                        <h4 class="font-semibold text-base text-white truncate max-w-[180px]"><?= e($acc['name']) ?></h4>
                        <?php if (!empty($acc['institution'])): ?>
                            <p class="text-xs text-white/40 mt-0.5"><?= e($acc['institution']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="w-10 h-10 rounded-xl flex items-center justify-center border shadow-inner flex-shrink-0"
                         style="background: <?= e($accColor) ?>1a; border-color: <?= e($accColor) ?>44; color: <?= e($accColor) ?>;">
                        <i class="bi <?= $iconClass ?> text-lg"></i>
                    </div>
                </div>

                <!-- Balance Display -->
                <div class="my-2">
                    <span class="text-[10px] text-white/40 uppercase tracking-wider block font-mono">Current Balance</span>
                    <p class="fp-font-display text-2xl font-bold tracking-tight fp-nums mt-0.5 <?= (float)$acc['balance'] >= 0 ? 'text-white' : 'text-rose-400' ?>">
                        <?= ($acc['balance'] < 0 ? '-' : '') . $currency . number_format(abs((float)$acc['balance']), 2) ?>
                    </p>
                </div>
            </div>

            <!-- Card Footer: Actions -->
            <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                <span class="text-[11px] text-white/30 font-mono">ID: #<?= $acc['id'] ?></span>
                <form method="POST" action="<?= url('accounts/' . $acc['id'] . '/delete') ?>"
                      onsubmit="return confirm('Delete this account and all associated transactions permanently?')"
                      class="inline">
                    <?= csrfField() ?>
                    <button type="submit"
                            class="text-xs text-white/30 hover:text-rose-400 transition p-1 rounded hover:bg-white/5"
                            title="Delete Account">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- Add Account Modal (ID preserved: #add-account-modal) -->
<div id="add-account-modal" class="hidden fp-modal-backdrop" onclick="if(event.target===this){this.classList.add('hidden');}">
    <div class="fp-modal-card max-w-md w-full" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <h3 class="fp-font-display text-base font-semibold text-white flex items-center gap-2">
                <i class="bi bi-bank text-indigo-400"></i> Add Financial Account
            </h3>
            <button type="button"
                    onclick="document.getElementById('add-account-modal').classList.add('hidden')"
                    class="p-1 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="<?= url('accounts/store') ?>" class="p-6 space-y-4">
            <?= csrfField() ?>

            <!-- Account Name -->
            <div class="fp-input-group">
                <label for="acc_name" class="fp-label">Account Name <span class="text-rose-400">*</span></label>
                <input type="text"
                       id="acc_name"
                       name="name"
                       required
                       placeholder="e.g. HDFC Salary, Amex Blue, Coinbase..."
                       class="fp-input">
            </div>

            <!-- Account Type -->
            <div class="fp-input-group">
                <label for="acc_type" class="fp-label">Account Type <span class="text-rose-400">*</span></label>
                <select id="acc_type" name="type" required class="fp-select">
                    <option value="checking">Checking / Current</option>
                    <option value="savings">Savings Account</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="cash">Cash / Physical Wallet</option>
                    <option value="investment">Investment / Brokerage</option>
                    <option value="other">Other Asset</option>
                </select>
            </div>

            <!-- Opening Balance -->
            <div class="fp-input-group">
                <label for="acc_balance" class="fp-label">Opening Balance (<?= e($currency) ?>)</label>
                <input type="number"
                       id="acc_balance"
                       name="balance"
                       step="0.01"
                       value="0.00"
                       placeholder="0.00"
                       class="fp-input fp-nums font-semibold">
            </div>

            <!-- Institution -->
            <div class="fp-input-group">
                <label for="acc_inst" class="fp-label">Financial Institution (Optional)</label>
                <input type="text"
                       id="acc_inst"
                       name="institution"
                       placeholder="e.g. Chase, HDFC Bank, Fidelity..."
                       class="fp-input">
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center gap-3 pt-3 border-t border-white/10">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-md flex-1">
                    <i class="bi bi-check2"></i> Connect Account
                </button>
                <button type="button"
                        onclick="document.getElementById('add-account-modal').classList.add('hidden')"
                        class="fp-btn fp-btn-secondary fp-btn-md px-5">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('add-account-modal')?.classList.add('hidden');
    }
});
</script>
