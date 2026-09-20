<?php
$currency = $user['currency'] ?? '$';
$budgets  = $summary['budgets'] ?? [];
$alerts   = $summary['alerts']  ?? [];
$totalBudget = (float)($summary['total_budget'] ?? 0);
$totalSpent  = (float)($summary['total_spent'] ?? 0);
$overallPct  = $totalBudget > 0 ? min(100, round(($totalSpent / $totalBudget) * 100, 1)) : 0;
?>

<!-- Header Toolbar & Metrics -->
<div class="fp-card p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-white/10">
        <!-- Month Selector Form -->
        <form method="GET" action="<?= url('budgets') ?>" class="flex items-center gap-3">
            <label class="fp-label flex items-center gap-1.5 whitespace-nowrap">
                <i class="bi bi-calendar-month text-indigo-400"></i> Budget Month
            </label>
            <input type="month"
                   name="month"
                   value="<?= e($month) ?>"
                   onchange="this.form.submit()"
                   class="fp-input text-xs py-2 px-3 w-auto cursor-pointer">
        </form>

        <!-- Status Summary Badges -->
        <div class="flex flex-wrap items-center gap-3">
            <?php if (($summary['over_budget'] ?? 0) > 0): ?>
                <span class="fp-badge fp-badge-danger text-xs">
                    <span class="fp-badge-dot pulse"></span>
                    <i class="bi bi-exclamation-circle"></i> <?= $summary['over_budget'] ?> Over Limit
                </span>
            <?php else: ?>
                <span class="fp-badge fp-badge-success text-xs">
                    <span class="fp-badge-dot"></span>
                    <i class="bi bi-check-circle"></i> All Budgets On Track
                </span>
            <?php endif; ?>

            <div class="text-xs text-white/50 font-medium fp-nums">
                Total Allocated:
                <strong class="text-white"><?= $currency . number_format($totalSpent, 0) ?></strong>
                <span class="text-white/30">/</span>
                <span><?= $currency . number_format($totalBudget, 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Overall Monthly Utilization Bar -->
    <div class="mt-4">
        <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
            <span class="text-white/60">Aggregated Monthly Utilization</span>
            <span class="fp-nums <?= $overallPct >= 100 ? 'text-rose-400' : ($overallPct >= 80 ? 'text-amber-400' : 'text-emerald-400') ?>">
                <?= $overallPct ?>% Consumed
            </span>
        </div>
        <div class="fp-progress-track h-2">
            <div class="fp-progress-fill <?= $overallPct >= 100 ? 'fp-progress-danger' : ($overallPct >= 80 ? 'fp-progress-warning' : 'fp-progress-success') ?>"
                 style="width: <?= $overallPct ?>%;"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Active Budgets List -->
    <div class="lg:col-span-2 space-y-4">
        <?php if (empty($budgets)): ?>
            <div class="fp-card p-12 text-center text-white/30">
                <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-3 border border-white/10">
                    <i class="bi bi-pie-chart text-3xl text-indigo-400"></i>
                </div>
                <h3 class="font-semibold text-base text-white mb-1">No Budgets for <?= date('F Y', strtotime($month . '-01')) ?></h3>
                <p class="text-xs text-white/50 max-w-sm mx-auto mb-4">
                    Establish spending limits per category to automatically detect overspending and anomalies.
                </p>
            </div>
        <?php else: foreach ($budgets as $b):
            $pct = min(100, (float)$b['pct']);
            $barClass = $pct >= 100 ? 'fp-progress-danger' : ($pct >= 80 ? 'fp-progress-warning' : 'fp-progress-success');
            $statusColor = $pct >= 100 ? 'text-rose-400' : ($pct >= 80 ? 'text-amber-400' : 'text-emerald-400');
            $remaining = (float)$b['remaining'];
        ?>
            <div class="fp-card fp-card-interactive p-5">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border shadow-inner"
                             style="background: <?= e($b['color']) ?>1f; border-color: <?= e($b['color']) ?>44; color: <?= e($b['color']) ?>;">
                            <i class="<?= e($b['icon']) ?> text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-white truncate"><?= e($b['category_name']) ?></h4>
                            <p class="text-xs text-white/50 fp-nums mt-0.5">
                                <?= $currency . number_format((float)$b['spent'], 2) ?> spent of <?= $currency . number_format((float)$b['amount'], 2) ?>
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-base font-bold fp-nums <?= $statusColor ?>"><?= $b['pct'] ?>%</p>
                        <p class="text-xs fp-nums <?= $remaining >= 0 ? 'text-white/40' : 'text-rose-400 font-medium' ?>">
                            <?= $remaining >= 0 ? ($currency . number_format($remaining, 0) . ' remaining') : ($currency . number_format(abs($remaining), 0) . ' over budget') ?>
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="fp-progress-track h-2 mb-3">
                    <div class="fp-progress-fill <?= $barClass ?>" style="width: <?= $pct ?>%;"></div>
                </div>

                <!-- Footer / Delete Action -->
                <div class="flex items-center justify-between text-xs text-white/30 pt-2 border-t border-white/5">
                    <span class="font-mono text-[11px] text-white/30">Category ID: #<?= $b['category_id'] ?></span>
                    <form method="POST" action="<?= url('budgets/' . $b['id'] . '/delete') ?>"
                          onsubmit="return confirm('Remove budget for <?= e($b['category_name']) ?>?')">
                        <?= csrfField() ?>
                        <button type="submit" class="text-xs text-white/30 hover:text-rose-400 transition flex items-center gap-1">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Set / Update Budget Side Panel -->
    <div class="fp-card p-6 h-fit sticky top-20">
        <h3 class="fp-font-display text-sm font-semibold text-white flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                <i class="bi bi-plus-circle text-xs"></i>
            </div>
            <span>Set Category Budget</span>
        </h3>

        <form method="POST" action="<?= url('budgets/store') ?>" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">

            <!-- Category Select -->
            <div class="fp-input-group">
                <label for="budget_cat" class="fp-label">Expense Category <span class="text-rose-400">*</span></label>
                <select id="budget_cat" name="category_id" required class="fp-select text-xs">
                    <option value="" disabled selected>Select category…</option>
                    <?php foreach ($expenseCategories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Monthly Limit Amount -->
            <div class="fp-input-group">
                <label for="budget_amount" class="fp-label">Monthly Limit (<?= e($currency) ?>) <span class="text-rose-400">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-white/40 font-bold text-sm pointer-events-none fp-nums">
                        <?= e($currency) ?>
                    </span>
                    <input type="number"
                           id="budget_amount"
                           name="amount"
                           min="1"
                           step="0.01"
                           required
                           placeholder="500.00"
                           class="fp-input pl-9 pr-3 py-2 text-sm font-semibold fp-nums">
                </div>
                <p class="text-[11px] text-white/40 mt-1">Applies to <?= date('F Y', strtotime($month . '-01')) ?></p>
            </div>

            <button type="submit" class="fp-btn fp-btn-primary fp-btn-md w-full mt-2">
                <i class="bi bi-check2"></i> Save Budget
            </button>
        </form>
    </div>
</div>
