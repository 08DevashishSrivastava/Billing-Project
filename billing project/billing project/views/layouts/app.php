<?php
use App\Core\Session;

// Redirect to login if not authenticated
if (!Session::isLoggedIn()) {
    header('Location: ' . url('login'));
    exit;
}

$user = Session::user();
$pageTitle = $title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | FinPilot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?= asset('css/finpilot.css') ?>">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-neutral: #64748b;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .sidebar {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease;
        }

        #main-content-wrapper {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar-link {
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-link.active {
            border-left: 3px solid var(--primary);
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, transparent 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        .table-glass {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .table-glass th {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .table-glass tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .table-glass td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Toast Notifications */
        .toast {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Fade animations */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Modal */
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(30, 30, 50, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Status badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-income  { background: rgba(16,185,129,0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
        .badge-expense { background: rgba(239,68,68,0.2);  color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
        .badge-neutral { background: rgba(100,116,139,0.2); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }
        .badge-success { background: rgba(16,185,129,0.2); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
        .badge-warning { background: rgba(245,158,11,0.2); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
        .badge-info    { background: rgba(99,102,241,0.2); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); }
        
        /* Desktop Sidebar Collapsed State */
        @media (min-width: 1024px) {
            body.sidebar-collapsed .sidebar {
                width: 5rem !important; /* 80px */
            }
            body.sidebar-collapsed #main-content-wrapper {
                margin-left: 5rem !important;
            }
            body.sidebar-collapsed .sidebar .sidebar-text {
                display: none !important;
            }
            body.sidebar-collapsed .sidebar .sidebar-header {
                padding: 0.75rem 0.5rem !important;
                flex-direction: column !important;
                gap: 0.625rem !important;
                align-items: center !important;
            }
            body.sidebar-collapsed .sidebar .sidebar-brand {
                justify-content: center !important;
                width: 100% !important;
            }
            body.sidebar-collapsed .sidebar .sidebar-toggle-btn {
                align-self: flex-end !important;
                width: 1.5rem !important;
                height: 1.5rem !important;
            }
            body.sidebar-collapsed .sidebar nav ul {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            body.sidebar-collapsed .sidebar .sidebar-link {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            body.sidebar-collapsed .sidebar .quick-add-container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            body.sidebar-collapsed .sidebar .quick-add-btn {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            body.sidebar-collapsed .sidebar .user-section {
                padding: 0.75rem 0.25rem !important;
            }
            body.sidebar-collapsed .sidebar .user-container {
                flex-direction: column !important;
                gap: 0.5rem !important;
                justify-content: center !important;
                align-items: center !important;
                padding: 0 !important;
            }
        }

        /* Responsive sidebar toggle */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
        
        /* Select Dropdown Fix */
        select, select option {
            background-color: #1e293b !important;
            color: #e5e7eb !important;
        }
        select:focus {
            outline: none;
            border-color: #6366f1;
        }
    </style>
</head>
<body class="text-white">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php require __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div id="main-content-wrapper" class="flex-1 flex flex-col lg:ml-64">
            <!-- Header -->
            <?php require __DIR__ . '/header.php'; ?>
            
            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-8 fade-in">
                <?= $content ?>
            </main>
            
            <!-- Footer -->
            <?php require __DIR__ . '/footer.php'; ?>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>
    
    <!-- Scripts -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= asset('js/' . $script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Show flash messages as toasts
        <?php if ($success = Session::getFlash('success')): ?>
            showToast('<?= e($success) ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error = Session::getFlash('error')): ?>
            showToast('<?= e($error) ?>', 'error');
        <?php endif; ?>
        
        <?php if ($info = Session::getFlash('info')): ?>
            showToast('<?= e($info) ?>', 'info');
        <?php endif; ?>
    </script>
</body>
</html>
