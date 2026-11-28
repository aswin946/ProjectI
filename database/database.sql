-- =====================================================================
-- database.sql  -  Personal Expense Management System (PEMS)
-- BCA Project I, Tribhuvan University
--
-- Tables and columns follow Section 7.3 of the approved proposal:
--   users, categories, transactions, budgets
--
-- HOW TO USE: phpMyAdmin -> Import -> choose this file -> Go.
-- Safe to run more than once (it does not delete existing data).
-- To start completely fresh, drop the pems_db database in phpMyAdmin first.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS pems_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pems_db;

-- ---------------------------------------------------------------------
-- users: one row per registered person
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,          -- result of password_hash(), never the real password
    status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email)              -- no two accounts with the same email
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- categories: each category belongs to one user
-- (default categories are copied for each new user at registration, Phase 2)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    category_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED NOT NULL,
    category_name  VARCHAR(50) NOT NULL,
    category_type  ENUM('income','expense') NOT NULL DEFAULT 'expense',
    status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (category_id),
    UNIQUE KEY uq_category_per_user (user_id, category_name, category_type),
    CONSTRAINT fk_categories_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE                          -- see note at the end of this file about deleting users
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- transactions: every income or expense record
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id           INT UNSIGNED NOT NULL,
    category_id       INT UNSIGNED NOT NULL,
    amount            DECIMAL(12,2) NOT NULL,
    type              ENUM('income','expense') NOT NULL,
    description       VARCHAR(255) NOT NULL DEFAULT '',
    transaction_date  DATE NOT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    KEY idx_tx_user_date (user_id, transaction_date),   -- speeds up lists, filters and reports
    KEY idx_tx_user_type (user_id, type),
    KEY idx_tx_category (category_id),
    CONSTRAINT chk_tx_amount CHECK (amount > 0),
    CONSTRAINT fk_tx_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tx_category
        FOREIGN KEY (category_id) REFERENCES categories (category_id)
        ON DELETE RESTRICT                          -- a category that has transactions cannot be deleted
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- budgets: a monthly budget amount for one category
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS budgets (
    budget_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED NOT NULL,
    category_id    INT UNSIGNED NOT NULL,
    budget_amount  DECIMAL(12,2) NOT NULL,
    `month`        TINYINT UNSIGNED NOT NULL,       -- 1 to 12
    `year`         SMALLINT UNSIGNED NOT NULL,      -- for example 2026
    PRIMARY KEY (budget_id),
    UNIQUE KEY uq_budget_period (user_id, category_id, `month`, `year`),  -- one budget per category per month
    KEY idx_budget_category (category_id),
    CONSTRAINT chk_budget_amount CHECK (budget_amount > 0),
    CONSTRAINT chk_budget_month  CHECK (`month` BETWEEN 1 AND 12),
    CONSTRAINT fk_budget_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_budget_category
        FOREIGN KEY (category_id) REFERENCES categories (category_id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTE ABOUT DELETING A USER: categories, transactions and budgets reference the user with
-- ON DELETE CASCADE, but transactions and budgets reference categories with ON DELETE RESTRICT
-- (a category in use must never disappear silently). Because of this, a user cannot be deleted
-- with a single DELETE. Delete the user's transactions, budgets and categories first
-- (see database/remove_demo_data.sql for the correct order). The application has no "delete account" feature.
--
-- NOTE: No default categories are inserted here on purpose.
-- In the proposal, categories.user_id is a foreign key, so every category belongs to a user.
-- The default categories will be created for each user when they register (Phase 2).
