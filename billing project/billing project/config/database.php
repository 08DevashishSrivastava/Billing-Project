<?php
declare(strict_types=1);

/**
 * Database Configuration
 * Production must use explicit environment values and must not silently fall back.
 */

require_once __DIR__ . '/env.php';

define('DB_DEFAULTS', [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'finpilot_db',
    'username' => 'root',
    'password' => '',
]);

function isLocalDbPlaceholder(string $value): bool
{
    return $value === '' || (function_exists('isPlaceholder') && isPlaceholder($value));
}

function validateDbConfig(array $config, bool $production = false): array
{
    $fallbackUsed = false;

    foreach (['host', 'database', 'username'] as $field) {
        $value = (string) ($config[$field] ?? '');

        if ($production) {
            if (isLocalDbPlaceholder($value)) {
                throw new RuntimeException('Missing or invalid production database setting: ' . $field . '. Set it in .env before deployment.');
            }
            continue;
        }

        if (isLocalDbPlaceholder($value)) {
            $config[$field] = DB_DEFAULTS[$field];
            $fallbackUsed = true;
        }
    }

    if ($production) {
        $password = (string) ($config['password'] ?? '');
        if (isLocalDbPlaceholder($password)) {
            throw new RuntimeException('Missing or invalid production database password. Set DB_PASS in .env before deployment.');
        }
    } elseif (isset($config['password']) && isLocalDbPlaceholder((string) $config['password'])) {
        $config['password'] = DB_DEFAULTS['password'];
        $fallbackUsed = true;
    }

    $config['using_fallback'] = $fallbackUsed;
    $config['fallback_warning'] = $fallbackUsed ? 'Local database fallback is active for development only.' : '';

    return $config;
}

$appEnv = strtolower((string) env('APP_ENV', 'local'));
$isProduction = in_array($appEnv, ['production', 'prod'], true);

$dbConfig = [
    'host'     => env('DB_HOST', $isProduction ? '' : DB_DEFAULTS['host']),
    'port'     => env('DB_PORT', DB_DEFAULTS['port']),
    'database' => env('DB_NAME', $isProduction ? '' : DB_DEFAULTS['database']),
    'username' => env('DB_USER', $isProduction ? '' : DB_DEFAULTS['username']),
    'password' => env('DB_PASS', $isProduction ? '' : DB_DEFAULTS['password']),
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ],
];

if ($isProduction) {
    $dbConfig = validateDbConfig($dbConfig, true);
    return $dbConfig;
}

$dbConfig = validateDbConfig($dbConfig, false);
return $dbConfig;
