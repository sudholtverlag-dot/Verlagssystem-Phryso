CREATE DATABASE IF NOT EXISTS phryso_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phryso_data;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'redakteur') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hefte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    heftnummer VARCHAR(20) NOT NULL,
    titel VARCHAR(255) NOT NULL,
    status ENUM('planung', 'offen', 'geschlossen') NOT NULL DEFAULT 'planung',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS beitraege (
    id INT AUTO_INCREMENT PRIMARY KEY,
    heft_id INT NOT NULL,
    user_id INT NOT NULL,
    ueberschrift VARCHAR(255) NOT NULL,
    subline VARCHAR(255) DEFAULT NULL,
    content LONGTEXT NOT NULL,
    word_count INT NOT NULL DEFAULT 0,
    image_count INT NOT NULL DEFAULT 0,
    titelbild_flag TINYINT(1) NOT NULL DEFAULT 0,
    calculated_pages DECIMAL(8,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_beitraege_heft FOREIGN KEY (heft_id) REFERENCES hefte(id) ON DELETE CASCADE,
    CONSTRAINT fk_beitraege_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, role)
SELECT 'SVMAdmin', '$2y$12$jZqrgYpM1AYPynIiL3lxMOOcam3Eu8GOuqpAhgI/Ycr0GxZ54K9E2', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'SVMAdmin');
