<?php $currency = $user['currency'] ?? '$'; ?>

<!-- Top Action Banner -->
<div class="fp-card p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-40 h-40 bg-yellow-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div>
        <span class="fp-label flex items-center gap-1.5">
            <i class="bi bi-trophy-fill text-yellow-400"></i> Financial Milestones
        </span>
        <h2 class="fp-font-display text-2xl font-bold text-white tracking-tight mt-1">Savings Goals</h2>
        <p class="text-xs text-white/50 mt-0.5">
            Track dedicated savings reserves, emergency buffers, and capital accumulation targets.
        </p>
    </div>

    <button onclick="document.getElementById('add-goal-modal').classList.remove('hidden')"
            class="fp-btn fp-btn-primary fp-btn-md shadow-lg">
        <i class="bi bi-plus-lg"></i>
        <span>New Savings Goal</span>
    </button>
</div>

<!-- Goals Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php if (empty($goals)): ?>
        <div class="col-span-3 fp-card p-12 text-center text-white/30">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-3 border border-white/10">
                <i class="bi bi-trophy text-4xl text-yellow-400"></i>
            </div>
            <h3 class="font-semibold text-base text-white mb-1">No Savings Goals Established</h3>
            <p class="text-xs text-white/50 max-w-sm mx-auto mb-4">
                Define target objectives to track progress and receive automated surplus allocation advice.
            </p>
            <button onclick="document.getElementById('add-goal-modal').classList.remove('hidden')"
                    class="fp-btn fp-btn-primary fp-btn-sm">
                <i class="bi bi-plus-lg"></i> Create First Goal
            </button>
        </div>
    <?php else: foreach ($goals as $g):
        $pct = \App\Models\Goal::progressPct($g);
        $statusColors = [
            'achieved' => ['badge' => 'fp-badge-success', 'dot' => '#10b981'],
            'paused'   => ['badge' => 'fp-badge-warning', 'dot' => '#f59e0b'],
            'active'   => ['badge' => 'fp-badge-info',    'dot' => '#6366f1'],
        ];
        $st = $statusColors[$g['status']] ?? $statusColors['active'];
    ?>
        <div class="fp-card fp-card-interactive p-5 flex flex-col justify-between group">
            <div>
                <!-- Top Header -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0 flex-1">
                        <span class="fp-badge <?= $st['badge'] ?> text-[11px] mb-1.5">
                            <span class="fp-badge-dot" style="background: <?= $st['dot'] ?>;"></span>
                            <?= ucfirst($g['status']) ?>
                        </span>
                        <h4 class="font-semibold text-base text-white truncate"><?= e($g['name']) ?></h4>
                    </div>

                    <?php if ($g['target_date']): ?>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[10px] text-white/40 block uppercase tracking-wider font-mono">Target Date</span>
                            <span class="text-xs text-indigo-300 font-medium whitespace-nowrap">
                                🎯 <?= formatDate($g['target_date'], 'M Y') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Progress Gauge -->
                <div class="my-3">
                    <div class="flex justify-between text-xs mb-1.5 font-medium">
                        <span class="text-white/70 fp-nums">
                            <?= $currency . number_format((float)$g['current_amount'], 0) ?>
                            <span class="text-white/40 font-normal">saved</span>
                        </span>
                        <span class="text-indigo-400 fp-nums font-bold"><?= $pct ?>%</span>
                    </div>

                    <div class="fp-progress-track h-2">
                        <div class="fp-progress-fill fp-progress-primary" style="width: <?= $pct ?>%;"></div>
                    </div>

                    <p class="text-[11px] text-white/40 mt-1.5 text-right fp-nums">
                        Goal: <?= $currency . number_format((float)$g['target_amount'], 0) ?>
                    </p>
                </div>

                <?php if ($g['notes']): ?>
                    <p class="text-xs text-white/50 line-clamp-2 mt-2 leading-relaxed bg-white/[0.02] p-2 rounded-lg border border-white/5">
                        <?= e($g['notes']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Card Actions -->
            <div class="mt-4 pt-3 border-t border-white/10 flex items-center gap-2">
                <?php if ($g['status'] === 'active'): ?>
                    <button onclick="openDeposit(<?= $g['id'] ?>, '<?= e(addslashes($g['name'])) ?>')"
                            class="fp-btn fp-btn-primary fp-btn-sm flex-1 text-xs">
                        <i class="bi bi-plus-circle"></i> Add Funds
                    </button>
                <?php endif; ?>

                <form method="POST" action="<?= url('goals/' . $g['id'] . '/delete') ?>"
                      onsubmit="return confirm('Delete this savings goal?')"
                      class="inline ml-auto">
                    <?= csrfField() ?>
                    <button type="submit"
                            class="fp-btn fp-btn-secondary fp-btn-sm text-xs px-2.5 hover:text-rose-400"
                            title="Delete Goal">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- Add Goal Modal (ID preserved: #add-goal-modal) -->
<div id="add-goal-modal" class="hidden fp-modal-backdrop" onclick="if(event.target===this){this.classList.add('hidden');}">
    <div class="fp-modal-card max-w-md w-full" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <h3 class="fp-font-display text-base font-semibold text-white flex items-center gap-2">
                <i class="bi bi-trophy text-yellow-400"></i> New Savings Goal
            </h3>
            <button type="button"
                    onclick="document.getElementById('add-goal-modal').classList.add('hidden')"
                    class="p-1 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form method="POST" action="<?= url('goals/store') ?>" class="p-6 space-y-4">
            <?= csrfField() ?>

            <!-- Name -->
            <div class="fp-input-group">
                <label for="goal_name" class="fp-label">Goal Name <span class="text-rose-400">*</span></label>
                <input type="text"
                       id="goal_name"
                       name="name"
                       required
                       placeholder="e.g. 6-Month Emergency Buffer, Japan 2027..."
                       class="fp-input">
            </div>

            <!-- Target Amount -->
            <div class="fp-input-group">
                <label for="goal_target" class="fp-label">Target Amount (<?= e($currency) ?>) <span class="text-rose-400">*</span></label>
                <input type="number"
                       id="goal_target"
                       name="target_amount"
                       step="0.01"
                       min="1"
                       required
                       placeholder="10000"
                       class="fp-input fp-nums font-semibold">
            </div>

            <!-- Starting Amount -->
            <div class="fp-input-group">
                <label for="goal_current" class="fp-label">Starting Amount (<?= e($currency) ?>)</label>
                <input type="number"
                       id="goal_current"
                       name="current_amount"
                       step="0.01"
                       min="0"
                       value="0"
                       placeholder="0.00"
                       class="fp-input fp-nums">
            </div>

            <!-- Target Date -->
            <div class="fp-input-group">
                <label for="goal_date" class="fp-label">Target Completion Date (Optional)</label>
                <input type="date"
                       id="goal_date"
                       name="target_date"
                       class="fp-input text-xs">
            </div>

            <!-- Notes -->
            <div class="fp-input-group">
                <label for="goal_notes" class="fp-label">Purpose & Strategy Notes</label>
                <textarea id="goal_notes"
                          name="notes"
                          rows="2"
                          placeholder="Why this milestone matters to your financial plan…"
                          class="fp-textarea text-xs resize-none"></textarea>
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center gap-3 pt-3 border-t border-white/10">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-md flex-1">
                    <i class="bi bi-check2"></i> Create Milestone
                </button>
                <button type="button"
                        onclick="document.getElementById('add-goal-modal').classList.add('hidden')"
                        class="fp-btn fp-btn-secondary fp-btn-md px-5">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Deposit Modal (IDs preserved: #deposit-modal, #deposit-form, #deposit-goal-name) -->
<div id="deposit-modal" class="hidden fp-modal-backdrop" onclick="if(event.target===this){this.classList.add('hidden');}">
    <div class="fp-modal-card max-w-sm w-full" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <div>
                <h3 class="fp-font-display text-base font-semibold text-white flex items-center gap-2">
                    <i class="bi bi-piggy-bank text-emerald-400"></i> Add Funds to Goal
                </h3>
                <p id="deposit-goal-name" class="text-xs text-indigo-300 font-medium mt-0.5"></p>
            </div>
            <button type="button"
                    onclick="document.getElementById('deposit-modal').classList.add('hidden')"
                    class="p-1 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form id="deposit-form" method="POST" action="" class="p-6 space-y-4">
            <?= csrfField() ?>

            <div class="fp-input-group">
                <label for="deposit_amount" class="fp-label">Deposit Amount (<?= e($currency) ?>) <span class="text-rose-400">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-white/40 font-bold text-lg pointer-events-none fp-nums">
                        <?= e($currency) ?>
                    </span>
                    <input type="number"
                           id="deposit_amount"
                           name="amount"
                           step="0.01"
                           min="0.01"
                           required
                           placeholder="500.00"
                           class="fp-input pl-10 pr-3 py-2.5 text-lg font-bold fp-nums text-emerald-400">
                </div>
            </div>

            <!-- Quick Amount Chips -->
            <div>
                <span class="fp-label text-[10px] block mb-1.5">Quick Presets</span>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ([50, 100, 250, 500, 1000] as $preset): ?>
                        <button type="button"
                                onclick="document.getElementById('deposit_amount').value = '<?= $preset ?>'"
                                class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-indigo-500/20 text-xs font-mono text-white/70 hover:text-indigo-300 border border-white/10 transition">
                            +<?= $currency . $preset ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center gap-3 pt-3 border-t border-white/10">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-md flex-1">
                    <i class="bi bi-check2"></i> Deposit
                </button>
                <button type="button"
                        onclick="document.getElementById('deposit-modal').classList.add('hidden')"
                        class="fp-btn fp-btn-secondary fp-btn-md px-5">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeposit(goalId, goalName) {
    document.getElementById('deposit-goal-name').textContent = goalName;
    document.getElementById('deposit-form').action = '<?= url("goals") ?>/' + goalId + '/deposit';
    document.getElementById('deposit_amount').value = '';
    document.getElementById('deposit-modal').classList.remove('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('add-goal-modal')?.classList.add('hidden');
        document.getElementById('deposit-modal')?.classList.add('hidden');
    }
});
</script>
