-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 10:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `safeproject_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `article`
--

CREATE TABLE `article` (
  `idArticle` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `auteur` varchar(255) NOT NULL,
  `dateCreation` date NOT NULL,
  `idCategorie` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'non approuvé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `article`
--

INSERT INTO `article` (`idArticle`, `titre`, `contenu`, `auteur`, `dateCreation`, `idCategorie`, `status`) VALUES
(10, '2', '2', '2', '2001-06-06', 2, 'approved'),
(11, '3', '3', '3', '2020-11-25', 1, 'approved'),
(13, 'harrasment', 'hfkhsfhbfjksfvjkbvjsbfvkskbfbfvsbvuivhefb', 'skon', '2025-11-30', 1, 'pending'),
(14, 'hkrhfgerg', 'regergtgte', 'gdrg', '2025-11-17', 2, 'non approuvé');

-- --------------------------------------------------------

--
-- Table structure for table `article_reactions`
--

CREATE TABLE `article_reactions` (
  `id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `reaction_type` enum('like') DEFAULT 'like',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `article_reactions`
--

INSERT INTO `article_reactions` (`id`, `article_id`, `user_ip`, `reaction_type`, `created_at`) VALUES
(3, 10, '::1', 'like', '2025-11-16 01:14:13'),
(4, 13, '::1', 'like', '2025-11-16 07:08:43'),
(5, 11, '::1', 'like', '2025-11-17 12:48:24');

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

CREATE TABLE `categorie` (
  `idCategorie` int(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signalements`
--

CREATE TABLE `signalements` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signalements`
--

INSERT INTO `signalements` (`id`, `titre`, `description`, `type_id`, `created_at`) VALUES
(7, 'yigku', 'nnlnkn,k?knknknln ln,l,nkl,n', 6, '2025-11-17 22:53:17'),
(8, 'hgjvhjhk', 'bjkbnknk:bnl!', 10, '2025-11-17 22:55:44'),
(10, 'rhglgbdtb', 'tyufukf', 12, '2025-11-18 09:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `nom`) VALUES
(6, '1'),
(7, '2'),
(8, '3'),
(10, '55555555'),
(12, 'fedi'),
(13, 'fedi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`idArticle`);

--
-- Indexes for table `article_reactions`
--
ALTER TABLE `article_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`article_id`,`user_ip`);

--
-- Indexes for table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`idCategorie`);

--
-- Indexes for table `signalements`
--
ALTER TABLE `signalements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `article`
--
ALTER TABLE `article`
  MODIFY `idArticle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `article_reactions`
--
ALTER TABLE `article_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `idCategorie` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signalements`
--
ALTER TABLE `signalements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `article_reactions`
--
ALTER TABLE `article_reactions`
  ADD CONSTRAINT `article_reactions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`idArticle`) ON DELETE CASCADE;

--
-- Constraints for table `signalements`
--
ALTER TABLE `signalements`
  ADD CONSTRAINT `signalements_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
