-- FinPilot Database Schema
-- Run: mysql -u root -p finpilot_db < database/schema.sql

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS statement_imports;
DROP TABLE IF EXISTS recurring_obligations;
DROP TABLE IF EXISTS goals;
DROP TABLE IF EXISTS budgets;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS users;

-- Legacy billing tables (removed)
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS settings;

SET FOREIGN_KEY_CHECKS = 1;

-- ── Users ─────────────────────────────────────────────────────────────────────
CREATE TABLE users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    currency     VARCHAR(5)   NOT NULL DEFAULT '$',
    last_login   DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Accounts (checking, savings, credit card, wallet, etc.) ──────────────────
CREATE TABLE accounts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    type        ENUM('checking','savings','credit_card','cash','investment','other') NOT NULL DEFAULT 'checking',
    balance     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    institution VARCHAR(100) NULL,
    color       VARCHAR(7)   NOT NULL DEFAULT '#6366f1',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_accounts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Categories ────────────────────────────────────────────────────────────────
CREATE TABLE categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL COMMENT 'NULL = system default',
    name       VARCHAR(80)  NOT NULL,
    type       ENUM('expense','income','both') NOT NULL DEFAULT 'expense',
    icon       VARCHAR(40)  NOT NULL DEFAULT 'bi-tag',
    color      VARCHAR(7)   NOT NULL DEFAULT '#8b5cf6',
    is_default TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Transactions ──────────────────────────────────────────────────────────────
CREATE TABLE transactions (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    account_id       INT UNSIGNED NOT NULL,
    category_id      INT UNSIGNED NULL,
    transaction_date DATE         NOT NULL,
    amount           DECIMAL(15,2) NOT NULL,
    type             ENUM('income','expense') NOT NULL,
    description      VARCHAR(200) NOT NULL DEFAULT '',
    merchant         VARCHAR(100) NULL,
    notes            TEXT         NULL,
    source           ENUM('manual','csv_import') NOT NULL DEFAULT 'manual',
    import_hash      VARCHAR(64)  NULL COMMENT 'SHA256 for duplicate detection',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_transactions_account  FOREIGN KEY (account_id)  REFERENCES accounts(id)   ON DELETE CASCADE,
    CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_transactions_date    (transaction_date),
    INDEX idx_transactions_user    (user_id),
    INDEX idx_transactions_account (account_id),
    UNIQUE KEY ux_import_hash (import_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Budgets ───────────────────────────────────────────────────────────────────
CREATE TABLE budgets (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    category_id  INT UNSIGNED NOT NULL,
    amount       DECIMAL(15,2) NOT NULL,
    period_month CHAR(7)      NOT NULL COMMENT 'YYYY-MM',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_budgets_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_budgets_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY ux_budget_user_category_month (user_id, category_id, period_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Goals ─────────────────────────────────────────────────────────────────────
CREATE TABLE goals (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    name           VARCHAR(100) NOT NULL,
    target_amount  DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    target_date    DATE         NULL,
    status         ENUM('active','achieved','paused') NOT NULL DEFAULT 'active',
    notes          TEXT         NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Recurring Obligations (bills & subscriptions) ─────────────────────────────
CREATE TABLE recurring_obligations (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    category_id      INT UNSIGNED NULL,
    merchant         VARCHAR(100) NOT NULL,
    expected_amount  DECIMAL(15,2) NOT NULL,
    frequency        ENUM('weekly','monthly','yearly') NOT NULL DEFAULT 'monthly',
    is_subscription  TINYINT(1)   NOT NULL DEFAULT 0,
    last_payment_date DATE        NULL,
    next_due_date    DATE         NULL,
    status           ENUM('active','paused','cancelled') NOT NULL DEFAULT 'active',
    confidence_score DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recurring_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_recurring_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Statement Imports ─────────────────────────────────────────────────────────
CREATE TABLE statement_imports (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    account_id     INT UNSIGNED NOT NULL,
    filename       VARCHAR(200) NOT NULL,
    total_rows     INT UNSIGNED NOT NULL DEFAULT 0,
    imported_rows  INT UNSIGNED NOT NULL DEFAULT 0,
    duplicate_rows INT UNSIGNED NOT NULL DEFAULT 0,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imports_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_imports_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
