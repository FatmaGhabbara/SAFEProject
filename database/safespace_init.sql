-- SAFEProject database bootstrap
-- Creates the schema SafeProject expects and seeds a few demo users
-- All sample users share the password hash provided by the user request

CREATE DATABASE IF NOT EXISTS safespace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE safespace;

-- Clean slate (order matters because of FKs)
DROP TABLE IF EXISTS support_messages;
DROP TABLE IF EXISTS support_requests;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    fullname VARCHAR(255) GENERATED ALWAYS AS (nom) STORED,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','conseilleur','membre') NOT NULL DEFAULT 'membre',
    status ENUM('en attente','actif','suspendu') NOT NULL DEFAULT 'en attente',
    profile_picture VARCHAR(255) NOT NULL DEFAULT 'assets/images/default-avatar.png',
    date_naissance DATE DEFAULT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    adresse VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    specialite VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_email (email),
    UNIQUE KEY idx_password_resets_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT DEFAULT NULL,
    suggestion TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared password hash supplied by the user request
SET @common_password := '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i';

INSERT INTO users (nom, email, password, role, status, profile_picture, created_at)
VALUES
    ('Administrateur SafeSpace', 'admin@safespace.com', @common_password, 'admin', 'actif', 'assets/images/default-avatar.png', NOW()),
    ('Marie Martin', 'marie.martin@example.com', @common_password, 'conseilleur', 'actif', 'assets/images/default-avatar.png', NOW()),
    ('Jean Dupont', 'jean.dupont@example.com', @common_password, 'membre', 'actif', 'assets/images/default-avatar.png', NOW());

-- Optional sample ratings to make the admin charts work immediately
INSERT INTO ratings (user_id, rating, comment, suggestion)
VALUES
    (2, 5, 'Toujours ravie du suivi proposé.', 'Continuer les sessions en visioconférence.'),
    (3, 4, 'Très bonne plateforme, quelques lenteurs parfois.', 'Ajouter une application mobile.');

-- ============================================
-- SUPPORT MODULE TABLES
-- ============================================

-- Table: support_requests (Demandes de support psychologique)
CREATE TABLE support_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    counselor_user_id INT DEFAULT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    urgence ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    statut ENUM('en_attente', 'assignee', 'en_cours', 'terminee', 'annulee') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_assignation DATETIME DEFAULT NULL,
    date_resolution DATETIME DEFAULT NULL,
    notes_admin TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_counselor_user_id (counselor_user_id),
    INDEX idx_statut (statut),
    INDEX idx_urgence (urgence),
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: support_messages (Messages de suivi)
CREATE TABLE support_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    support_request_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (support_request_id) REFERENCES support_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_support_request_id (support_request_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_lu (lu),
    INDEX idx_date_envoi (date_envoi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS FOR SUPPORT MODULE
-- ============================================

DELIMITER $$

-- Trigger: Set date_assignation when counselor is assigned
DROP TRIGGER IF EXISTS tr_set_date_assignation$$
CREATE TRIGGER tr_set_date_assignation
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.counselor_user_id IS NOT NULL AND OLD.counselor_user_id IS NULL THEN
        SET NEW.date_assignation = NOW();
        SET NEW.statut = 'assignee';
    END IF;
END$$

-- Trigger: Set date_resolution when request is completed
DROP TRIGGER IF EXISTS tr_set_date_resolution$$
CREATE TRIGGER tr_set_date_resolution
BEFORE UPDATE ON support_requests
FOR EACH ROW
BEGIN
    IF NEW.statut = 'terminee' AND OLD.statut != 'terminee' THEN
        SET NEW.date_resolution = NOW();
    END IF;
END$$

DELIMITER ;
