<?php $currency = $user['currency'] ?? '$'; ?>

<!-- Top Summary & Action Banner -->
<div class="fp-card p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-40 h-40 bg-orange-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-orange-500/15 text-orange-400 flex items-center justify-center border border-orange-500/30 flex-shrink-0">
            <i class="bi bi-calendar-check text-2xl"></i>
        </div>
        <div>
            <span class="fp-label">Projected Recurring Cost</span>
            <p class="fp-font-display text-2xl lg:text-3xl font-bold tracking-tight text-white fp-nums mt-0.5">
                <?= $currency . number_format($projected, 2) ?>
                <span class="text-xs text-white/40 font-normal font-sans">/ month</span>
            </p>
        </div>
    </div>

    <button onclick="document.getElementById('add-recurring-modal').classList.remove('hidden')"
            class="fp-btn fp-btn-primary fp-btn-md shadow-lg">
        <i class="bi bi-plus-lg"></i>
        <span>Add Recurring Obligation</span>
    </button>
</div>

<!-- Recurring Obligations Table -->
<div class="fp-table-container">
    <table class="fp-table">
        <thead>
            <tr>
                <th>Merchant / Obligation</th>
                <th class="hidden md:table-cell">Category</th>
                <th>Frequency</th>
                <th class="hidden lg:table-cell">Next Due Date</th>
                <th class="text-right">Expected Amount</th>
                <th class="text-center w-20">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recurring)): ?>
                <tr>
                    <td colspan="6" class="py-16 text-center text-white/30">
                        <i class="bi bi-arrow-repeat text-4xl block mb-3 opacity-40"></i>
                        <p class="text-sm font-medium text-white/60 mb-1">No recurring obligations tracked</p>
                        <p class="text-xs text-white/40 mb-4">Track subscriptions, insurance, rent and utility commitments.</p>
                        <button onclick="document.getElementById('add-recurring-modal').classList.remove('hidden')"
                                class="fp-btn fp-btn-primary fp-btn-sm">
                            <i class="bi bi-plus-lg"></i> Add First Bill
                        </button>
                    </td>
                </tr>
            <?php else: foreach ($recurring as $r):
                $days = max(0, (int)ceil((strtotime($r['next_due_date'] ?? 'today') - time()) / 86400));
                $urgent = $days <= 3;
            ?>
                <tr class="group">
                    <!-- Merchant & Sub Badge -->
                    <td>
                        <div class="flex items-center gap-2">
                            <?php if (!empty($r['is_subscription'])): ?>
                                <span class="fp-badge fp-badge-info text-[10px] py-0.5 px-2">
                                    Sub
                                </span>
                            <?php endif; ?>
                            <span class="text-sm font-medium text-white"><?= e($r['merchant']) ?></span>
                        </div>
                    </td>

                    <!-- Category -->
                    <td class="hidden md:table-cell text-xs text-white/60">
                        <span class="fp-badge fp-badge-neutral text-xs">
                            <?= e($r['category_name'] ?? 'Uncategorised') ?>
                        </span>
                    </td>

                    <!-- Frequency -->
                    <td class="text-xs text-white/70">
                        <span class="inline-flex items-center gap-1">
                            <i class="bi bi-arrow-repeat text-indigo-400"></i>
                            <?= recurringFrequencyLabel($r['frequency']) ?>
                        </span>
                    </td>

                    <!-- Next Due Date -->
                    <td class="hidden lg:table-cell text-xs fp-nums">
                        <?php if ($r['next_due_date']): ?>
                            <div class="flex items-center gap-1.5 <?= $urgent ? 'text-rose-400 font-semibold' : 'text-white/60' ?>">
                                <?php if ($urgent): ?>
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <?php endif; ?>
                                <span><?= formatDate($r['next_due_date']) ?></span>
                                <span class="text-[11px] opacity-70">(in <?= $days ?>d)</span>
                            </div>
                        <?php else: ?>
                            <span class="text-white/30">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Expected Amount -->
                    <td class="text-right font-semibold fp-nums text-sm text-rose-400 whitespace-nowrap">
                        <?= $currency . number_format((float)$r['expected_amount'], 2) ?>
                    </td>

                    <!-- Delete Action -->
                    <td class="text-center">
                        <form method="POST" action="<?= url('recurring/' . $r['id'] . '/delete') ?>"
                              onsubmit="return confirm('Remove recurring obligation for <?= e($r['merchant']) ?>?')">
                            <?= csrfField() ?>
                            <button type="submit"
                                    class="p-1.5 rounded-lg hover:bg-rose-500/20 text-white/40 hover:text-rose-400 transition"
                                    title="Delete Obligation">
                                <i class="bi bi-trash text-xs"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Recurring Modal (ID preserved: #add-recurring-modal) -->
<div id="add-recurring-modal" class="hidden fp-modal-backdrop" onclick="if(event.target===this){this.classList.add('hidden');}">
    <div class="fp-modal-card max-w-md w-full" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <h3 class="fp-font-display text-base font-semibold text-white flex items-center gap-2">
                <i class="bi bi-arrow-repeat text-orange-400"></i> Add Recurring Obligation
            </h3>
            <button type="button"
                    onclick="document.getElementById('add-recurring-modal').classList.add('hidden')"
                    class="p-1 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form method="POST" action="<?= url('recurring/store') ?>" class="p-6 space-y-4">
            <?= csrfField() ?>

            <!-- Merchant / Name -->
            <div class="fp-input-group">
                <label for="rec_merchant" class="fp-label">Merchant / Service Name <span class="text-rose-400">*</span></label>
                <input type="text"
                       id="rec_merchant"
                       name="merchant"
                       required
                       placeholder="e.g. Netflix, Rent, Electricity, Gym..."
                       class="fp-input">
            </div>

            <!-- Amount & Frequency -->
            <div class="grid grid-cols-2 gap-3">
                <div class="fp-input-group">
                    <label for="rec_amount" class="fp-label">Expected (<?= e($currency) ?>) <span class="text-rose-400">*</span></label>
                    <input type="number"
                           id="rec_amount"
                           name="expected_amount"
                           step="0.01"
                           min="0.01"
                           required
                           placeholder="49.00"
                           class="fp-input fp-nums font-semibold">
                </div>

                <div class="fp-input-group">
                    <label for="rec_freq" class="fp-label">Frequency <span class="text-rose-400">*</span></label>
                    <select id="rec_freq" name="frequency" class="fp-select text-xs">
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <!-- Category -->
            <div class="fp-input-group">
                <label for="rec_cat" class="fp-label">Category</label>
                <select id="rec_cat" name="category_id" class="fp-select text-xs">
                    <option value="">None / Uncategorised</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Next Due Date -->
            <div class="fp-input-group">
                <label for="rec_due" class="fp-label">Next Due Date</label>
                <input type="date"
                       id="rec_due"
                       name="next_due_date"
                       class="fp-input text-xs">
            </div>

            <!-- Is Subscription Checkbox -->
            <div class="flex items-center gap-2.5 pt-1">
                <input type="checkbox"
                       name="is_subscription"
                       id="is_sub"
                       value="1"
                       class="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-0 cursor-pointer">
                <label for="is_sub" class="text-xs text-white/70 cursor-pointer select-none">
                    This is a recurring SaaS / streaming subscription
                </label>
            </div>

            <!-- Modal Actions -->
            <div class="flex items-center gap-3 pt-3 border-t border-white/10">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-md flex-1">
                    <i class="bi bi-check2"></i> Add Obligation
                </button>
                <button type="button"
                        onclick="document.getElementById('add-recurring-modal').classList.add('hidden')"
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
        document.getElementById('add-recurring-modal')?.classList.add('hidden');
    }
});
</script>
