<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

$appEnv = strtolower((string) env('APP_ENV', 'local'));
$isProduction = in_array($appEnv, ['production', 'prod'], true);

return [
    // Application settings
    'app_name' => 'FinPilot',
    'app_tagline' => 'Personal Finance Decision Support Agent',
    'app_version' => '1.0.0',
    'app_url' => env('APP_URL', $isProduction ? '' : 'http://localhost:8000'),
    'timezone' => 'UTC',
    
    // Debug mode
    'debug' => $isProduction ? false : env('APP_DEBUG', true),
    
    // Session settings
    'session' => [
        'name' => 'finpilot_session',
        'lifetime' => 7200, // 2 hours
    ],
    
    // Pagination defaults
    'per_page' => 15,
    
    // Statement Upload Settings
    'uploads' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['csv', 'txt'],
        'storage_path' => dirname(__DIR__) . '/storage/imports',
    ],
    
    // Currency defaults
    'currency' => [
        'symbol' => '$',
        'code' => 'USD',
        'position' => 'before', // 'before' or 'after'
    ],
    
    // Supported Currencies
    'currencies' => [
        '$' => 'USD / CAD / AUD ($)',
        '₹' => 'Indian Rupee (₹)',
        '€' => 'Euro (€)',
        '£' => 'British Pound (£)',
        '¥' => 'Japanese Yen (¥)',
    ],
    
    // Date formats
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',
    'display_date_format' => 'M d, Y',
];
