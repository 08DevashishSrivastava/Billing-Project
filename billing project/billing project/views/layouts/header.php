<header class="glass sticky top-0 z-30 px-4 lg:px-8 py-3">
    <div class="flex items-center justify-between gap-4">
        <!-- Mobile Menu Toggle -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-white/10 transition">
            <i class="bi bi-list text-xl"></i>
        </button>

        <!-- Page Title -->
        <div class="flex-1">
            <h2 class="text-lg lg:text-xl font-semibold"><?= e($pageTitle ?? 'Dashboard') ?></h2>
            <p class="text-xs text-white/40 hidden sm:block"><?= date('l, F j, Y') ?></p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <!-- Financial Alerts Bell -->
            <div class="relative">
                <button onclick="toggleNotifications()" class="p-2 rounded-xl hover:bg-white/10 transition relative" id="bell-btn">
                    <i class="bi bi-bell text-lg"></i>
                    <?php
                    // Show red dot only if there are budget alerts
                    $hasBudgetAlerts = !empty($budgetSummary['alerts'] ?? []);
                    if ($hasBudgetAlerts): ?>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <?php endif; ?>
                </button>

                <!-- Financial Alerts Dropdown -->
                <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 rounded-xl shadow-2xl overflow-hidden z-50 border border-white/15" style="background: #0f172a; box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.85);">
                    <div class="p-4 border-b border-white/10 flex items-center justify-between bg-slate-900/95">
                        <h3 class="font-semibold text-sm text-white">Financial Alerts</h3>
                        <a href="<?= url('budgets') ?>" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition">View budgets</a>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-white/5 bg-[#0f172a]">
                        <?php
                        $alerts = $budgetSummary['alerts'] ?? [];
                        if (empty($alerts)): ?>
                            <div class="p-6 text-center text-white/60 text-sm">
                                <i class="bi bi-check-circle text-2xl text-emerald-400 block mb-2"></i>
                                No alerts — all budgets on track!
                            </div>
                        <?php else: foreach ($alerts as $alert): ?>
                            <a href="<?= url('budgets') ?>" class="flex items-start gap-3 p-4 hover:bg-white/5 transition">
                                <div class="w-8 h-8 rounded-full <?= $alert['type'] === 'over' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' ?> flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-<?= $alert['type'] === 'over' ? 'x-circle' : 'exclamation-triangle' ?> text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white"><?= e($alert['category']) ?></p>
                                    <p class="text-xs text-white/70 mt-0.5">
                                        <?= $alert['type'] === 'over' ? '<span class="text-rose-400 font-medium">Over budget</span>' : '<span class="text-amber-400 font-medium">Near limit</span>' ?> — <span class="font-semibold text-white/90"><?= $alert['pct'] ?>%</span> used
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="p-3 border-t border-white/10 bg-slate-900/95">
                        <a href="<?= url('agent') ?>" class="flex items-center justify-center gap-2 text-sm text-indigo-400 hover:text-indigo-300 font-medium transition">
                            <i class="bi bi-robot"></i> Ask AI Agent for insights
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Add Transaction -->
            <a href="<?= url('transactions/create') ?>" class="hidden sm:flex items-center gap-2 btn-primary px-4 py-2 rounded-xl text-sm font-medium">
                <i class="bi bi-plus-lg"></i>
                <span>Add Transaction</span>
            </a>
        </div>
    </div>
</header>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('hidden');
}
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    dropdown.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notifications-dropdown');
    if (!e.target.closest('#notifications-dropdown') && !e.target.closest('#bell-btn')) {
        dropdown?.classList.add('hidden');
    }
});
</script>
