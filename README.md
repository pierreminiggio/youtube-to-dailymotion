# youtube-to-dailymotion

Migration :
```sql
-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  lun. 28 déc. 2020 à 17:11
-- Version du serveur :  5.7.17
-- Version de PHP :  5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Structure de la table `dailymotion_channel`
--

CREATE TABLE `dailymotion_channel` (
  `id` int(11) NOT NULL,
  `dailymotion_id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `api_secret` varchar(255) NOT NULL,
  `description_prefix` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dailymotion_channel_youtube_channel`
--

CREATE TABLE `dailymotion_channel_youtube_channel` (
  `id` int(11) NOT NULL,
  `dailymotion_id` int(11) NOT NULL,
  `youtube_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dailymotion_video`
--

CREATE TABLE `dailymotion_video` (
  `id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `dailymotion_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dailymotion_video_youtube_video`
--

CREATE TABLE `dailymotion_video_youtube_video` (
  `id` int(11) NOT NULL,
  `dailymotion_id` int(11) NOT NULL,
  `youtube_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `dailymotion_channel`
--
ALTER TABLE `dailymotion_channel`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dailymotion_channel_youtube_channel`
--
ALTER TABLE `dailymotion_channel_youtube_channel`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dailymotion_video`
--
ALTER TABLE `dailymotion_video`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `dailymotion_video_youtube_video`
--
ALTER TABLE `dailymotion_video_youtube_video`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `dailymotion_channel`
--
ALTER TABLE `dailymotion_channel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dailymotion_channel_youtube_channel`
--
ALTER TABLE `dailymotion_channel_youtube_channel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dailymotion_video`
--
ALTER TABLE `dailymotion_video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dailymotion_video_youtube_video`
--
ALTER TABLE `dailymotion_video_youtube_video`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

```
