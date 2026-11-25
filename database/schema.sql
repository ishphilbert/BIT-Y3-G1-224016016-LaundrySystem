-- ============================================================================
-- database/schema.sql  -  Online Laundry Service System
-- Requires MySQL 8.0.16+ or MariaDB 10.2+ (CHECK constraints are enforced).
-- WARNING: running this file DROPS and recreates the database.
-- ============================================================================

DROP DATABASE IF EXISTS online_laundry_db;
CREATE DATABASE online_laundry_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_laundry_db;

-- ----------------------------------------------------------------------------
-- TABLE 1: users  (authentication + roles)
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,              -- output of password_hash(), never plain text
    role          ENUM('admin','staff','customer') NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password reset tokens (only SHA-256 hashes are stored; tokens expire in one hour).
CREATE TABLE password_resets (
    reset_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expiry (expires_at)
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- TABLE 2: customers  (profile; exactly one per customer user account)
-- ----------------------------------------------------------------------------
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL UNIQUE,                  -- UNIQUE => users (1) -> customers (0 or 1)
    full_name   VARCHAR(150) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    address     TEXT NOT NULL,
    CONSTRAINT fk_customers_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE           -- deleting the account removes the profile
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- TABLE 3: orders
-- ----------------------------------------------------------------------------
CREATE TABLE orders (
    order_id          INT AUTO_INCREMENT PRIMARY KEY,
    customer_id       INT NOT NULL,
    assigned_staff_id INT NULL,                       -- a users.user_id whose role is 'staff'
    pickup_date       DATE NOT NULL,
    delivery_date     DATE NOT NULL,
    total             DECIMAL(10,2) NOT NULL,         -- SUM(quantity * unit_price), set by PHP in a transaction
    status            ENUM('pending','confirmed','picked_up','processing',
                           'ready_for_delivery','out_for_delivery','delivered','cancelled')
                      NOT NULL DEFAULT 'pending',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,          -- deleting a customer removes their orders
    CONSTRAINT fk_orders_staff
        FOREIGN KEY (assigned_staff_id) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,         -- deleting a staff user un-assigns, keeps the order

    CONSTRAINT chk_orders_dates CHECK (delivery_date > pickup_date),
    CONSTRAINT chk_orders_total CHECK (total >= 0),

    INDEX idx_orders_status   (status),
    INDEX idx_orders_staff    (assigned_staff_id),
    INDEX idx_orders_pickup   (pickup_date)
) ENGINE=InnoDB;                                      -- customer_id is indexed automatically by its FK

-- ----------------------------------------------------------------------------
-- TABLE 4: order_items
-- ----------------------------------------------------------------------------
CREATE TABLE order_items (
    item_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    item_type  VARCHAR(100) NOT NULL,
    quantity   INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,                -- price at the time of ordering (historical snapshot)

    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE,          -- deleting an order removes its items
    CONSTRAINT chk_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_items_price    CHECK (unit_price >= 0)
) ENGINE=InnoDB;                                      -- order_id is indexed automatically by its FK
