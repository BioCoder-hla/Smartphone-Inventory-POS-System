-- SQL Schema for Smartphone Shop Inventory Management System
-- Database: MySQL / MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for `suppliers`
-- Stores information about where you buy your stock from.
--
CREATE TABLE `suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone_number` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `customers`
-- Stores information about your clients.
--
CREATE TABLE `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) UNIQUE DEFAULT NULL,
  `phone_number` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `users`
-- Stores login credentials and roles for system users (employees).
--
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Manager', 'Sales Staff') NOT NULL DEFAULT 'Sales Staff',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `smartphones`
-- This is your product catalog. It defines each model of phone you sell.
--
CREATE TABLE `smartphones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `brand` VARCHAR(100) NOT NULL,
  `model_name` VARCHAR(255) NOT NULL,
  `storage_gb` INT(11) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `specifications` JSON DEFAULT NULL,
  `release_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_smartphone_variant` (`brand`, `model_name`, `storage_gb`, `color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `inventory_items`
-- This is the core table, tracking every single physical phone unit.
--
CREATE TABLE `inventory_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `smartphone_id` INT(11) NOT NULL,
  `imei` VARCHAR(15) NOT NULL UNIQUE,
  `serial_number` VARCHAR(100) DEFAULT NULL UNIQUE,
  `status` ENUM('In Stock', 'Sold', 'Returned', 'Defective') NOT NULL DEFAULT 'In Stock',
  `purchase_price` DECIMAL(10, 2) NOT NULL,
  `selling_price` DECIMAL(10, 2) NOT NULL,
  `supplier_id` INT(11) DEFAULT NULL,
  `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_imei` (`imei`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`smartphone_id`) REFERENCES `smartphones`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `sales`
-- Records the header information for each transaction.
--
CREATE TABLE `sales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `sale_items`
-- A junction table linking a sale to the specific inventory items sold.
--
CREATE TABLE `sale_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sale_id` INT(11) NOT NULL,
  `inventory_item_id` INT(11) NOT NULL UNIQUE, -- A single item can only be sold once.
  `price_at_sale` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for `purchase_orders`
-- Tracks orders placed with your suppliers.
--
CREATE TABLE `purchase_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` INT(11) NOT NULL,
  `order_date` DATE NOT NULL,
  `expected_delivery_date` DATE DEFAULT NULL,
  `status` ENUM('Pending', 'Shipped', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `total_cost` DECIMAL(12, 2) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
