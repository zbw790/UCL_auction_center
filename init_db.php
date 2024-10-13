<?php
// 数据库连接信息
$host = 'localhost'; // MySQL 服务器
$db = 'UCL_auction_center'; // 数据库名
$user = 'root'; // MySQL 用户名
$pass = ''; // MySQL 密码（默认为空）

try {
    // 创建PDO连接（不连接数据库）
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 检查数据库是否存在，如果不存在则创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";

    // 连接到刚刚创建或已存在的数据库
    $pdo->exec("USE $db");

    // 检查并创建 user 表
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

    // 检查并创建 personal_info 表
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

    // 检查并创建 category 表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS category (
            categoryID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            categoryName VARCHAR(100) NOT NULL UNIQUE
        )
    ");
    echo "Category table created or already exists.\n";

    // 检查并创建 item 表
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

    // 检查并创建 bid 表
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

    // 检查并创建 auction_transaction 表
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

    // 插入样例数据到 category 表，使用 INSERT IGNORE 避免重复插入
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

} catch (PDOException $e) {
    // 输出错误信息
    echo "Database error: " . $e->getMessage();
}
?>
