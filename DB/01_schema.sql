CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    
    profile_image VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL, 
    last_login TIMESTAMP NULL DEFAULT NULL,
    
    is_verified TINYINT(1) DEFAULT 0,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    iso_code_2 VARCHAR(2) NOT NULL, -- "BG"
    iso_code_3 VARCHAR(3) NOT NULL, -- "BGR"
    continent ENUM('Africa', 'Antarctica', 'Asia', 'Europe', 'North America', 'Oceania', 'South America', 'Unknown') DEFAULT 'Unknown',
    flag_image VARCHAR(255) DEFAULT NULL,
    
    is_historical TINYINT(1) DEFAULT 0 -- 0 = active country, 1 = no longer exists (e.g. USSR)
);

CREATE TABLE IF NOT EXISTS catalog_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    title VARCHAR(255) NOT NULL, 
    denomination VARCHAR(50), 
    year INT,
    period VARCHAR(100),
    material VARCHAR(100),
    description TEXT,
    
    weight DECIMAL(10, 2) DEFAULT NULL, -- grams
    diameter DECIMAL(10, 2) DEFAULT NULL, 
    thickness DECIMAL(10, 2) DEFAULT NULL, 
    shape VARCHAR(50) DEFAULT 'Round', -- round, square, hexagonal...
    mintage INT DEFAULT NULL, -- hoe many were made
    
    catalog_image_front VARCHAR(255),
    catalog_image_back VARCHAR(255),
    
    created_by_user_id INT, 
    is_approved TINYINT(1) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS user_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    catalog_coin_id INT NOT NULL, 
    
    grade VARCHAR(50), 
    status ENUM('collection', 'swap', 'sell', 'wishlist') DEFAULT 'collection',
    
    price DECIMAL(10,2) DEFAULT 0.00, -- price now
    purchase_price DECIMAL(10,2) DEFAULT NULL, 
    purchase_date DATE DEFAULT NULL, 
    purchase_location VARCHAR(100) DEFAULT NULL, -- ebay/friend/shop
    
    is_favorite TINYINT(1) DEFAULT 0, 
    
    private_notes TEXT,
    own_image_front VARCHAR(255),
    own_image_back VARCHAR(255),
    
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (catalog_coin_id) REFERENCES catalog_coins(id) ON DELETE CASCADE
);