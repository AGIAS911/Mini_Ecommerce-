CREATE DATABASE IF NOT EXISTS ecommerce_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_products_category 
        FOREIGN KEY (category_id) REFERENCES categories (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_product_images_product 
        FOREIGN KEY (product_id) REFERENCES products (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cod') DEFAULT 'cod',
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    order_status ENUM('pending', 'delivered') DEFAULT 'pending',
    CONSTRAINT fk_orders_user 
        FOREIGN KEY (user_id) REFERENCES users (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_order_items_order 
        FOREIGN KEY (order_id) REFERENCES orders (id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product 
        FOREIGN KEY (product_id) REFERENCES products (id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@store.com', '$2y$10$tZ2E7f8wM9Fw9kO.JcRfe.gJ1C9O.sEfe024YwQ8tJ6vP6P9tL1sK', 'admin'),
('Customer', 'customer@store.com', '$2y$10$tZ2E7f8wM9Fw9kO.JcRfe.gJ1C9O.sEfe024YwQ8tJ6vP6P9tL1sK', 'customer');

INSERT INTO categories (name, description) VALUES
('Electronics', 'Gadgets and hardware'),
('Clothing', 'Men and women apparel');

INSERT INTO products (category_id, name, description, price, stock) VALUES
(1, 'Gaming Mouse', 'Optical RGB gaming mouse', 25.00, 15),
(1, 'Mechanical Keyboard', 'Blue switch mechanical keyboard', 45.00, 10),
(2, 'Black Hoodie', 'Cotton pullover hoodie', 20.00, 30);

INSERT INTO product_images (product_id, image_path, is_primary) VALUES
(1, 'uploads/products/mouse_1.webp', 1),
(2, 'uploads/products/keyboard_1.webp', 1),
(3, 'uploads/products/hoodie_1.webp', 1);