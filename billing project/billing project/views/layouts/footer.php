<footer class="glass border-t border-white/10 px-4 lg:px-8 py-4 mt-auto">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40">
        <p>&copy; <?= date('Y') ?> FinPilot &mdash; Personal Finance Decision Support Agent</p>
        <div class="flex items-center gap-4">
            <a href="<?= url('agent') ?>" class="hover:text-indigo-400 transition flex items-center gap-1"><i class="bi bi-robot"></i> AI Agent</a>
            <a href="<?= url('settings') ?>" class="hover:text-white transition">Settings</a>
            <span class="text-white/20">v1.0.0</span>
        </div>
    </div>
</footer>
