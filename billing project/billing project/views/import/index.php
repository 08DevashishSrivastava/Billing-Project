<?php $preview = \App\Core\Session::get('import_preview'); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Upload Statement Panel -->
    <div class="fp-card p-6">
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30 flex-shrink-0">
                <i class="bi bi-cloud-arrow-up text-xl"></i>
            </div>
            <div>
                <h3 class="fp-font-display text-base font-semibold text-white">Import Statement</h3>
                <p class="text-xs text-white/50">Upload CSV / TXT bank records</p>
            </div>
        </div>

        <form method="POST" action="<?= url('import/upload') ?>" enctype="multipart/form-data" class="space-y-4">
            <?= csrfField() ?>

            <!-- Target Account -->
            <div class="fp-input-group">
                <label for="import_account" class="fp-label">Destination Account <span class="text-rose-400">*</span></label>
                <select id="import_account" name="account_id" required class="fp-select text-xs">
                    <option value="" disabled selected>Select account…</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"><?= e($acc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Statement File Input -->
            <div class="fp-input-group">
                <label class="fp-label">Statement File (.csv, .txt) <span class="text-rose-400">*</span></label>
                <div class="border-2 border-dashed border-white/15 rounded-xl p-4 text-center hover:border-indigo-500/50 hover:bg-white/[0.02] transition cursor-pointer relative group">
                    <input type="file"
                           name="statement"
                           id="statement_file"
                           accept=".csv,.txt"
                           required
                           class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                           onchange="updateFileName(this)">
                    <div class="space-y-1 pointer-events-none">
                        <i class="bi bi-file-earmark-spreadsheet text-2xl text-indigo-400 block group-hover:scale-110 transition"></i>
                        <span id="file_label" class="text-xs text-white/70 font-medium block">Choose CSV or drag here</span>
                        <span class="text-[11px] text-white/40 block">Max file size: 10MB</span>
                    </div>
                </div>
            </div>

            <!-- Column Mapping Accordion / Guide -->
            <div class="pt-2">
                <span class="fp-label text-[11px] mb-2 block text-indigo-300 flex items-center gap-1.5">
                    <i class="bi bi-sliders"></i> CSV Column Name Mapping
                </span>
                <p class="text-[11px] text-white/40 mb-3 leading-relaxed">
                    Map FinPilot's fields to the exact header titles used in your bank's CSV export.
                </p>

                <div class="space-y-2.5">
                    <?php foreach ([
                        'col_date'        => ['Date Column', 'date', 'bi-calendar'],
                        'col_description' => ['Description Column', 'description', 'bi-card-text'],
                        'col_amount'      => ['Amount Column', 'amount', 'bi-currency-exchange'],
                        'col_type'        => ['Type Column (Optional)', 'type', 'bi-arrow-left-right'],
                    ] as $name => [$label, $default, $icon]): ?>
                        <div class="fp-input-group">
                            <label class="text-[11px] text-white/60 font-medium flex items-center gap-1">
                                <i class="bi <?= $icon ?> text-white/40 text-[10px]"></i> <?= $label ?>
                            </label>
                            <input type="text"
                                   name="<?= $name ?>"
                                   value="<?= $default ?>"
                                   class="fp-input text-xs py-1.5 px-3 font-mono text-white/80">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="fp-btn fp-btn-primary fp-btn-md w-full mt-3">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <span>Parse & Preview Statement</span>
            </button>
        </form>
    </div>

    <!-- Preview & History Container -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Preview Panel (Rendered only when session has preview data) -->
        <?php if ($preview && !empty($preview['rows'])):
            $newRowsCount = count(array_filter($preview['rows'], fn($r) => !$r['is_duplicate']));
            $dupRowsCount = count(array_filter($preview['rows'], fn($r) => $r['is_duplicate']));
        ?>
            <div class="fp-card p-6 border-indigo-500/30 shadow-[0_0_24px_rgba(99,102,241,0.15)]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-4 border-b border-white/10">
                    <div>
                        <h3 class="fp-font-display text-base font-bold text-white flex items-center gap-2">
                            <i class="bi bi-eye text-indigo-400"></i> Import Preview
                        </h3>
                        <p class="text-xs text-white/50 mt-0.5">
                            Verified against existing ledger to prevent duplicate entries
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="fp-badge fp-badge-success text-xs">
                            <span class="fp-badge-dot"></span> <?= $newRowsCount ?> New Entries
                        </span>
                        <?php if ($dupRowsCount > 0): ?>
                            <span class="fp-badge fp-badge-warning text-xs">
                                <span class="fp-badge-dot pulse"></span> <?= $dupRowsCount ?> Duplicates
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="fp-table-container max-h-72 overflow-y-auto mb-4">
                    <table class="fp-table text-xs">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-right">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($preview['rows'], 0, 50) as $row): ?>
                                <tr class="<?= $row['is_duplicate'] ? 'opacity-40 bg-white/[0.01]' : '' ?>">
                                    <td class="text-white/60 fp-nums whitespace-nowrap"><?= e($row['transaction_date']) ?></td>
                                    <td class="truncate max-w-[200px] text-white font-medium"><?= e($row['description']) ?></td>
                                    <td>
                                        <span class="fp-badge <?= $row['type'] === 'income' ? 'fp-badge-income' : 'fp-badge-expense' ?> text-[10px] py-0.5 px-2">
                                            <?= ucfirst($row['type']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right fp-nums font-semibold <?= $row['type'] === 'income' ? 'text-emerald-400' : 'text-rose-400' ?>">
                                        <?= number_format((float)$row['amount'], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['is_duplicate']): ?>
                                            <span class="text-amber-400 text-[11px] font-mono">Duplicate</span>
                                        <?php else: ?>
                                            <span class="text-emerald-400 text-[11px] font-mono">New Entry</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Confirm Form -->
                <form method="POST" action="<?= url('import/confirm') ?>">
                    <?= csrfField() ?>
                    <button type="submit" class="fp-btn fp-btn-primary fp-btn-lg w-full">
                        <i class="bi bi-check2-circle text-lg"></i>
                        <span>Confirm & Persist <?= $newRowsCount ?> New Transactions</span>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Import History Card -->
        <div class="fp-card p-6">
            <h3 class="fp-font-display text-sm font-semibold text-white/90 flex items-center gap-2 mb-4 pb-3 border-b border-white/10">
                <i class="bi bi-clock-history text-indigo-400"></i> Reconciliation History
            </h3>

            <?php if (empty($history)): ?>
                <div class="text-center text-white/30 text-xs py-8">
                    <i class="bi bi-archive text-3xl block mb-2 opacity-50"></i>
                    No prior statement imports recorded.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($history as $h): ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition">
                            <div class="min-w-0 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30 flex-shrink-0">
                                    <i class="bi bi-file-earmark-check text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-white truncate"><?= e($h['account_name'] ?? 'Account') ?></p>
                                    <p class="text-[11px] text-white/40 mt-0.5"><?= formatDateTime($h['created_at']) ?></p>
                                </div>
                            </div>

                            <div class="text-right text-xs fp-nums flex-shrink-0">
                                <span class="text-emerald-400 font-semibold block">+<?= $h['imported_rows'] ?> imported</span>
                                <?php if (!empty($h['duplicate_rows'])): ?>
                                    <span class="text-amber-400 text-[11px] opacity-80 block"><?= $h['duplicate_rows'] ?> skipped</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const label = document.getElementById('file_label');
    if (input.files && input.files[0]) {
        label.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
        label.classList.add('text-indigo-300');
    }
}
</script>
