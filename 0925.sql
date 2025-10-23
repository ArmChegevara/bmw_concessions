-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 23 oct. 2025 à 12:20
-- Version du serveur : 8.0.35
-- Version de PHP : 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `0925`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `id_categorie` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `titre`, `description`, `prix`, `id_categorie`, `date_creation`, `image`) VALUES
(1, 'Chaise gaming premium', 'ceci est une belle chaise gaming haut de gamme            ', 119.99, 1, '2025-09-08 10:53:23', NULL),
(3, 'Clavier Corsair AZERTY ', 'ceci est un clavier Gamer', 80.00, 3, '2025-10-02 15:04:38', NULL),
(15, 'Souris logitech', '                                                                                                                                                                        G502                                                                                                                                                ', 59.00, NULL, '2025-10-22 11:36:21', 'img_68f8a95f71d9b3.23027839.jpg'),
(16, 'Produit ajouté via API', 'ceci est un test', 30.00, NULL, '2025-10-22 16:57:11', NULL),
(17, 'Produit ajouté via API', 'ceci est un test', 30.00, NULL, '2025-10-22 19:22:48', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'PC'),
(2, 'Console'),
(3, 'Accessoires'),
(4, 'Jeux');

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_article` int NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`id`, `id_utilisateur`, `id_article`, `date_ajout`) VALUES
(2, 4, 1, '2025-10-02 14:18:57');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int NOT NULL,
  `email` varchar(191) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `role` enum('client','vendeur') DEFAULT 'client',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `mot_de_passe`, `nom`, `prenom`, `role`, `date_creation`) VALUES
(3, 'mehdi.littame@gmail.com', '$2y$10$S1sdpPt.242C9e.crss6d.GgZL75SOr8FioXUGxKOxa257kVEoDI.', 'Littamé', 'Mehdi', 'vendeur', '2025-10-02 13:28:50'),
(4, 'toto@test.com', '$2y$10$J0VW7j7Nvv2bl/7TA4ETbeB2oIpDNuaAdih.Ri1yrNiimr9E5LPEK', 'Test', 'Toto', 'client', '2025-10-02 13:30:12'),
(9, 'tutu@gmail.com', '$2y$10$./9EjHErjUeTeuLZtLKnBeueCtEsb9SKnQdvGXMDz3eoT4oyc1BoK', 'test', 'test', 'client', '2025-10-21 09:39:12'),
(10, 'superuser@gmail.com', '$2y$10$ST2dEbjil2Es7K/amzCoEuvUpFHzcr7Dkq9g/qNHyMvCPsEKgO7W.', 'admin', 'tesst', 'vendeur', '2025-10-22 07:26:35');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`id_utilisateur`,`id_article`),
  ADD KEY `idx_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_article` (`id_article`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
