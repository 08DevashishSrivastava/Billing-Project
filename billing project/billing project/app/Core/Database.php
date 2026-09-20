<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private static bool $connectionFailed = false;
    private static string $lastError = '';
    private static array $config = [];

    private function __construct()
    {
        self::$config = require dirname(__DIR__, 2) . '/config/database.php';
        $this->connect();
    }

    /**
     * Create PDO connection
     */
    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            self::$config['host'],
            self::$config['port'],
            self::$config['database'],
            self::$config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false,
                ]
            );

            self::$connectionFailed = false;
            self::$lastError = '';

        } catch (PDOException $e) {
            self::$connectionFailed = true;
            self::$lastError = $e->getMessage();
            $this->pdo = null;
        }
    }

    /**
     * Singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Connection status
     */
    public static function isConnected(): bool
    {
        return self::getInstance()->pdo !== null && !self::$connectionFailed;
    }

    public static function hasFailed(): bool
    {
        return self::$connectionFailed;
    }

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    public static function getConfig(): array
    {
        return self::$config;
    }

    public static function isUsingFallback(): bool
    {
        return self::$config['using_fallback'] ?? false;
    }

    public static function getFallbackWarning(): string
    {
        return self::$config['fallback_warning'] ?? '';
    }

    /**
     * Health check helper
     */
    public static function testConnection(): array
    {
        return [
            'connected' => self::isConnected(),
            'error' => self::$lastError,
            'using_fallback' => self::isUsingFallback(),
            'fallback_warning' => self::getFallbackWarning(),
            'config' => [
                'host' => self::$config['host'] ?? '',
                'port' => self::$config['port'] ?? '',
                'database' => self::$config['database'] ?? '',
                'username' => self::$config['username'] ?? '',
            ],
        ];
    }

    /**
     * Ensure PDO exists
     */
    private function requireConnection(): void
    {
        if ($this->pdo === null) {
            throw new PDOException('Database not connected: ' . self::$lastError);
        }
    }

    public function getConnection(): PDO
    {
        $this->requireConnection();
        return $this->pdo;
    }

    /**
     * Run prepared query
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $this->requireConnection();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Fetch single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Insert helper
     */
    public function insert(string $table, array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update helper
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, [...array_values($data), ...$whereParams]);

        return $stmt->rowCount();
    }

    /**
     * Delete helper
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $stmt = $this->query("DELETE FROM {$table} WHERE {$where}", $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): int
    {
        $this->requireConnection();
        return (int)$this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        $this->requireConnection();
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo?->commit() ?? false;
    }

    public function rollback(): bool
    {
        return $this->pdo?->rollBack() ?? false;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
