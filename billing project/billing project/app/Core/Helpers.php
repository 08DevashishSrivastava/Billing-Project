<?php
declare(strict_types=1);

use App\Core\Session;

/**
 * Escape HTML entities
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get asset URL (public/assets)
 */
function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

/**
 * Get URL (route path)
 * Safe for PHP built-in server with -t public
 */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/**
 * Check if current URL matches
 */
function isActive(string $path): bool
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $checkPath = url($path);
    return $currentPath === $checkPath || str_starts_with($currentPath, $checkPath . '/');
}

/**
 * Format currency
 */
function formatCurrency(float $amount, string $currency = '$'): string
{
    return $currency . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime(?string $date, string $format = 'M d, Y H:i'): string
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * CSRF hidden input
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . Session::csrf() . '">';
}

/**
 * Method spoofing hidden input
 */
function methodField(string $method): string
{
    return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
}

/**
 * Dump and die
 */
function dd(mixed ...$vars): never
{
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    exit;
}

/**
 * Old form input
 */
function old(string $key, mixed $default = ''): mixed
{
    return Session::get('_old_input')[$key] ?? $default;
}

/**
 * Get user preference (currency, etc.) from session or config default
 */
function userCurrency(): string
{
    return \App\Core\Session::get('user')['currency'] ?? '$';
}

/**
 * Transaction type badge CSS class
 */
function transactionTypeBadge(string $type): string
{
    return match ($type) {
        'income'  => 'badge-income',
        'expense' => 'badge-expense',
        default   => 'badge-neutral',
    };
}

/**
 * Budget utilization color (green < 75%, yellow < 90%, red >= 90%)
 */
function budgetProgressColor(float $used, float $budget): string
{
    if ($budget <= 0) return 'var(--color-neutral)';
    $pct = ($used / $budget) * 100;
    if ($pct >= 90) return 'var(--color-danger)';
    if ($pct >= 75) return 'var(--color-warning)';
    return 'var(--color-success)';
}

/**
 * Goal status badge class
 */
function goalStatusBadge(string $status): string
{
    return match ($status) {
        'achieved' => 'badge-success',
        'paused'   => 'badge-warning',
        default    => 'badge-info',
    };
}

/**
 * Format signed amount with + or - prefix and currency symbol
 */
function formatAmountSigned(float $amount, string $type, string $currency = '$'): string
{
    $formatted = $currency . number_format(abs($amount), 2);
    return $type === 'income' ? '+' . $formatted : '-' . $formatted;
}

/**
 * Human-readable recurring frequency label
 */
function recurringFrequencyLabel(string $frequency): string
{
    return match ($frequency) {
        'weekly'  => 'Weekly',
        'monthly' => 'Monthly',
        'yearly'  => 'Yearly',
        default   => ucfirst($frequency),
    };
}

/**
 * Days until a future date (negative if past)
 */
function daysUntil(string $date): int
{
    $target = new DateTime($date);
    $today  = new DateTime('today');
    $diff   = (int) $today->diff($target)->days;
    return $target >= $today ? $diff : -$diff;
}

/**
 * Text truncate
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

/**
 * Random string
 */
function randomString(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * AJAX check
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Client IP
 */
function getClientIp(): string
{
    foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) return $_SERVER[$key];
    }
    return 'UNKNOWN';
}
