<div class="max-w-2xl mx-auto space-y-6 py-2">

    <!-- Profile Settings Card -->
    <div class="fp-card p-6 lg:p-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center border border-indigo-500/30 flex-shrink-0">
                <i class="bi bi-person-gear text-xl"></i>
            </div>
            <div>
                <h3 class="fp-font-display text-base font-bold text-white">Profile Preferences</h3>
                <p class="text-xs text-white/50">Manage personal identification and account currency</p>
            </div>
        </div>

        <form method="POST" action="<?= url('settings/update') ?>" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="profile">

            <!-- Name -->
            <div class="fp-input-group">
                <label for="profile_name" class="fp-label">Full Name <span class="text-rose-400">*</span></label>
                <input type="text"
                       id="profile_name"
                       name="name"
                       required
                       value="<?= e($user['name'] ?? '') ?>"
                       class="fp-input">
            </div>

            <!-- Email -->
            <div class="fp-input-group">
                <label for="profile_email" class="fp-label">Email Address <span class="text-rose-400">*</span></label>
                <input type="email"
                       id="profile_email"
                       name="email"
                       required
                       value="<?= e($user['email'] ?? '') ?>"
                       class="fp-input">
            </div>

            <!-- Default Currency -->
            <div class="fp-input-group">
                <label for="profile_curr" class="fp-label">Default Financial Currency</label>
                <select id="profile_curr" name="currency" class="fp-select text-xs">
                    <?php foreach ($currencies as $symbol => $label): ?>
                        <option value="<?= e($symbol) ?>" <?= ($user['currency'] ?? '$') === $symbol ? 'selected' : '' ?>>
                            <?= e($symbol) ?> — <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[11px] text-white/40 mt-1">
                    Currency symbol used on dashboards, charts, and transaction ledgers.
                </p>
            </div>

            <div class="pt-2">
                <button type="submit" class="fp-btn fp-btn-primary fp-btn-md">
                    <i class="bi bi-check2"></i> Save Profile Preferences
                </button>
            </div>
        </form>
    </div>

    <!-- Password / Security Card -->
    <div class="fp-card p-6 lg:p-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/10">
            <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-400 flex items-center justify-center border border-purple-500/30 flex-shrink-0">
                <i class="bi bi-shield-lock text-xl"></i>
            </div>
            <div>
                <h3 class="fp-font-display text-base font-bold text-white">Security & Password</h3>
                <p class="text-xs text-white/50">Update credential keys for session protection</p>
            </div>
        </div>

        <form method="POST" action="<?= url('settings/update') ?>" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="password">

            <!-- Current Password -->
            <div class="fp-input-group">
                <label for="curr_pass" class="fp-label">Current Password <span class="text-rose-400">*</span></label>
                <input type="password"
                       id="curr_pass"
                       name="current_password"
                       required
                       placeholder="••••••••"
                       class="fp-input">
            </div>

            <!-- New Password -->
            <div class="fp-input-group">
                <label for="new_pass" class="fp-label">New Password (Min 8 characters) <span class="text-rose-400">*</span></label>
                <input type="password"
                       id="new_pass"
                       name="new_password"
                       required
                       minlength="8"
                       placeholder="••••••••"
                       class="fp-input">
            </div>

            <!-- Confirm Password -->
            <div class="fp-input-group">
                <label for="conf_pass" class="fp-label">Confirm New Password <span class="text-rose-400">*</span></label>
                <input type="password"
                       id="conf_pass"
                       name="confirm_password"
                       required
                       placeholder="••••••••"
                       class="fp-input">
            </div>

            <div class="pt-2">
                <button type="submit" class="fp-btn fp-btn-secondary fp-btn-md">
                    <i class="bi bi-key"></i> Change Password
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone Card -->
    <div class="fp-card p-6 lg:p-8 border-rose-500/30 shadow-[0_0_24px_rgba(244,63,94,0.1)]">
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-rose-500/20">
            <div class="w-10 h-10 rounded-xl bg-rose-500/15 text-rose-400 flex items-center justify-center border border-rose-500/30 flex-shrink-0">
                <i class="bi bi-exclamation-octagon text-xl"></i>
            </div>
            <div>
                <h3 class="fp-font-display text-base font-bold text-rose-400">Danger Zone</h3>
                <p class="text-xs text-white/50">Irreversible account purge actions</p>
            </div>
        </div>

        <p class="text-xs text-white/60 mb-5 leading-relaxed">
            Permanently purges all transactions, category budgets, savings milestones, recurring obligations, and statement import histories.
            Your user login and profile will remain active.
        </p>

        <form method="POST" action="<?= url('settings/reset') ?>"
              onsubmit="return confirm('⚠️ CRITICAL WARNING: This will permanently purge ALL your financial records and transactions. This cannot be undone. Are you absolutely certain?')">
            <?= csrfField() ?>
            <button type="submit" class="fp-btn fp-btn-danger fp-btn-md">
                <i class="bi bi-trash3-fill"></i>
                <span>Reset All Financial Data</span>
            </button>
        </form>
    </div>

</div>
