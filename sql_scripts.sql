-- User table
CREATE TABLE user (
  userID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  registrationDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Personal Information table
CREATE TABLE personal_info (
  personal_infoID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
);

-- Category table
CREATE TABLE category (
  categoryID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoryName VARCHAR(100) NOT NULL UNIQUE
);

-- Item table
CREATE TABLE item (
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
);


-- Bid table
CREATE TABLE bid (
  bidID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemID INT UNSIGNED NOT NULL,
  userID INT UNSIGNED NOT NULL,
  bidAmount DECIMAL(10, 2) NOT NULL,
  bidDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (itemID) REFERENCES item(itemID),
  FOREIGN KEY (userID) REFERENCES user(userID)
);

-- Auction Transaction table
CREATE TABLE auction_transaction (
  transactionID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  itemID INT UNSIGNED NOT NULL UNIQUE,
  buyerID INT UNSIGNED NOT NULL,
  transactionAmount DECIMAL(10, 2) NOT NULL,
  transactionDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (itemID) REFERENCES item(itemID),
  FOREIGN KEY (buyerID) REFERENCES user(userID)
);

-- Insert some sample categories
INSERT INTO category (categoryName) VALUES 
('Electronics'),
('Fashion'),
('Home & Garden'),
('Sports'),
('Collectibles & Art'),
('Motors'),
('Toys & Hobbies');