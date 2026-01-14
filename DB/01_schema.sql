CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
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
    flag_image VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS catalog_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    year INT,
    face_value VARCHAR(50),
    period VARCHAR(100),
    material VARCHAR(100),
    description TEXT,
    catalog_image_front VARCHAR(255),
    catalog_image_back VARCHAR(255),
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS user_coins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    catalog_coin_id INT NOT NULL,
    grade VARCHAR(50),
    private_notes TEXT,
    status ENUM('collection', 'swap', 'sell', 'wishlist') DEFAULT 'collection',
    own_image_front VARCHAR(255),
    own_image_back VARCHAR(255),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (catalog_coin_id) REFERENCES catalog_coins(id) ON DELETE CASCADE
);