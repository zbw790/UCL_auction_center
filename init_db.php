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

    // Check and create user table (now including personal info)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user (
            user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone_number VARCHAR(20)
        )
    ");
    echo "User table created or already exists.\n";

    // Check and create category table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS category (
            category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE
        )
    ");
    echo "Category table created or already exists.\n";

    // Check and create auction table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auction (
            auction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT UNSIGNED NOT NULL,
            category_id INT UNSIGNED NOT NULL,
            highest_bidder_id INT UNSIGNED,
            item_name VARCHAR(255) NOT NULL,
            description TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            starting_price DECIMAL(10, 2) NOT NULL,
            reserve_price DECIMAL(10, 2),
            current_price DECIMAL(10, 2),
            highest_bid_price DECIMAL(10, 2),
            image_url VARCHAR(2083),
            status ENUM('active', 'ended', 'cancelled') NOT NULL DEFAULT 'active',
            FOREIGN KEY (seller_id) REFERENCES user(user_id),
            FOREIGN KEY (category_id) REFERENCES category(category_id),
            FOREIGN KEY (highest_bidder_id) REFERENCES user(user_id),
            CONSTRAINT check_dates CHECK (end_date > start_date)
        )
    ");
    echo "Auction table created or already exists.\n";

    // Check and create bid table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bid (
            bid_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            auction_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            bid_amount DECIMAL(10, 2) NOT NULL,
            bid_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (auction_id) REFERENCES auction(auction_id),
            FOREIGN KEY (user_id) REFERENCES user(user_id)
        )
    ");
    echo "Bid table created or already exists.\n";

    // Check and create auction_transaction table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auction_transaction (
            transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            auction_id INT UNSIGNED NOT NULL UNIQUE,
            buyer_id INT UNSIGNED NOT NULL,
            transaction_amount DECIMAL(10, 2) NOT NULL,
            transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (auction_id) REFERENCES auction(auction_id),
            FOREIGN KEY (buyer_id) REFERENCES user(user_id)
        )
    ");
    echo "Auction transaction table created or already exists.\n";

    // Check and create rating table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rating (
            rating_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT UNSIGNED NOT NULL UNIQUE,
            rating_score INT NOT NULL,
            rating_comment TEXT,
            rating_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (transaction_id) REFERENCES auction_transaction(transaction_id),
            CHECK (rating_score >= 1 AND rating_score <= 5)
        )
    ");
    echo "Rating table created or already exists.\n";

    // Check and create private_message table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS private_message (
            message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sender_id INT UNSIGNED NOT NULL,
            receiver_id INT UNSIGNED NOT NULL,
            message_content TEXT NOT NULL,
            sent_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES user(user_id),
            FOREIGN KEY (receiver_id) REFERENCES user(user_id)
        )
    ");
    echo "Private message table created or already exists.\n";

    // Insert sample data into category table
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

    // Insert sample users with minimal required fields
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

    // Insert more sample auctions with varied end dates and prices
    $pdo->exec("
    INSERT IGNORE INTO auction (seller_id, category_id, item_name, description, start_date, end_date, starting_price, reserve_price, current_price, image_url, status) VALUES
    (1, 5, 'Vintage Watch', 'A beautiful vintage watch from the 1960s', '2024-09-10 12:00:00', '2024-10-20 12:00:00', 100.00, 200.00, 100.00, './images/1.jpg', 'active'),
    (2, 1, 'Gaming Laptop', 'High-performance gaming laptop', '2024-09-15 10:00:00', '2024-10-25 10:00:00', 800.00, 1000.00, 800.00, './images/2.jpg', 'active'),
    (3, 5, 'Antique Vase', 'Rare antique vase from the Ming Dynasty', '2024-09-20 14:00:00', '2024-10-10 14:00:00', 5000.00, 8000.00, 5000.00, './images/3.jpg', 'active'),
    (4, 4, 'Mountain Bike', 'Professional mountain bike, barely used', '2024-09-25 09:00:00', '2024-10-30 09:00:00', 300.00, 500.00, 300.00, './images/4.jpg', 'active'),
    (1, 2, 'Designer Handbag', 'Limited edition designer handbag', '2024-09-30 11:00:00', '2024-11-05 11:00:00', 1000.00, 1500.00, 1000.00, './images/5.jpg', 'active'),
    (2, 3, 'Smart Home Kit', 'Complete smart home automation kit', '2024-10-05 13:00:00', '2024-11-10 13:00:00', 200.00, 300.00, 200.00, './images/6.jpg', 'active'),
    (3, 6, 'Classic Car', 'Restored classic car from the 1970s', '2024-10-10 15:00:00', '2024-11-15 15:00:00', 15000.00, 20000.00, 15000.00, './images/7.jpg', 'active'),
    (4, 5, 'Rare Comic Book', 'First edition rare comic book', '2024-10-15 10:00:00', '2024-11-20 10:00:00', 500.00, 1000.00, 500.00, './images/8.jpg', 'active'),
    (5, 1, 'Smartphone', 'Latest model smartphone', '2024-10-20 09:00:00', '2024-11-25 09:00:00', 500.00, 700.00, 500.00, './images/9.jpg', 'active'),
    (6, 2, 'Leather Jacket', 'Vintage leather jacket', '2024-10-25 11:00:00', '2024-11-30 11:00:00', 200.00, 300.00, 200.00, './images/10.jpg', 'active'),
    (7, 3, 'Gardening Tools Set', 'Complete set of gardening tools', '2024-09-01 13:00:00', '2024-10-05 13:00:00', 150.00, 250.00, 150.00, './images/11.jpg', 'ended'),
    (8, 4, 'Tennis Racket', 'Professional tennis racket', '2024-09-05 15:00:00', '2024-10-15 15:00:00', 100.00, 150.00, 100.00, './images/1.jpg', 'active'),
    (5, 5, 'Antique Clock', 'Rare antique clock from the 18th century', '2024-09-10 10:00:00', '2024-10-20 10:00:00', 2000.00, 3000.00, 2000.00, './images/2.jpg', 'active'),
    (6, 6, 'Electric Scooter', 'Foldable electric scooter', '2024-09-15 12:00:00', '2024-10-25 12:00:00', 300.00, 400.00, 300.00, './images/3.jpg', 'active'),
    (7, 7, 'Board Game Collection', 'Collection of popular board games', '2024-09-20 14:00:00', '2024-10-30 14:00:00', 100.00, 150.00, 100.00, './images/4.jpg', 'active'),
    (8, 1, 'Digital Camera', 'High-end digital camera with accessories', '2024-09-25 16:00:00', '2024-11-05 16:00:00', 600.00, 800.00, 600.00, './images/5.jpg', 'active')
    ");

    // Insert sample bids with realistic bid progression
    $pdo->exec("
    INSERT IGNORE INTO bid (auction_id, user_id, bid_amount, bid_date) VALUES
    (1, 2, 150.00, '2024-09-25 14:30:00'),
    (1, 3, 180.00, '2024-10-05 16:45:00'),
    (1, 4, 200.00, '2024-10-06 12:30:00'),
    (2, 3, 900.00, '2024-09-30 11:15:00'),
    (2, 4, 950.00, '2024-10-10 13:20:00'),
    (2, 5, 1000.00, '2024-10-11 09:45:00'),
    (3, 2, 6000.00, '2024-09-25 10:00:00'),
    (3, 4, 7500.00, '2024-10-05 17:30:00'),
    (3, 6, 8000.00, '2024-10-06 14:15:00'),
    (4, 1, 400.00, '2024-10-05 09:45:00'),
    (4, 2, 450.00, '2024-10-12 14:10:00'),
    (4, 3, 500.00, '2024-10-13 16:20:00'),
    (5, 2, 1100.00, '2024-10-10 12:30:00'),
    (5, 3, 1200.00, '2024-10-20 15:40:00'),
    (5, 4, 1300.00, '2024-10-21 11:25:00'),
    (6, 4, 250.00, '2024-10-15 16:20:00'),
    (6, 5, 275.00, '2024-10-16 13:45:00'),
    (6, 6, 300.00, '2024-10-17 10:30:00'),
    (7, 1, 16000.00, '2024-10-20 11:50:00'),
    (7, 4, 18000.00, '2024-11-01 13:15:00'),
    (7, 5, 19000.00, '2024-11-02 15:40:00'),
    (8, 3, 800.00, '2024-10-25 10:05:00'),
    (8, 5, 900.00, '2024-10-26 14:30:00'),
    (8, 6, 950.00, '2024-10-27 16:15:00'),
    (9, 6, 600.00, '2024-11-01 11:30:00'),
    (9, 7, 650.00, '2024-11-10 14:45:00'),
    (9, 8, 700.00, '2024-11-11 09:20:00'),
    (10, 7, 250.00, '2024-11-05 13:20:00'),
    (10, 8, 280.00, '2024-11-15 16:10:00'),
    (10, 1, 300.00, '2024-11-16 10:45:00')
    ");

    // Insert sample transactions for ended auctions
    $pdo->exec("
    INSERT IGNORE INTO auction_transaction (auction_id, buyer_id, transaction_amount, transaction_date) VALUES
    (11, 1, 200.00, '2024-10-05 13:01:00'),
    (3, 6, 8000.00, '2024-10-10 14:01:00')
    ");

    // Insert sample ratings for completed transactions
    $pdo->exec("
    INSERT IGNORE INTO rating (transaction_id, rating_score, rating_comment, rating_date) VALUES
    (1, 5, 'Great buyer, smooth transaction!', '2024-10-06 10:00:00'),
    (2, 4, 'Item as described, quick shipping', '2024-10-11 15:30:00')
    ");

    // Insert sample private messages
    $pdo->exec("
    INSERT IGNORE INTO private_message (sender_id, receiver_id, message_content, sent_date) VALUES
    (1, 2, 'Is this item still available?', '2024-10-01 09:30:00'),
    (2, 1, 'Yes, it is! Feel free to place a bid.', '2024-10-01 10:15:00'),
    (3, 4, 'When can you ship the item?', '2024-10-02 14:20:00'),
    (4, 3, 'I can ship it tomorrow if you win the auction.', '2024-10-02 15:45:00')
    ");

    // Create trigger for updating highest bid
    $pdo->exec("
    CREATE TRIGGER IF NOT EXISTS update_auction_after_bid
    AFTER INSERT ON bid
    FOR EACH ROW
    BEGIN
        UPDATE auction
        SET current_price = NEW.bid_amount,
            highest_bid_price = NEW.bid_amount,
            highest_bidder_id = NEW.user_id
        WHERE auction_id = NEW.auction_id
        AND (highest_bid_price IS NULL OR NEW.bid_amount > highest_bid_price);
    END
    ");
    echo "Trigger for updating highest bid created or already exists.\n";

    echo "All sample data inserted successfully.\n";

} catch (PDOException $e) {
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