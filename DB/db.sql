CREATE DATABASE IF NOT EXISTS coin_collector_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coin_collector_db;

--table for users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0, -- 0 is unverified, 1 is verified
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--table for email confirmations with tokens
CREATE TABLE IF NOT EXISTS email_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

--table for countries
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    iso_code VARCHAR(3) NOT NULL, -- BGN, USA
    flag_image VARCHAR(255) DEFAULT NULL
);

--catalog of coins(type of coin and info)
CREATE TABLE IF NOT EXISTS catalog_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    title VARCHAR(255) NOT NULL, --f.e."2 Лева Паисий Хилендарски"
    year INT,
    face_value VARCHAR(50), --f.e "2 BGN"
    period VARCHAR(100), -- f.e."Народна република (1946-1990)"
    material VARCHAR(100), -- f.e. "Мед-никел"
    description TEXT,
    catalog_image_front VARCHAR(255), -- official pic front and back
    catalog_image_back VARCHAR(255),
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

--specific coins owned by users not just type of coin from catalog
CREATE TABLE IF NOT EXISTS user_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    catalog_coin_id INT NOT NULL,
    
    --specific details about the user's coin
    grade VARCHAR(50), -- СЪСТОЯНИЕ: UNC, VF, F...
    private_notes TEXT, -- can be anything user wants to note
    status ENUM('collection', 'swap', 'sell', 'wishlist') DEFAULT 'collection',
    
    --personal pics if user wants to upload
    own_image_front VARCHAR(255),
    own_image_back VARCHAR(255),
    
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (catalog_coin_id) REFERENCES catalog_coins(id) ON DELETE CASCADE
);