<?php
/**
 * Simple Environment Loader
 * Loads variables from .env file into $_ENV and getenv()
 */

// Placeholder values that should be treated as empty
define('ENV_PLACEHOLDERS', [
    'your_user',
    'your_password', 
    'your_username',
    'your_host',
    'your_database',
    'placeholder',
    'changeme',
    'CHANGEME',
    'xxx',
    'XXX',
]);

/**
 * Check if a value is a placeholder
 */
function isPlaceholder(string $value): bool
{
    $value = trim($value);
    
    // Empty is placeholder
    if ($value === '') {
        return true;
    }
    
    // Check against known placeholders
    if (in_array(strtolower($value), array_map('strtolower', ENV_PLACEHOLDERS))) {
        return true;
    }
    
    // Values starting with 'your_' are placeholders
    if (str_starts_with(strtolower($value), 'your_')) {
        return true;
    }
    
    return false;
}

/**
 * Load environment variables from .env file
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    
    foreach ($lines as $line) {
        // Trim whitespace
        $line = trim($line);
        
        // Skip empty lines and comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Parse KEY=value
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Trim again after quote removal
            $value = trim($value);

            // Skip placeholder values - treat as not set
            if (isPlaceholder($value)) {
                continue;
            }

            // Only set if not already defined (system env takes precedence)
            if (!isset($_ENV[$key]) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

/**
 * Get environment variable with optional default
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false || $value === '') {
        return $default;
    }
    
    // Double-check for placeholders at retrieval time
    if (is_string($value) && isPlaceholder($value)) {
        return $default;
    }

    // Handle boolean strings
    return match (strtolower((string)$value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => $value,
    };
}

// Auto-load .env from project root (gracefully handle missing file)
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);
