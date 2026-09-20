<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account — FinPilot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your free FinPilot account to start tracking personal finances.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/finpilot.css') ?>">
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-[#0B0F19]">

    <!-- Ambient Glow Orbs -->
    <div class="fixed -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="fixed -bottom-32 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="fp-card max-w-md w-full p-8 relative z-10 my-8">
        <!-- Logo & Branding -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3 shadow-[0_0_24px_rgba(99,102,241,0.4)] border border-white/20">
                <i class="bi bi-person-plus-fill text-2xl text-white"></i>
            </div>
            <h1 class="fp-font-display text-2xl font-bold text-white tracking-tight">Create Account</h1>
            <p class="text-xs text-white/50 mt-1">Start tracking and automating your personal finances</p>
        </div>

        <!-- Flash Notifications -->
        <?php if (\App\Core\Session::hasFlash('error')): ?>
            <div class="fp-alert fp-alert-danger mb-5">
                <i class="bi bi-exclamation-circle-fill text-rose-400 text-base flex-shrink-0"></i>
                <span class="text-sm font-medium"><?= e(\App\Core\Session::getFlash('error')) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="<?= url('register') ?>" class="space-y-4">
            <?= csrfField() ?>

            <!-- Full Name -->
            <div class="fp-input-group">
                <label for="name" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-person text-indigo-400"></i> Full Name <span class="text-rose-400">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       placeholder="Alex Sharma"
                       value="<?= e(old('name', '')) ?>"
                       required
                       autocomplete="name"
                       class="fp-input">
            </div>

            <!-- Email -->
            <div class="fp-input-group">
                <label for="email" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-envelope text-indigo-400"></i> Email Address <span class="text-rose-400">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       placeholder="alex@example.com"
                       value="<?= e(old('email', '')) ?>"
                       required
                       autocomplete="email"
                       class="fp-input">
            </div>

            <!-- Password -->
            <div class="fp-input-group">
                <label for="password" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-lock text-indigo-400"></i> Password <span class="text-rose-400">*</span>
                </label>
                <div class="relative flex items-center">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="At least 8 characters"
                           required
                           minlength="8"
                           autocomplete="new-password"
                           class="fp-input pr-10">
                    <button type="button"
                            id="password-toggle-btn"
                            onclick="togglePasswordVisibility('password', this)"
                            class="absolute right-3 text-white hover:text-white/80 transition flex items-center justify-center focus:outline-none cursor-pointer"
                            aria-label="Toggle password visibility"
                            tabindex="-1">
                        <i class="bi bi-eye text-base text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Default Currency -->
            <div class="fp-input-group">
                <label for="currency" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-currency-exchange text-indigo-400"></i> Default Currency
                </label>
                <select id="currency" name="currency" class="fp-select">
                    <option value="$">$ — USD / CAD / AUD</option>
                    <option value="₹">₹ — Indian Rupee</option>
                    <option value="€">€ — Euro</option>
                    <option value="£">£ — British Pound</option>
                    <option value="¥">¥ — Japanese Yen</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="fp-btn fp-btn-primary fp-btn-lg w-full mt-2">
                <i class="bi bi-person-check-fill"></i>
                <span>Create My Account</span>
            </button>
        </form>

        <!-- Footer Link -->
        <p class="text-center text-xs text-white/50 mt-6">
            Already have an account?
            <a href="<?= url('login') ?>" class="text-indigo-400 hover:text-indigo-300 font-semibold transition ml-1">
                Sign in →
            </a>
        </p>
    </div>

    <script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash text-base text-white';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye text-base text-white';
        }
    }
    </script>
</body>
</html>
