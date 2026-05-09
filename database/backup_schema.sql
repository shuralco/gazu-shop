-- Database backup created on 2025-08-30
-- This file contains the schema and important data for SimpleShop

-- Payment Gateway Settings
CREATE TABLE IF NOT EXISTS payment_gateway_settings (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255),
    code VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    configuration TEXT,
    fee_percentage DECIMAL(5,2),
    min_amount DECIMAL(10,2),
    max_amount DECIMAL(10,2),
    currency VARCHAR(3),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Shipping Providers
CREATE TABLE IF NOT EXISTS shipping_providers (
    id INTEGER PRIMARY KEY,
    name VARCHAR(255),
    code VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    configuration TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Orders table with payment integration
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    middle_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(255),
    note TEXT,
    total DECIMAL(10,2),
    status BOOLEAN DEFAULT 0,
    paid_at TIMESTAMP,
    shipping_cost DECIMAL(10,2),
    shipping_provider VARCHAR(255),
    shipping_method VARCHAR(255),
    shipping_data TEXT,
    payment_method VARCHAR(255),
    payment_status VARCHAR(255) DEFAULT 'pending',
    coupon_id INTEGER,
    coupon_code VARCHAR(255),
    discount_amount DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id VARCHAR(36) PRIMARY KEY,
    order_id INTEGER,
    gateway VARCHAR(255),
    transaction_id VARCHAR(255),
    amount DECIMAL(10,2),
    currency VARCHAR(3),
    status VARCHAR(255),
    metadata TEXT,
    response_data TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);