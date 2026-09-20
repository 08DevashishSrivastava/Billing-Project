<?php
declare(strict_types=1);

// Production: suppress error display; log errors instead
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len     = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) return;

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) require $file;
});

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/app/Core/Helpers.php';
$config = require BASE_PATH . '/config/config.php';

date_default_timezone_set($config['timezone']);

session_name($config['session']['name']);
session_start();

use App\Core\Router;
use App\Core\Session;
use App\Core\Database;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TransactionController;
use App\Controllers\AccountController;
use App\Controllers\BudgetController;
use App\Controllers\GoalController;
use App\Controllers\RecurringController;
use App\Controllers\ImportController;
use App\Controllers\AgentController;
use App\Controllers\ReportController;
use App\Controllers\SettingController;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ── Health Check ─────────────────────────────────────────────────────────────
if ($requestUri === '/health/db') {
    header('Content-Type: application/json');
    echo json_encode(Database::testConnection(), JSON_PRETTY_PRINT);
    exit;
}

// ── CSRF Protection ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf_token'] ?? '';
    if (!Session::verifyCsrf($token) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
}

$router = new Router();

// ── AUTH ──────────────────────────────────────────────────────────────────────
$router->get('/login',    [AuthController::class, 'loginForm']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/logout',   [AuthController::class, 'logout']);

// ── DASHBOARD ─────────────────────────────────────────────────────────────────
$router->get('/',          [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// ── TRANSACTIONS ──────────────────────────────────────────────────────────────
$router->get('/transactions',              [TransactionController::class, 'index']);
$router->get('/transactions/create',       [TransactionController::class, 'create']);
$router->post('/transactions/store',       [TransactionController::class, 'store']);
$router->get('/transactions/{id}/edit',    [TransactionController::class, 'edit']);
$router->post('/transactions/{id}/update', [TransactionController::class, 'update']);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'delete']);

// ── ACCOUNTS ──────────────────────────────────────────────────────────────────
$router->get('/accounts',              [AccountController::class, 'index']);
$router->post('/accounts/store',       [AccountController::class, 'store']);
$router->post('/accounts/{id}/update', [AccountController::class, 'update']);
$router->post('/accounts/{id}/delete', [AccountController::class, 'delete']);

// ── BUDGETS ───────────────────────────────────────────────────────────────────
$router->get('/budgets',               [BudgetController::class, 'index']);
$router->post('/budgets/store',        [BudgetController::class, 'store']);
$router->post('/budgets/{id}/update',  [BudgetController::class, 'update']);
$router->post('/budgets/{id}/delete',  [BudgetController::class, 'delete']);

// ── GOALS ─────────────────────────────────────────────────────────────────────
$router->get('/goals',               [GoalController::class, 'index']);
$router->post('/goals/store',        [GoalController::class, 'store']);
$router->post('/goals/{id}/update',  [GoalController::class, 'update']);
$router->post('/goals/{id}/delete',  [GoalController::class, 'delete']);
$router->post('/goals/{id}/deposit', [GoalController::class, 'deposit']);

// ── RECURRING OBLIGATIONS ─────────────────────────────────────────────────────
$router->get('/recurring',               [RecurringController::class, 'index']);
$router->post('/recurring/store',        [RecurringController::class, 'store']);
$router->post('/recurring/{id}/update',  [RecurringController::class, 'update']);
$router->post('/recurring/{id}/delete',  [RecurringController::class, 'delete']);

// ── STATEMENT IMPORT ──────────────────────────────────────────────────────────
$router->get('/import',           [ImportController::class, 'index']);
$router->post('/import/upload',   [ImportController::class, 'upload']);
$router->post('/import/confirm',  [ImportController::class, 'confirm']);

// ── AI DECISION AGENT ─────────────────────────────────────────────────────────
$router->get('/agent',        [AgentController::class, 'index']);
$router->post('/agent/query', [AgentController::class, 'query']);

// ── REPORTS ───────────────────────────────────────────────────────────────────
$router->get('/reports',         [ReportController::class, 'index']);
$router->get('/reports/export',  [ReportController::class, 'export']);

// ── SETTINGS ──────────────────────────────────────────────────────────────────
$router->get('/settings',         [SettingController::class, 'index']);
$router->post('/settings/update', [SettingController::class, 'update']);
$router->post('/settings/reset',  [SettingController::class, 'resetData']);


$router->resolve();
