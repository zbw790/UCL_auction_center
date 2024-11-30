CREATE DATABASE IF NOT EXISTS ucl_auction_center;

USE ucl_auction_center;

CREATE USER IF NOT EXISTS 'auction_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON ucl_auction_center.* TO 'auction_user'@'localhost';
FLUSH PRIVILEGES;

-- User table
CREATE TABLE user (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone_number VARCHAR(20),
    registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Category table
CREATE TABLE category (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Bid table (needs to be created before auction due to foreign key reference)
CREATE TABLE bid (
    bid_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auction_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    bid_amount DECIMAL(10, 2) NOT NULL,
    bid_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);

-- Auction table
CREATE TABLE auction (
    auction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id INT UNSIGNED NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT UNSIGNED NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    starting_price DECIMAL(10, 2) NOT NULL,
    reserve_price DECIMAL(10, 2),
    highest_bid DECIMAL(10, 2),
    highest_bid_id INT UNSIGNED,
    image_url VARCHAR(2083),
    status ENUM('active', 'ended', 'cancelled') NOT NULL DEFAULT 'active',
    FOREIGN KEY (seller_id) REFERENCES user(user_id),
    FOREIGN KEY (category_id) REFERENCES category(category_id),
    FOREIGN KEY (highest_bid_id) REFERENCES bid(bid_id),
    CONSTRAINT check_dates CHECK (end_date > start_date)
);

-- Add foreign key to bid table after auction table is created
ALTER TABLE bid
ADD FOREIGN KEY (auction_id) REFERENCES auction(auction_id);

-- Auction Transaction table
CREATE TABLE auction_transaction (
    transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auction_id INT UNSIGNED NOT NULL UNIQUE,
    buyer_id INT UNSIGNED NOT NULL,
    transaction_amount DECIMAL(10, 2) NOT NULL,
    transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auction_id) REFERENCES auction(auction_id),
    FOREIGN KEY (buyer_id) REFERENCES user(user_id)
);

-- Insert sample categories
INSERT INTO category (category_name) VALUES 
('Electronics'),
('Fashion'),
('Home & Garden'),
('Sports'),
('Collectibles & Art'),
('Motors'),
('Toys & Hobbies');

-- Insert sample users
INSERT INTO user (username, email, password, registration_date) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-01 10:00:00'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-02 11:30:00'),
('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-03 14:45:00'),
('sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-09-04 09:15:00');

-- Insert auctions first without highest_bid_id
INSERT INTO auction (seller_id, item_name, description, category_id, start_date, end_date, starting_price, reserve_price, highest_bid, image_url, status) VALUES
(1, 'Vintage Watch', 'A beautiful vintage watch from the 1960s', 5, '2024-09-10 12:00:00', '2024-10-20 12:00:00', 100.00, 200.00, 180.00, './images/1.jpg', 'active'),
(2, 'Gaming Laptop', 'High-performance gaming laptop', 1, '2024-09-15 10:00:00', '2024-10-25 10:00:00', 800.00, 1000.00, 950.00, './images/2.jpg', 'active'),
(3, 'Antique Vase', 'Rare antique vase from the Ming Dynasty', 5, '2024-09-20 14:00:00', '2024-10-10 14:00:00', 5000.00, 8000.00, 7500.00, './images/3.jpg', 'ended'),
(4, 'Mountain Bike', 'Professional mountain bike, barely used', 4, '2024-09-25 09:00:00', '2024-10-30 09:00:00', 300.00, 500.00, 450.00, './images/4.jpg', 'active');

-- Insert bids
INSERT INTO bid (auction_id, user_id, bid_amount, bid_date) VALUES
(1, 2, 150.00, '2024-09-25 14:30:00'),
(1, 3, 180.00, '2024-10-05 16:45:00'),
(2, 3, 900.00, '2024-09-30 11:15:00'),
(2, 4, 950.00, '2024-10-10 13:20:00');

-- Update auctions with highest_bid_id
UPDATE auction a
JOIN bid b ON a.auction_id = b.auction_id
SET a.highest_bid_id = b.bid_id
WHERE b.bid_amount = a.highest_bid;

-- Insert transactions
INSERT INTO auction_transaction (auction_id, buyer_id, transaction_amount, transaction_date) VALUES
(3, 4, 7500.00, '2024-10-10 14:01:00');

-- Create trigger for updating highest bid
DELIMITER //
CREATE TRIGGER update_auction_after_bid
AFTER INSERT ON bid
FOR EACH ROW
BEGIN
    UPDATE auction
    SET highest_bid = NEW.bid_amount,
        highest_bid_id = NEW.bid_id
    WHERE auction_id = NEW.auction_id
    AND (highest_bid IS NULL OR NEW.bid_amount > highest_bid);
END//
DELIMITER ;