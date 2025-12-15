-- ============================================
-- SAFEPROJECT_DB - COMPLETE DATABASE SCHEMA
-- All Tables: Users, Posts, Comments, Responds, Articles, Support
-- Generated: 2025-12-15
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- DATABASE CREATION
-- ============================================

CREATE DATABASE IF NOT EXISTS `safeproject_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `safeproject_db`;

-- ============================================
-- DROP EXISTING TABLES (Order matters for FK)
-- ============================================

DROP TABLE IF EXISTS `responds`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `support_messages`;
DROP TABLE IF EXISTS `support_requests`;
DROP TABLE IF EXISTS `comment_articles`;
DROP TABLE IF EXISTS `reactions`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `fingerprint_logs`;
DROP TABLE IF EXISTS `face_auth`;
DROP TABLE IF EXISTS `biometric_credentials`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `users`;

-- ============================================
-- CORE TABLES
-- ============================================

-- Table: users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','conseilleur','membre') NOT NULL DEFAULT 'membre',
  `status` enum('actif','inactif','en attente','banni') NOT NULL DEFAULT 'en attente',
  `profile_picture` varchar(255) DEFAULT 'assets/images/default-avatar.png',
  `date_naissance` date DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `specialite` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_resets
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: biometric_credentials
CREATE TABLE `biometric_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `credential_id` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `counter` int(11) DEFAULT 0,
  `device_name` varchar(100) DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credential_id` (`credential_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `biometric_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: face_auth
CREATE TABLE `face_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `face_descriptor` text NOT NULL,
  `registered_at` datetime NOT NULL,
  `last_used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `face_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: fingerprint_logs
CREATE TABLE `fingerprint_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `fingerprint_hash` varchar(255) NOT NULL,
  `browser_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fingerprint_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: ratings
CREATE TABLE `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` text DEFAULT NULL,
  `suggestion` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- POSTS/FORUM SYSTEM TABLES
-- ============================================

-- Table: posts
CREATE TABLE `posts` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `author` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: comments
CREATE TABLE `comments` (
  `id_com` int(11) NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_com`),
  KEY `id_post` (`id_post`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: responds
CREATE TABLE `responds` (
  `id_res` int(11) NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `id_com` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_res`),
  KEY `id_post` (`id_post`),
  KEY `id_com` (`id_com`),
  CONSTRAINT `responds_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `responds_ibfk_2` FOREIGN KEY (`id_com`) REFERENCES `comments` (`id_com`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ARTICLES SYSTEM TABLES
-- ============================================

-- Table: categories
CREATE TABLE `categories` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: articles
CREATE TABLE `articles` (
  `id_article` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `id_auteur` int(11) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected','brouillon','publie','archive') NOT NULL DEFAULT 'pending',
  `statut` enum('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
  `vues` int(11) DEFAULT 0,
  PRIMARY KEY (`id_article`),
  KEY `id_categorie` (`id_categorie`),
  KEY `id_auteur` (`id_auteur`),
  CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id_categorie`) ON DELETE CASCADE,
  CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`id_auteur`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: comment_articles
CREATE TABLE `comment_articles` (
  `id_comment` int(11) NOT NULL AUTO_INCREMENT,
  `id_article` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comment`),
  KEY `id_article` (`id_article`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `comment_articles_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id_article`) ON DELETE CASCADE,
  CONSTRAINT `comment_articles_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reactions
CREATE TABLE `reactions` (
  `id_reaction` int(11) NOT NULL AUTO_INCREMENT,
  `id_article` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `reaction` enum('like','love','support') NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reaction`),
  UNIQUE KEY `unique_user_article_reaction` (`id_article`,`id_user`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `articles` (`id_article`) ON DELETE CASCADE,
  CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUPPORT SYSTEM TABLES
-- ============================================

-- Table: support_requests
CREATE TABLE `support_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `counselor_user_id` int(11) DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `urgence` enum('basse','moyenne','haute') NOT NULL DEFAULT 'moyenne',
  `statut` enum('en_attente','assignee','en_cours','terminee','annulee') NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_assignation` datetime DEFAULT NULL,
  `date_resolution` datetime DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `counselor_user_id` (`counselor_user_id`),
  KEY `statut` (`statut`),
  CONSTRAINT `support_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_requests_ibfk_2` FOREIGN KEY (`counselor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: support_messages
CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `support_request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `support_request_id` (`support_request_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`support_request_id`) REFERENCES `support_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER $$

CREATE TRIGGER `tr_set_date_assignation`
BEFORE UPDATE ON `support_requests`
FOR EACH ROW
BEGIN
    IF OLD.counselor_user_id IS NULL AND NEW.counselor_user_id IS NOT NULL THEN
        SET NEW.date_assignation = NOW();
    END IF;
END$$

CREATE TRIGGER `tr_set_date_resolution`
BEFORE UPDATE ON `support_requests`
FOR EACH ROW
BEGIN
    IF OLD.statut != 'terminee' AND NEW.statut = 'terminee' THEN
        SET NEW.date_resolution = NOW();
    END IF;
END$$

DELIMITER ;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Default Admin User (password: password123)
SET @admin_password := '$2y$10$pJpHEzguq4INFPnqZn6rCeEB2.fFVwEs/gaSTKxa5ajyug6wZ.y9i';

INSERT INTO `users` (`nom`, `email`, `password`, `role`, `status`, `profile_picture`, `created_at`) VALUES
('Administrateur SafeSpace', 'admin@safespace.com', @admin_password, 'admin', 'actif', 'assets/images/default-avatar.png', NOW()),
('Marie Martin', 'marie.martin@example.com', @admin_password, 'conseilleur', 'actif', 'assets/images/default-avatar.png', NOW()),
('Jean Dupont', 'jean.dupont@example.com', @admin_password, 'membre', 'actif', 'assets/images/default-avatar.png', NOW());

-- Sample Categories
INSERT INTO `categories` (`nom_categorie`, `description`) VALUES
('Santé Mentale', 'Articles sur la santé mentale et le bien-être'),
('Conseils', 'Conseils pratiques pour la vie quotidienne'),
('Témoignages', 'Histoires et expériences partagées');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
