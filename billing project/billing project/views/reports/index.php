<?php $currency = $user['currency'] ?? '$'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left / Primary Analytics Column -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Month Selector & CSV Export Bar -->
        <div class="fp-card p-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="<?= url('reports') ?>" class="flex items-center gap-2">
                <label class="fp-label flex items-center gap-1.5 whitespace-nowrap">
                    <i class="bi bi-calendar-range text-indigo-400"></i> Reporting Period
                </label>
                <input type="month"
                       name="month"
                       value="<?= e($month) ?>"
                       onchange="this.form.submit()"
                       class="fp-input text-xs py-1.5 px-3 w-auto cursor-pointer">
            </form>

            <a href="<?= url('reports/export?month=' . $month) ?>"
               class="fp-btn fp-btn-secondary fp-btn-sm text-xs">
                <i class="bi bi-download"></i>
                <span>Export Statement (CSV)</span>
            </a>
        </div>

        <!-- 3 Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Inflow -->
            <div class="fp-stat-card">
                <span class="fp-label">Gross Inflow</span>
                <p class="fp-font-display text-2xl font-bold tracking-tight text-emerald-400 fp-nums mt-1">
                    <?= $currency . number_format($totals['income'], 0) ?>
                </p>
                <span class="text-[11px] text-white/40 block mt-1">Settled Income</span>
            </div>

            <!-- Outflow -->
            <div class="fp-stat-card">
                <span class="fp-label">Gross Outflow</span>
                <p class="fp-font-display text-2xl font-bold tracking-tight text-rose-400 fp-nums mt-1">
                    <?= $currency . number_format($totals['expense'], 0) ?>
                </p>
                <span class="text-[11px] text-white/40 block mt-1">Operating Expenses</span>
            </div>

            <!-- Savings Rate -->
            <div class="fp-stat-card">
                <span class="fp-label">Savings Ratio</span>
                <p class="fp-font-display text-2xl font-bold tracking-tight fp-nums mt-1 <?= $savingsRate >= 20 ? 'text-emerald-400' : ($savingsRate > 0 ? 'text-amber-400' : 'text-rose-400') ?>">
                    <?= $savingsRate ?>%
                </p>
                <span class="text-[11px] text-white/40 block mt-1">
                    <?= $savingsRate >= 20 ? 'Healthy accumulation' : 'Below 20% target' ?>
                </span>
            </div>
        </div>

        <!-- 6-Month Telemetry Bar Chart -->
        <div class="fp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                    <i class="bi bi-bar-chart-line text-indigo-400"></i> Multi-Month Cash Flow Telemetry
                </h3>
                <span class="text-xs text-white/40 font-mono">Trailing 6 Months</span>
            </div>
            <canvas id="cashFlowChart" height="120"></canvas>
        </div>

        <!-- Month vs. Previous Month Comparison -->
        <?php if (!empty($comparison)): ?>
            <div class="fp-card p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                    <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2">
                        <i class="bi bi-arrow-left-right text-purple-400"></i>
                        Variance vs. <?= date('F', strtotime($prevMonth . '-01')) ?>
                    </h3>
                    <span class="text-xs text-white/40">Category Delta</span>
                </div>

                <div class="space-y-3">
                    <?php foreach (array_slice($comparison, 0, 8) as $c):
                        $delta = (float)$c['delta'];
                        $isHigherExpense = $delta > 0;
                    ?>
                        <div class="flex items-center justify-between gap-3 text-xs py-1.5 px-2 rounded-lg hover:bg-white/[0.02] transition">
                            <span class="w-36 text-white/80 font-medium truncate"><?= e($c['category']) ?></span>

                            <div class="flex-1 flex items-center justify-end gap-3 fp-nums">
                                <span class="text-white/40"><?= $currency . number_format($c['month1'], 0) ?></span>
                                <span class="text-white/20">→</span>
                                <span class="text-white font-medium"><?= $currency . number_format($c['month2'], 0) ?></span>

                                <span class="w-20 text-right font-semibold <?= $isHigherExpense ? 'text-rose-400' : 'text-emerald-400' ?>">
                                    <?= $delta > 0 ? '+' : '' ?><?= $currency . number_format(abs($delta), 0) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Sidebar Column -->
    <div class="space-y-6">

        <!-- Category Doughnut Chart -->
        <div class="fp-card p-6">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 mb-4 flex items-center gap-2">
                <i class="bi bi-pie-chart text-pink-400"></i> Category Allocation
            </h3>

            <?php if (!empty($breakdown)): ?>
                <canvas id="categoryChart" height="150"></canvas>
                <div class="mt-4 space-y-2 max-h-48 overflow-y-auto pr-1">
                    <?php foreach ($breakdown as $b): ?>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-white/70 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full inline-block flex-shrink-0" style="background:<?= e($b['color']) ?>"></span>
                                <span class="truncate max-w-[130px]"><?= e($b['category'] ?? 'Other') ?></span>
                            </span>
                            <span class="text-white font-medium fp-nums"><?= $currency . number_format((float)$b['total'], 0) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-white/30 text-xs text-center py-8">No category expenses recorded for this month</p>
            <?php endif; ?>
        </div>

        <!-- Unusual Spending / Anomalies -->
        <?php if (!empty($anomalies)): ?>
            <div class="fp-card p-6 border-amber-500/30">
                <div class="flex items-center gap-2 mb-3.5 pb-2 border-b border-white/10">
                    <i class="bi bi-exclamation-triangle-fill text-amber-400 text-sm"></i>
                    <h3 class="fp-font-display text-sm font-bold text-amber-300">Spending Anomalies</h3>
                </div>

                <div class="space-y-3">
                    <?php foreach ($anomalies as $a): ?>
                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-amber-200"><?= e($a['category']) ?></p>
                                <span class="fp-badge fp-badge-warning text-[10px] py-0 px-1.5 font-mono">
                                    ×<?= $a['ratio'] ?> Baseline
                                </span>
                            </div>
                            <p class="text-[11px] text-white/60 mt-1 fp-nums">
                                <?= $currency . number_format($a['current'], 0) ?> this period vs. <?= $currency . number_format($a['avg_3m'], 0) ?> 3-month trailing average.
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
<?php
$chartLabels   = array_column($chartData, 'month');
$chartIncomes  = array_column($chartData, 'income');
$chartExpenses = array_column($chartData, 'expense');
$catLabels = array_column($breakdown, 'category');
$catValues = array_column($breakdown, 'total');
$catColors = array_column($breakdown, 'color');
?>

// Cash Flow Telemetry
const cashCtx = document.getElementById('cashFlowChart')?.getContext('2d');
if (cashCtx) {
    new Chart(cashCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [
                {
                    label: 'Inflow',
                    data: <?= json_encode($chartIncomes) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Outflow',
                    data: <?= json_encode($chartExpenses) ?>,
                    backgroundColor: 'rgba(244, 63, 94, 0.75)',
                    borderRadius: 6,
                    borderSkipped: false
                },
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: 'rgba(255, 255, 255, 0.65)', font: { family: 'Inter', size: 11 } } },
                tooltip: {
                    backgroundColor: 'rgba(19, 27, 46, 0.95)',
                    borderColor: 'rgba(255, 255, 255, 0.15)',
                    borderWidth: 1,
                    titleFont: { family: 'Manrope' },
                    bodyFont: { family: 'JetBrains Mono' }
                }
            },
            scales: {
                x: { ticks: { color: 'rgba(255, 255, 255, 0.45)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255, 255, 255, 0.04)' } },
                y: { ticks: { color: 'rgba(255, 255, 255, 0.45)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255, 255, 255, 0.04)' } }
            }
        }
    });
}

// Category Breakdown
const catCtx = document.getElementById('categoryChart')?.getContext('2d');
if (catCtx && <?= count($breakdown) ?> > 0) {
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
