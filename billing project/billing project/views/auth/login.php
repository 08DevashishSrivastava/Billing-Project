<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In — FinPilot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to FinPilot — Your Personal Finance Decision Support Agent">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/finpilot.css') ?>">
    <style>
        /* Suppress browser-native password reveal buttons (Edge, IE, Chromium) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            pointer-events: none !important;
        }
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            visibility: hidden !important;
            display: none !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-[#0B0F19]">

    <!-- Ambient Glow Orbs -->
    <div class="fixed -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="fixed -bottom-32 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="fp-card max-w-md w-full p-8 relative z-10 my-8">
        <!-- Logo & Branding -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3 shadow-[0_0_24px_rgba(99,102,241,0.4)] border border-white/20">
                <i class="bi bi-graph-up-arrow text-2xl text-white"></i>
            </div>
            <h1 class="fp-font-display text-2xl font-bold text-white tracking-tight">FinPilot</h1>
            <p class="text-xs text-white/50 mt-1">Autonomous Decision Support & Personal Finance</p>
        </div>

        <!-- Flash Notifications -->
        <?php if (\App\Core\Session::hasFlash('error')): ?>
            <div class="fp-alert fp-alert-danger mb-5">
                <i class="bi bi-exclamation-circle-fill text-rose-400 text-base flex-shrink-0"></i>
                <span class="text-sm font-medium"><?= e(\App\Core\Session::getFlash('error')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::hasFlash('success')): ?>
            <div class="fp-alert fp-alert-success mb-5">
                <i class="bi bi-check-circle-fill text-emerald-400 text-base flex-shrink-0"></i>
                <span class="text-sm font-medium"><?= e(\App\Core\Session::getFlash('success')) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="<?= url('login') ?>" class="space-y-4">
            <?= csrfField() ?>

            <!-- Email -->
            <div class="fp-input-group">
                <label for="email" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-envelope text-indigo-400"></i> Email Address
                </label>
                <div class="relative">
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="demo@finpilot.app"
                           value="<?= e(old('email', '')) ?>"
                           required
                           autocomplete="email"
                           class="fp-input">
                </div>
            </div>

            <!-- Password -->
            <div class="fp-input-group">
                <label for="password" class="fp-label flex items-center gap-1.5">
                    <i class="bi bi-lock text-indigo-400"></i> Password
                </label>
                <div class="relative flex items-center">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password"
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

            <!-- Options -->
            <div class="flex items-center justify-between text-xs text-white/60 pt-1">
                <label class="flex items-center gap-2 cursor-pointer hover:text-white transition">
                    <input type="checkbox" name="remember" class="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-0">
                    <span>Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="fp-btn fp-btn-primary fp-btn-lg w-full mt-2">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Sign In to FinPilot</span>
            </button>
        </form>

        <!-- Demo Account Shortcut -->
        <div class="mt-6 p-3.5 rounded-xl bg-white/[0.03] border border-white/10 text-xs text-white/60">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-medium text-indigo-300 flex items-center gap-1.5">
                    <i class="bi bi-key-fill text-indigo-400"></i> Demo Credentials
                </span>
                <button type="button"
                        onclick="fillDemoAccount()"
                        class="text-[11px] text-white/50 hover:text-indigo-300 underline transition">
                    Auto-Fill
                </button>
            </div>
            <div class="flex items-center justify-between font-mono text-[11px] text-white/40 fp-nums">
                <span>demo@finpilot.app</span>
                <span>finpilot123</span>
            </div>
        </div>

        <!-- Footer Link -->
        <p class="text-center text-xs text-white/50 mt-6">
            Don't have an account?
            <a href="<?= url('register') ?>" class="text-indigo-400 hover:text-indigo-300 font-semibold transition ml-1">
                Create one free →
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

    function fillDemoAccount() {
        document.getElementById('email').value = 'demo@finpilot.app';
        document.getElementById('password').value = 'finpilot123';
    }
    </script>
</body>
</html>
