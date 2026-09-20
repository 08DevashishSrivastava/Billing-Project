<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$menuItems = [
    ['url' => 'dashboard',    'icon' => 'bi-grid-1x2-fill',     'label' => 'Dashboard'],
    ['url' => 'transactions', 'icon' => 'bi-arrow-left-right',  'label' => 'Transactions'],
    ['url' => 'accounts',     'icon' => 'bi-bank',              'label' => 'Accounts'],
    ['url' => 'budgets',      'icon' => 'bi-pie-chart-fill',    'label' => 'Budgets'],
    ['url' => 'goals',        'icon' => 'bi-trophy-fill',       'label' => 'Goals'],
    ['url' => 'recurring',    'icon' => 'bi-arrow-repeat',      'label' => 'Recurring Bills'],
    ['url' => 'import',       'icon' => 'bi-cloud-upload-fill', 'label' => 'Import Statement'],
    ['url' => 'agent',        'icon' => 'bi-robot',             'label' => 'AI Decision Agent'],
    ['url' => 'reports',      'icon' => 'bi-bar-chart-line-fill','label' => 'Reports'],
    ['url' => 'settings',     'icon' => 'bi-gear-fill',         'label' => 'Settings'],
];

if (!function_exists('isMenuActive')) {
    function isMenuActive(string $url): bool {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $menuPath = url($url);
        return $currentPath === $menuPath || str_starts_with($currentPath, $menuPath . '/');
    }
}
?>
<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 flex flex-col z-50 transition-transform duration-300">
    <!-- Header: Logo & Collapse Toggle -->
    <div class="sidebar-header p-4 lg:p-5 border-b border-white/10 flex items-center justify-between relative min-h-[73px]">
        <a href="<?= url('dashboard') ?>" class="flex items-center gap-3 sidebar-brand overflow-hidden" title="FinPilot">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="bi bi-graph-up-arrow text-lg text-white"></i>
            </div>
            <div class="sidebar-text whitespace-nowrap">
                <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent leading-tight">
                    FinPilot
                </h1>
                <p class="text-xs text-white/50">Personal Finance</p>
            </div>
        </a>

        <!-- Desktop Collapse Toggle Button (Top-Right Corner) -->
        <button id="sidebar-collapse-btn"
                type="button"
                onclick="toggleSidebarCollapse()"
                class="sidebar-toggle-btn hidden lg:flex items-center justify-center w-7 h-7 rounded-lg bg-white/5 hover:bg-white/15 text-white/70 hover:text-white border border-white/10 transition-all flex-shrink-0 cursor-pointer"
                title="Toggle sidebar collapse"
                aria-label="Toggle sidebar collapse">
            <i id="sidebar-collapse-icon" class="bi bi-chevron-left text-xs"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-0.5 px-3">
            <?php foreach ($menuItems as $item): ?>
                <?php $isActive = isMenuActive($item['url']); ?>
                <li>
                    <a
                        href="<?= url($item['url']) ?>"
                        class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-white/70 hover:text-white <?= $isActive ? 'active text-white' : '' ?>"
                        title="<?= e($item['label']) ?>"
                    >
                        <i class="<?= $item['icon'] ?> text-base w-5 text-center flex-shrink-0 <?= $isActive ? 'text-indigo-400' : '' ?>"></i>
                        <span class="text-sm font-medium sidebar-text whitespace-nowrap"><?= $item['label'] ?></span>
                        <?php if ($item['url'] === 'agent'): ?>
                            <span class="sidebar-text ml-auto text-xs bg-indigo-500/30 text-indigo-300 px-1.5 py-0.5 rounded-full font-semibold">AI</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Quick Action -->
        <div class="quick-add-container px-3 mt-6">
            <p class="sidebar-text px-4 text-xs font-semibold text-white/30 uppercase tracking-wider mb-2">Quick Add</p>
            <a
                href="<?= url('transactions/create') ?>"
                class="quick-add-btn flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 text-white hover:from-indigo-500/30 hover:to-purple-500/30 transition-all"
                title="Add Transaction"
            >
                <i class="bi bi-plus-circle-fill text-base text-indigo-400 flex-shrink-0"></i>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Add Transaction</span>
            </a>
        </div>
    </nav>

    <!-- User Section -->
    <div class="user-section p-4 border-t border-white/10">
        <div class="user-container flex items-center gap-3 px-3 py-2">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0" title="<?= e($user['name'] ?? 'User') ?>">
                <span class="text-sm font-bold text-white"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= e($user['name'] ?? 'User') ?></p>
                <p class="text-xs text-white/40 truncate"><?= e($user['email'] ?? '') ?></p>
            </div>
            <a
                href="<?= url('logout') ?>"
                class="logout-btn p-1.5 rounded-lg hover:bg-white/10 text-white/50 hover:text-red-400 transition flex items-center justify-center"
                title="Sign Out"
                aria-label="Sign Out"
            >
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>

<script>
(function() {
    try {
        const isCollapsed = localStorage.getItem('finpilot_sidebar_collapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 1024) {
            document.body.classList.add('sidebar-collapsed');
            const icon = document.getElementById('sidebar-collapse-icon');
            if (icon) {
                icon.className = 'bi bi-chevron-right text-xs';
            }
        }
    } catch(e) {}
})();

function toggleSidebarCollapse() {
    const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
    try {
        localStorage.setItem('finpilot_sidebar_collapsed', isCollapsed ? 'true' : 'false');
    } catch(e) {}
    
    const icon = document.getElementById('sidebar-collapse-icon');
    if (icon) {
        icon.className = isCollapsed ? 'bi bi-chevron-right text-xs' : 'bi bi-chevron-left text-xs';
    }

    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 310);
}
</script>
