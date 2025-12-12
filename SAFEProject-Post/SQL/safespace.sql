
--
-- Base de données : `safespace`
--

CREATE DATABASE IF NOT EXISTS safespace;
USE safespace;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `fullname` varchar(100) NOT NULL,
 `email` varchar(100) NOT NULL,
 `PASSWORD` varchar(255) NOT NULL,
 `role` enum('membre','conseilleur') DEFAULT 'membre',
 `status` enum('en attente','approved','blocked') DEFAULT 'en attente',
 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci


--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `author` varchar(100) NOT NULL,
 `message` text NOT NULL,
 `time` text NOT NULL,
 `image` varchar(255) NOT NULL,
 `status` varchar(100) NOT NULL,
 `id_user` int(11) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_post` int(11) NOT NULL,
 `author` varchar(100) NOT NULL,
 `message` text NOT NULL,
 `time` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

-- --------------------------------------------------------

--
-- Structure de la table `responds`
--

CREATE TABLE `responds` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_post` int(11) NOT NULL,
 `id_com` int(11) NOT NULL,
 `author` varchar(100) NOT NULL,
 `message` text NOT NULL,
 `time` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci

-- --------------------------------------------------------

