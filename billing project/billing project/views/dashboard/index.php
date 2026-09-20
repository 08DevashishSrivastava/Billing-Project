<?php $currency = $user['currency'] ?? '$'; ?>

<!-- Top Metrics / Stat Cards Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Net Worth -->
    <div class="fp-stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="fp-label">Total Net Worth</span>
            <div class="w-9 h-9 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                <i class="bi bi-bank2 text-base"></i>
            </div>
        </div>
        <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight fp-nums <?= $netWorth >= 0 ? 'text-white' : 'text-rose-400' ?>">
            <?= ($netWorth < 0 ? '-' : '') . $currency . number_format(abs($netWorth), 2) ?>
        </p>
        <p class="text-xs text-white/50 mt-1 flex items-center gap-1.5">
            <i class="bi bi-layers text-indigo-400"></i> Across <?= count($accounts) ?> active account<?= count($accounts) !== 1 ? 's' : '' ?>
        </p>
    </div>

    <!-- Monthly Income -->
    <div class="fp-stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="fp-label">Monthly Inflow</span>
            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                <i class="bi bi-arrow-down-circle text-base"></i>
            </div>
        </div>
        <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight text-emerald-400 fp-nums">
            <?= $currency . number_format($totals['income'], 2) ?>
        </p>
        <p class="text-xs text-white/50 mt-1">
            <?= date('F Y', strtotime(($currentMonth ?? date('Y-m')) . '-01')) ?>
        </p>
    </div>

    <!-- Monthly Expense -->
    <div class="fp-stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="fp-label">Monthly Outflow</span>
            <div class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-400 flex items-center justify-center border border-rose-500/30">
                <i class="bi bi-arrow-up-circle text-base"></i>
            </div>
        </div>
        <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight text-rose-400 fp-nums">
            <?= $currency . number_format($totals['expense'], 2) ?>
        </p>
        <p class="text-xs text-white/50 mt-1">
            <?= date('F Y', strtotime(($currentMonth ?? date('Y-m')) . '-01')) ?>
        </p>
    </div>

    <!-- Net Savings -->
    <div class="fp-stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="fp-label">Net Savings</span>
            <div class="w-9 h-9 rounded-xl bg-purple-500/15 text-purple-400 flex items-center justify-center border border-purple-500/30">
                <i class="bi bi-piggy-bank text-base"></i>
            </div>
        </div>
        <?php
        $net = $totals['net'];
        $rate = $totals['income'] > 0 ? round(($net / $totals['income']) * 100, 1) : 0;
        ?>
        <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight fp-nums <?= $net >= 0 ? 'text-purple-400' : 'text-rose-400' ?>">
            <?= ($net >= 0 ? '' : '-') . $currency . number_format(abs($net), 2) ?>
        </p>
        <p class="text-xs text-white/50 mt-1">
            Savings Rate:
            <span class="font-semibold fp-nums <?= $rate >= 20 ? 'text-emerald-400' : ($rate > 0 ? 'text-amber-400' : 'text-rose-400') ?>">
                <?= $rate ?>%
            </span>
        </p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- 6-Month Cash Flow Bar Chart -->
    <div class="fp-card p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                <i class="bi bi-bar-chart-line text-indigo-400"></i> 6-Month Cash Flow Telemetry
            </h3>
            <span class="text-xs text-white/40">Inflow vs. Outflow</span>
        </div>
        <canvas id="cashFlowChart" height="115"></canvas>
    </div>

    <!-- Spending by Category Doughnut Chart -->
    <div class="fp-card p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                    <i class="bi bi-pie-chart text-purple-400"></i> Category Allocation
                </h3>
                <a href="<?= url('reports') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 transition">Reports →</a>
            </div>

            <?php if (!empty($categoryBreakdown)): ?>
                <canvas id="categoryChart" height="135"></canvas>
                <div class="mt-4 space-y-2 max-h-36 overflow-y-auto pr-1">
                    <?php foreach (array_slice($categoryBreakdown, 0, 5) as $cat): ?>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-white/70 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0" style="background:<?= e($cat['color']) ?>"></span>
                                <span class="truncate max-w-[120px]"><?= e($cat['category'] ?? 'Other') ?></span>
                            </span>
                            <span class="font-medium text-white fp-nums"><?= $currency . number_format((float)$cat['total'], 0) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center h-44 text-white/30 text-sm">
                    <i class="bi bi-pie-chart text-3xl mb-2"></i>
                    <span>No expense data recorded this month</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Budgets & Goals Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Budgets This Month -->
    <div class="fp-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                <i class="bi bi-pie-chart-fill text-amber-400"></i> Active Budgets
            </h3>
            <a href="<?= url('budgets') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 transition">Manage Budgets →</a>
        </div>

        <?php if (empty($budgetSummary['budgets'])): ?>
            <div class="text-center text-white/30 text-sm py-8">
                <i class="bi bi-pie-chart text-3xl block mb-2 opacity-50"></i>
                <p class="mb-2">No category budgets active this month.</p>
                <a href="<?= url('budgets') ?>" class="fp-btn fp-btn-secondary fp-btn-sm">Set a Budget</a>
            </div>
        <?php else: foreach (array_slice($budgetSummary['budgets'], 0, 4) as $b):
            $pct = min(100, (float)$b['pct']);
            $barClass = $pct >= 100 ? 'fp-progress-danger' : ($pct >= 80 ? 'fp-progress-warning' : 'fp-progress-success');
        ?>
            <div class="mb-4 last:mb-0">
                <div class="flex justify-between items-center mb-1.5 text-xs">
                    <span class="text-white/80 font-medium flex items-center gap-2">
                        <i class="<?= e($b['icon']) ?>" style="color:<?= e($b['color']) ?>"></i>
                        <?= e($b['category_name']) ?>
                    </span>
                    <span class="text-white/50 fp-nums">
                        <?= $currency . number_format((float)$b['spent'], 0) ?> of <?= $currency . number_format((float)$b['amount'], 0) ?>
                        <span class="font-semibold text-white/80 ml-1">(<?= $b['pct'] ?>%)</span>
                    </span>
                </div>
                <div class="fp-progress-track h-2">
                    <div class="fp-progress-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Savings Goals -->
    <div class="fp-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                <i class="bi bi-trophy-fill text-yellow-400"></i> Savings Targets
            </h3>
            <a href="<?= url('goals') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 transition">Manage Goals →</a>
        </div>

        <?php if (empty($goals)): ?>
            <div class="text-center text-white/30 text-sm py-8">
                <i class="bi bi-trophy text-3xl block mb-2 opacity-50"></i>
                <p class="mb-2">No savings goals established yet.</p>
                <a href="<?= url('goals') ?>" class="fp-btn fp-btn-secondary fp-btn-sm">Create Savings Goal</a>
            </div>
        <?php else: foreach (array_slice($goals, 0, 4) as $g):
            $pct = \App\Models\Goal::progressPct($g);
        ?>
            <div class="mb-4 last:mb-0">
                <div class="flex justify-between items-center mb-1.5 text-xs">
                    <span class="text-white/80 font-medium flex items-center gap-1.5">
                        <i class="bi bi-bullseye text-indigo-400"></i> <?= e($g['name']) ?>
                    </span>
                    <span class="text-white/50 fp-nums font-semibold text-indigo-300"><?= $pct ?>%</span>
                </div>
                <div class="fp-progress-track h-2">
                    <div class="fp-progress-fill fp-progress-primary" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-white/40 mt-1 fp-nums">
                    <span><?= $currency . number_format((float)$g['current_amount'], 0) ?> saved</span>
                    <span><?= $currency . number_format((float)$g['target_amount'], 0) ?> target</span>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Transactions & Bills Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Recent Transactions Feed -->
    <div class="fp-card p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                <i class="bi bi-clock-history text-indigo-400"></i> Recent Ledger Activity
            </h3>
            <a href="<?= url('transactions') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 transition">View All →</a>
        </div>

        <?php if (empty($recentTx)): ?>
            <div class="text-center text-white/30 text-sm py-8">
                <i class="bi bi-arrow-left-right text-3xl block mb-2 opacity-50"></i>
                <p class="mb-2">No transactions recorded yet.</p>
                <a href="<?= url('transactions/create') ?>" class="fp-btn fp-btn-primary fp-btn-sm">+ Add Transaction</a>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($recentTx as $tx): ?>
                    <div class="flex items-center gap-3.5 py-2.5 px-3 rounded-xl hover:bg-white/[0.03] transition border border-transparent hover:border-white/5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:<?= e($tx['category_color'] ?? '#6366f1') ?>22; border: 1px solid <?= e($tx['category_color'] ?? '#6366f1') ?>44;">
                            <i class="<?= e($tx['category_icon'] ?? 'bi-tag') ?> text-sm"
                               style="color:<?= e($tx['category_color'] ?? '#6366f1') ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate"><?= e($tx['description']) ?></p>
                            <p class="text-xs text-white/40">
                                <?= e($tx['category_name'] ?? 'Uncategorised') ?> · <?= formatDate($tx['transaction_date'], 'M d') ?>
                            </p>
                        </div>
                        <span class="text-sm font-semibold fp-nums <?= $tx['type'] === 'income' ? 'text-emerald-400' : 'text-rose-400' ?>">
                            <?= formatAmountSigned((float)$tx['amount'], $tx['type'], $currency) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Bills & AI Teaser -->
    <div class="fp-card p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                    <i class="bi bi-calendar-check text-orange-400"></i> Upcoming Bills
                </h3>
                <a href="<?= url('recurring') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 transition">Manage →</a>
            </div>

            <?php if (empty($upcomingBills)): ?>
                <div class="text-center text-white/30 text-xs py-6">
                    <i class="bi bi-calendar text-2xl block mb-1.5 opacity-50"></i>
                    No commitments due in next 14 days
                </div>
            <?php else: ?>
                <div class="space-y-2 mb-4">
                    <?php foreach ($upcomingBills as $bill):
                        $days = max(0, (int)ceil((strtotime($bill['next_due_date']) - time()) / 86400));
                    ?>
                        <div class="flex items-center justify-between py-2 px-2.5 rounded-lg bg-white/[0.02] border border-white/5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-lg bg-orange-500/15 text-orange-400 flex items-center justify-center flex-shrink-0 border border-orange-500/30">
                                    <i class="<?= e($bill['icon'] ?? 'bi-arrow-repeat') ?> text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-white truncate"><?= e($bill['merchant']) ?></p>
                                    <p class="text-[11px] text-white/40">in <?= $days ?> day<?= $days !== 1 ? 's' : '' ?></p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-rose-400 fp-nums">
                                <?= $currency . number_format((float)$bill['expected_amount'], 0) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- AI Agent Quick Access Card -->
        <div class="mt-4 p-4 rounded-xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/25">
            <div class="flex items-center gap-2 mb-1.5">
                <i class="bi bi-robot text-indigo-400 text-sm"></i>
                <span class="text-xs font-semibold text-indigo-300">AI Decision Support</span>
            </div>
            <p class="text-xs text-white/60 mb-2.5 leading-snug">
                Ask questions about cash flow, spending leaks, and goal projections.
            </p>
            <a href="<?= url('agent') ?>" class="fp-btn fp-btn-secondary fp-btn-sm w-full text-xs">
                <span>Launch Assistant</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Obsidian Render Scripts -->
<script>
<?php
$labels   = array_column($chartData, 'month');
$incomes  = array_column($chartData, 'income');
$expenses = array_column($chartData, 'expense');
$catLabels = array_column($categoryBreakdown, 'category');
$catValues = array_column($categoryBreakdown, 'total');
$catColors = array_column($categoryBreakdown, 'color');
?>

// 1. Cash Flow Bar Chart
const cashCtx = document.getElementById('cashFlowChart')?.getContext('2d');
if (cashCtx) {
    new Chart(cashCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Inflow',
                    data: <?= json_encode($incomes) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Outflow',
                    data: <?= json_encode($expenses) ?>,
                    backgroundColor: 'rgba(244, 63, 94, 0.75)',
                    borderRadius: 6,
                    borderSkipped: false
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: 'rgba(255, 255, 255, 0.65)',
                        font: { family: 'Inter', size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(19, 27, 46, 0.95)',
                    borderColor: 'rgba(255, 255, 255, 0.15)',
                    borderWidth: 1,
                    titleFont: { family: 'Manrope', weight: 'bold' },
                    bodyFont: { family: 'JetBrains Mono' }
                }
            },
            scales: {
                x: {
                    ticks: { color: 'rgba(255, 255, 255, 0.45)', font: { family: 'JetBrains Mono', size: 10 } },
                    grid: { color: 'rgba(255, 255, 255, 0.04)' }
                },
                y: {
                    ticks: { color: 'rgba(255, 255, 255, 0.45)', font: { family: 'JetBrains Mono', size: 10 } },
                    grid: { color: 'rgba(255, 255, 255, 0.04)' }
                }
            }
        }
    });
}

// 2. Category Doughnut Chart
const catCtx = document.getElementById('categoryChart')?.getContext('2d');
if (catCtx && <?= count($categoryBreakdown) ?> > 0) {
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
                data: <?= json_encode($catValues) ?>,
                backgroundColor: <?= json_encode($catColors) ?>,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(19, 27, 46, 0.95)',
                    borderColor: 'rgba(255, 255, 255, 0.15)',
                    borderWidth: 1,
                    bodyFont: { family: 'JetBrains Mono' }
                }
            }
        }
    });
}
</script>
