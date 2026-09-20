<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    /**
     * Ensure PHP session is active
     */
    private static function ensureStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /* ---------- FLASH ---------- */

    public static function setFlash(string $type, string $message): void
    {
        self::ensureStarted();
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        self::ensureStarted();
        $msg = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $msg;
    }

    public static function getAllFlash(): array
    {
        self::ensureStarted();
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }

    public static function hasFlash(string $type): bool
    {
        self::ensureStarted();
        return isset($_SESSION['_flash'][$type]);
    }

    /* ---------- AUTH ---------- */

    public static function login(array $user): void
    {
        self::ensureStarted();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['logged_in'] = true;
    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::ensureStarted();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::ensureStarted();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        self::ensureStarted();
        return $_SESSION['user_id'] ?? null;
    }

    /* ---------- CSRF ---------- */

    public static function csrf(): string
    {
        self::ensureStarted();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrf(string $token): bool
    {
        self::ensureStarted();
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }
}
