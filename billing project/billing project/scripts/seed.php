<?php
declare(strict_types=1);

/**
 * FinPilot CLI Seeder
 * Usage: php scripts/seed.php [--fresh]
 *
 * --fresh  Drop and recreate all tables before seeding
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/app/Core/Helpers.php';
$config = require BASE_PATH . '/config/config.php';
$dbCfg  = require BASE_PATH . '/config/database.php';

$port = $dbCfg['port'] ?? '3306';
$dsnRoot = "mysql:host={$dbCfg['host']};port={$port};charset=utf8mb4";
$pdo = new PDO($dsnRoot, $dbCfg['username'], $dbCfg['password'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbCfg['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbCfg['database']}`");

$fresh = in_array('--fresh', $argv ?? [], true);

echo "\n🚀 FinPilot Seeder\n";
echo str_repeat('─', 40) . "\n";

if ($fresh) {
    echo "⚡ Running schema.sql (--fresh)...\n";
    $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
        if ($stmt) $pdo->exec($stmt . ';');
    }
    echo "✅ Schema created.\n";
}

echo "🌱 Running seed.sql...\n";
$seed = file_get_contents(BASE_PATH . '/database/seed.sql');
foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
    if ($stmt) $pdo->exec($stmt . ';');
}

echo "✅ Seed complete!\n\n";
echo "┌──────────────────────────────────────┐\n";
echo "│  Demo Credentials                    │\n";
echo "│  Email:    demo@finpilot.app         │\n";
echo "│  Password: finpilot123               │\n";
echo "└──────────────────────────────────────┘\n\n";
