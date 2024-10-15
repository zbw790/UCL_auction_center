<?php
// Database connection information
$host = 'localhost';
$db = 'ucl_auction_center';
$root_user = 'root';
$root_pass = '';
$auction_user = 'auction_user';
$auction_pass = 'password';

try {
    // Create PDO connection (without connecting to the database)
    $pdo = new PDO("mysql:host=$host", $root_user, $root_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the database exists, if not create it
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";

    // Connect to the newly created or existing database
    $pdo->exec("USE $db");

    // Create auction_user and grant privileges
    $pdo->exec("CREATE USER IF NOT EXISTS '$auction_user'@'localhost' IDENTIFIED BY '$auction_pass'");
    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON $db.* TO '$auction_user'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "Auction user created and privileges granted.\n";

    // Check and create user table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user (
            user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) UNIQUE NOT NULL,
            password CHAR(60) NOT NULL,
            registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "User table created or already exists.\n";

    // Check and create personal_info table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personal_info (
            personal_info_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255),
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            postal_code VARCHAR(20) NOT NULL,
            country VARCHAR(100) NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            UNIQUE (user_id),
            FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
        )
    ");
    echo "Personal_info table created or already exists.\n";

    // Check and create category table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS category (
            category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE
        )
    ");
    echo "Category table created or already exists.\n";

    // Check and create item table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS item (
            item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            item_name VARCHAR(255) NOT NULL,
            description TEXT,
            category_id INT UNSIGNED NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            starting_price DECIMAL(10, 2) NOT NULL,
            reserve_price DECIMAL(10, 2),
            highest_bid DECIMAL(10, 2),
            highest_bidder_id INT UNSIGNED NULL,
            image_url VARCHAR(2083),
            status ENUM('active', 'ended', 'cancelled') NOT NULL DEFAULT 'active',
            FOREIGN KEY (seller_id) REFERENCES user(user_id),
            FOREIGN KEY (category_id) REFERENCES category(category_id),
            FOREIGN KEY (highest_bidder_id) REFERENCES user(user_id),
            CONSTRAINT check_dates CHECK (end_date > start_date)
        )
    ");
    echo "Item table created or already exists.\n";

    // Check and create bid table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bid (
            bid_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            item_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            bid_amount DECIMAL(10, 2) NOT NULL,
            bid_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES item(item_id),
            FOREIGN KEY (user_id) REFERENCES user(user_id)
        )
    ");
    echo "Bid table created or already exists.\n";

    // Check and create auction_transaction table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auction_transaction (
            transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            item_id INT UNSIGNED NOT NULL UNIQUE,
            buyer_id INT UNSIGNED NOT NULL,
            transaction_amount DECIMAL(10, 2) NOT NULL,
            transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES item(item_id),
            FOREIGN KEY (buyer_id) REFERENCES user(user_id)
        )
    ");
    echo "Auction transaction table created or already exists.\n";

    // Insert sample data into category table, using INSERT IGNORE to avoid duplicate entries
    $pdo->exec("
        INSERT IGNORE INTO category (category_name) VALUES 
        ('Electronics'),
        ('Fashion'),
        ('Home & Garden'),
        ('Sports'),
        ('Collectibles & Art'),
        ('Motors'),
        ('Toys & Hobbies')
    ");
    echo "Sample categories inserted or already exist.\n";

    // Insert more users (total 8)
    $pdo->exec("
    INSERT IGNORE INTO user (username, email, password, registration_date) VALUES
    ('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-01 10:00:00'),
    ('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-02 11:30:00'),
    ('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-03 14:45:00'),
    ('sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-04 09:15:00'),
    ('alex_johnson', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-05 16:20:00'),
    ('emily_davis', 'emily@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-06 13:10:00'),
    ('chris_lee', 'chris@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-07 11:45:00'),
    ('lisa_wang', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-08 15:30:00')
    ");
    echo "Sample users inserted or already exist.\n";

    $pdo->exec("
    INSERT IGNORE INTO item (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, highest_bid, highest_bidder_id, image_url, status) VALUES
    (1, 'Vintage Watch', 'A beautiful vintage watch from the 1960s', 5, '2024-09-10 12:00:00', '2024-10-20 12:00:00', 100.00, 200.00, 180.00, 2, './images/1.jpg', 'active'),
    (2, 'Gaming Laptop', 'High-performance gaming laptop', 1, '2024-09-15 10:00:00', '2024-10-25 10:00:00', 800.00, 1000.00, 950.00, 3, './images/2.jpg', 'active'),
    (3, 'Antique Vase', 'Rare antique vase from the Ming Dynasty', 5, '2024-09-20 14:00:00', '2024-10-10 14:00:00', 5000.00, 8000.00, 7500.00, 4, './images/3.jpg', 'ended'),
    (4, 'Mountain Bike', 'Professional mountain bike, barely used', 4, '2024-09-25 09:00:00', '2024-10-30 09:00:00', 300.00, 500.00, 450.00, 1, './images/4.jpg', 'active'),
    (1, 'Designer Handbag', 'Limited edition designer handbag', 2, '2024-09-30 11:00:00', '2024-11-05 11:00:00', 1000.00, 1500.00, 1200.00, 2, './images/5.jpg', 'active'),
    (2, 'Smart Home Kit', 'Complete smart home automation kit', 3, '2024-10-05 13:00:00', '2024-11-10 13:00:00', 200.00, 300.00, 250.00, 4, './images/6.jpg', 'active'),
    (3, 'Classic Car', 'Restored classic car from the 1970s', 6, '2024-10-10 15:00:00', '2024-11-15 15:00:00', 15000.00, 20000.00, 18000.00, 1, './images/7.jpg', 'active'),
    (4, 'Rare Comic Book', 'First edition rare comic book', 5, '2024-10-15 10:00:00', '2024-11-20 10:00:00', 500.00, 1000.00, 800.00, 3, './images/8.jpg', 'active'),
    (5, 'Smartphone', 'Latest model smartphone', 1, '2024-10-20 09:00:00', '2024-11-25 09:00:00', 500.00, 700.00, 650.00, 6, './images/9.jpg', 'active'),
    (6, 'Leather Jacket', 'Vintage leather jacket', 2, '2024-10-25 11:00:00', '2024-11-30 11:00:00', 200.00, 300.00, 280.00, 7, './images/10.jpg', 'active'),
    (7, 'Gardening Tools Set', 'Complete set of gardening tools', 3, '2024-09-01 13:00:00', '2024-10-05 13:00:00', 150.00, 250.00, 200.00, 8, './images/11.jpg', 'ended'),
    (8, 'Tennis Racket', 'Professional tennis racket', 4, '2024-09-05 15:00:00', '2024-10-15 15:00:00', 100.00, 150.00, 130.00, 1, './images/1.jpg', 'active'),
    (5, 'Antique Clock', 'Rare antique clock from the 18th century', 5, '2024-09-10 10:00:00', '2024-10-20 10:00:00', 2000.00, 3000.00, 2500.00, 2, './images/2.jpg', 'active'),
    (6, 'Electric Scooter', 'Foldable electric scooter', 6, '2024-09-15 12:00:00', '2024-10-25 12:00:00', 300.00, 400.00, 350.00, 3, './images/3.jpg', 'active'),
    (7, 'Board Game Collection', 'Collection of popular board games', 7, '2024-09-20 14:00:00', '2024-10-30 14:00:00', 100.00, 150.00, 120.00, 4, './images/4.jpg', 'active'),
    (8, 'Digital Camera', 'High-end digital camera with accessories', 1, '2024-09-25 16:00:00', '2024-11-05 16:00:00', 600.00, 800.00, 700.00, 5, './images/5.jpg', 'active')
    ");
    echo "Sample items inserted or already exist.\n";

    $pdo->exec("
    INSERT IGNORE INTO bid (item_id, user_id, bid_amount, bid_date) VALUES
    (1, 2, 150.00, '2024-09-25 14:30:00'),
    (1, 3, 180.00, '2024-10-05 16:45:00'),
    (2, 3, 900.00, '2024-09-30 11:15:00'),
    (2, 4, 950.00, '2024-10-10 13:20:00'),
    (3, 2, 6000.00, '2024-09-25 10:00:00'),
    (3, 4, 7500.00, '2024-10-05 17:30:00'),
    (4, 1, 400.00, '2024-10-05 09:45:00'),
    (4, 2, 450.00, '2024-10-12 14:10:00'),
    (5, 2, 1100.00, '2024-10-10 12:30:00'),
    (5, 3, 1200.00, '2024-10-20 15:40:00'),
    (6, 4, 250.00, '2024-10-15 16:20:00'),
    (7, 1, 16000.00, '2024-10-20 11:50:00'),
    (7, 4, 18000.00, '2024-11-01 13:15:00'),
    (8, 3, 800.00, '2024-10-25 10:05:00'),
    (9, 6, 600.00, '2024-11-01 11:30:00'),
    (9, 7, 650.00, '2024-11-10 14:45:00'),
    (10, 7, 250.00, '2024-11-05 13:20:00'),
    (10, 8, 280.00, '2024-11-15 16:10:00'),
    (11, 8, 180.00, '2024-09-15 15:40:00'),
    (11, 1, 200.00, '2024-09-25 09:30:00'),
    (12, 1, 120.00, '2024-09-25 10:15:00'),
    (12, 2, 130.00, '2024-10-05 12:50:00'),
    (13, 2, 2300.00, '2024-09-30 14:25:00'),
    (13, 3, 2500.00, '2024-10-10 17:00:00'),
    (14, 3, 330.00, '2024-10-01 16:40:00'),
    (14, 4, 350.00, '2024-10-10 10:20:00'),
    (15, 4, 110.00, '2024-10-05 11:55:00'),
    (15, 5, 120.00, '2024-10-15 13:35:00')
    ");
    echo "Sample bids inserted or already exist.\n";

    $pdo->exec("
    INSERT IGNORE INTO auction_transaction (item_id, buyer_id, transaction_amount, transaction_date) VALUES
    (3, 4, 7500.00, '2024-10-10 14:01:00'),
    (11, 1, 200.00, '2024-10-05 13:01:00')
    ");
    echo "Sample transactions inserted or already exist.\n";

    echo "All sample data inserted successfully.\n";

} catch (PDOException $e) {
    // Output error message
    echo "Database error: " . $e->getMessage();
}

// Test connection with the new user
try {
    $auction_pdo = new PDO("mysql:host=$host;dbname=$db", $auction_user, $auction_pass);
    $auction_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected with auction_user.\n";
} catch (PDOException $e) {
    echo "Connection failed for auction_user: " . $e->getMessage();
}
?>