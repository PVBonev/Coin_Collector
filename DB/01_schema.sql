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
    iso_code_2 VARCHAR(2) NOT NULL,
    iso_code_3 VARCHAR(3) NOT NULL,
    continent ENUM('Africa', 'Antarctica', 'Asia', 'Europe', 'North America', 'Oceania', 'South America', 'Unknown') DEFAULT 'Unknown',
    flag_image VARCHAR(255) DEFAULT NULL,
    
    is_historical TINYINT(1) DEFAULT 0
);

-- new table for materials used in coins
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    symbol VARCHAR(5) DEFAULT NULL,
    is_precious TINYINT(1) DEFAULT 0
);


INSERT IGNORE INTO materials (name, symbol, is_precious) VALUES 
('Gold', 'Au', 1),
('Silver', 'Ag', 1),
('Platinum', 'Pt', 1),
('Palladium', 'Pd', 1),
('Copper', 'Cu', 0),
('Nickel', 'Ni', 0),
('Zinc', 'Zn', 0),
('Iron', 'Fe', 0),
('Aluminum', 'Al', 0),
('Tin', 'Sn', 0),
('Bronze', NULL, 0),
('Brass', NULL, 0),
('Steel', NULL, 0);

CREATE TABLE IF NOT EXISTS catalog_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    title VARCHAR(255) NOT NULL, 
    denomination VARCHAR(50), 
    year INT,
    period VARCHAR(100),
    description TEXT,
    
    weight DECIMAL(10, 2) DEFAULT NULL,
    diameter DECIMAL(10, 2) DEFAULT NULL, 
    thickness DECIMAL(10, 2) DEFAULT NULL, 
    shape VARCHAR(50) DEFAULT 'Round',
    mintage INT DEFAULT NULL,
    
    catalog_image_front VARCHAR(255),
    catalog_image_back VARCHAR(255),
    catalog_image_edge VARCHAR(255),
    
    created_by_user_id INT, 
    is_approved TINYINT(1) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

-- table for coin composition, linking catalog coins <-> materials with percentage
CREATE TABLE IF NOT EXISTS coin_composition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    catalog_coin_id INT NOT NULL,
    material_id INT NOT NULL,
    percentage DECIMAL(5, 2) NOT NULL, -- f.e. 92.50
    
    FOREIGN KEY (catalog_coin_id) REFERENCES catalog_coins(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

CREATE TABLE IF NOT EXISTS user_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    catalog_coin_id INT NOT NULL, 
    
    grade VARCHAR(50), 
    status ENUM('collection', 'swap', 'sell', 'wishlist') DEFAULT 'collection',
    
    price DECIMAL(10,2) DEFAULT 0.00,
    purchase_price DECIMAL(10,2) DEFAULT NULL, 
    purchase_date DATE DEFAULT NULL, 
    purchase_location VARCHAR(100) DEFAULT NULL,
    
    is_favorite TINYINT(1) DEFAULT 0,
    
    is_locked TINYINT(1) DEFAULT 0,
    
    private_notes TEXT,
    own_image_front VARCHAR(255),
    own_image_back VARCHAR(255),
    own_image_edge VARCHAR(255), 
    
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (catalog_coin_id) REFERENCES catalog_coins(id) ON DELETE CASCADE
);

-- swap and sell trades between users
CREATE TABLE IF NOT EXISTS trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,       
    receiver_id INT NOT NULL,     
    
    type ENUM('swap', 'sell') DEFAULT 'swap', 
    status ENUM('pending', 'accepted', 'declined', 'completed', 'cancelled') DEFAULT 'pending',
    
    message TEXT DEFAULT NULL, 
    
    sender_confirmed TINYINT(1) DEFAULT 0,    
    receiver_confirmed TINYINT(1) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- coins involved in each trade
CREATE TABLE IF NOT EXISTS trade_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trade_id INT NOT NULL,
    user_coin_id INT NOT NULL,
    
    -- 'offered' =sender coin
    -- 'requested' = receiver coin
    type ENUM('offered', 'requested') NOT NULL, 
    
    FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
    FOREIGN KEY (user_coin_id) REFERENCES user_coins(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_coin_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_coin_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_coin_id) REFERENCES user_coins(id) ON DELETE CASCADE
);