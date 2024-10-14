<?php
// Database connection information
$host = 'localhost';
$db = 'UCL_auction_center';
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
    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON $db.* TO '$auction_user'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "Auction user created and privileges granted.\n";

    // Check and create user table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user (
            userID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) UNIQUE NOT NULL,
            password CHAR(60) NOT NULL,
            registrationDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "User table created or already exists.\n";

    // Check and create personal_info table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personal_info (
            personalInfoID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            userID INT UNSIGNED NOT NULL,
            firstName VARCHAR(100) NOT NULL,
            lastName VARCHAR(100) NOT NULL,
            addressLine1 VARCHAR(255) NOT NULL,
            addressLine2 VARCHAR(255),
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            postalCode VARCHAR(20) NOT NULL,
            country VARCHAR(100) NOT NULL,
            phoneNumber VARCHAR(20) NOT NULL,
            UNIQUE (userID),
            FOREIGN KEY (userID) REFERENCES user(userID) ON DELETE CASCADE
        )
    ");
    echo "Personal_info table created or already exists.\n";

    // Check and create category table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS category (
            categoryID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            categoryName VARCHAR(100) NOT NULL UNIQUE
        )
    ");
    echo "Category table created or already exists.\n";

    // Check and create item table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS item (
            itemID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sellerID INT UNSIGNED NOT NULL,
            itemName VARCHAR(255) NOT NULL,
            description TEXT,
            categoryID INT UNSIGNED NOT NULL,
            startDate DATETIME NOT NULL,
            endDate DATETIME NOT NULL,
            startingPrice DECIMAL(10, 2) NOT NULL,
            reservePrice DECIMAL(10, 2),
            highestBid DECIMAL(10, 2),
            highestBidderID INT UNSIGNED NULL,
            imageURL VARCHAR(2083),
            status ENUM('active', 'ended', 'cancelled') NOT NULL DEFAULT 'active',
            FOREIGN KEY (sellerID) REFERENCES user(userID),
            FOREIGN KEY (categoryID) REFERENCES category(categoryID),
            FOREIGN KEY (highestBidderID) REFERENCES user(userID),
            CONSTRAINT check_dates CHECK (endDate > startDate)
        )
    ");
    echo "Item table created or already exists.\n";

    // Check and create bid table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bid (
            bidID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            itemID INT UNSIGNED NOT NULL,
            userID INT UNSIGNED NOT NULL,
            bidAmount DECIMAL(10, 2) NOT NULL,
            bidDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (itemID) REFERENCES item(itemID),
            FOREIGN KEY (userID) REFERENCES user(userID)
        )
    ");
    echo "Bid table created or already exists.\n";

    // Check and create auction_transaction table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auction_transaction (
            transactionID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            itemID INT UNSIGNED NOT NULL UNIQUE,
            buyerID INT UNSIGNED NOT NULL,
            transactionAmount DECIMAL(10, 2) NOT NULL,
            transactionDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (itemID) REFERENCES item(itemID),
            FOREIGN KEY (buyerID) REFERENCES user(userID)
        )
    ");
    echo "Auction transaction table created or already exists.\n";

    // Insert sample data into category table, using INSERT IGNORE to avoid duplicate entries
    $pdo->exec("
        INSERT IGNORE INTO category (categoryName) VALUES 
        ('Electronics'),
        ('Fashion'),
        ('Home & Garden'),
        ('Sports'),
        ('Collectibles & Art'),
        ('Motors'),
        ('Toys & Hobbies')
    ");
    echo "Sample categories inserted or already exist.\n";

    // Insert 4 users
    $pdo->exec("
        INSERT IGNORE INTO user (username, email, password, registrationDate) VALUES
        ('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-05-01 10:00:00'),
        ('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-05-02 11:30:00'),
        ('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-05-03 14:45:00'),
        ('sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2023-05-04 09:15:00')
    ");
    echo "Sample users inserted or already exist.\n";

    // Insert 8 auctions (items)
    $pdo->exec("
        INSERT IGNORE INTO item (sellerID, itemName, description, categoryID, startDate, endDate, startingPrice, reservePrice, highestBid, highestBidderID, imageURL, status) VALUES
        (1, 'Vintage Watch', 'A beautiful vintage watch from the 1960s', 5, '2023-05-10 12:00:00', '2023-06-10 12:00:00', 100.00, 200.00, 180.00, 2, 'http://example.com/watch.jpg', 'active'),
        (2, 'Gaming Laptop', 'High-performance gaming laptop', 1, '2023-05-11 10:00:00', '2023-06-11 10:00:00', 800.00, 1000.00, 950.00, 3, 'http://example.com/laptop.jpg', 'active'),
        (3, 'Antique Vase', 'Rare antique vase from the Ming Dynasty', 5, '2023-05-12 14:00:00', '2023-05-19 14:00:00', 5000.00, 8000.00, 7500.00, 4, 'http://example.com/vase.jpg', 'ended'),
        (4, 'Mountain Bike', 'Professional mountain bike, barely used', 4, '2023-05-13 09:00:00', '2023-06-13 09:00:00', 300.00, 500.00, 450.00, 1, 'http://example.com/bike.jpg', 'active'),
        (1, 'Designer Handbag', 'Limited edition designer handbag', 2, '2023-05-14 11:00:00', '2023-06-14 11:00:00', 1000.00, 1500.00, 1200.00, 2, 'http://example.com/handbag.jpg', 'active'),
        (2, 'Smart Home Kit', 'Complete smart home automation kit', 3, '2023-05-15 13:00:00', '2023-06-15 13:00:00', 200.00, 300.00, 250.00, 4, 'http://example.com/smarthome.jpg', 'active'),
        (3, 'Classic Car', 'Restored classic car from the 1970s', 6, '2023-05-16 15:00:00', '2023-06-16 15:00:00', 15000.00, 20000.00, 18000.00, 1, 'http://example.com/car.jpg', 'active'),
        (4, 'Rare Comic Book', 'First edition rare comic book', 5, '2023-05-17 10:00:00', '2023-06-17 10:00:00', 500.00, 1000.00, 800.00, 3, 'http://example.com/comic.jpg', 'active')
    ");
    echo "Sample items inserted or already exist.\n";

    // Insert bids for each auction
    $pdo->exec("
        INSERT IGNORE INTO bid (itemID, userID, bidAmount, bidDate) VALUES
        (1, 2, 150.00, '2023-05-20 14:30:00'),
        (1, 3, 180.00, '2023-05-25 16:45:00'),
        (2, 3, 900.00, '2023-05-21 11:15:00'),
        (2, 4, 950.00, '2023-05-26 13:20:00'),
        (3, 2, 6000.00, '2023-05-15 10:00:00'),
        (3, 4, 7500.00, '2023-05-18 17:30:00'),
        (4, 1, 400.00, '2023-05-22 09:45:00'),
        (4, 2, 450.00, '2023-05-27 14:10:00'),
        (5, 2, 1100.00, '2023-05-23 12:30:00'),
        (5, 3, 1200.00, '2023-05-28 15:40:00'),
        (6, 4, 250.00, '2023-05-24 16:20:00'),
        (7, 1, 16000.00, '2023-05-25 11:50:00'),
        (7, 4, 18000.00, '2023-05-29 13:15:00'),
        (8, 3, 800.00, '2023-05-26 10:05:00')
    ");
    echo "Sample bids inserted or already exist.\n";

    // Insert transaction for the ended auction
    $pdo->exec("
        INSERT IGNORE INTO auction_transaction (itemID, buyerID, transactionAmount, transactionDate) VALUES
        (3, 4, 7500.00, '2023-05-19 14:01:00')
    ");
    echo "Sample transaction inserted or already exists.\n";

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