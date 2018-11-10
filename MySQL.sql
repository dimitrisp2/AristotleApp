-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Φιλοξενητής: 127.0.0.1
-- Χρόνος δημιουργίας: 10 Νοε 2018 στις 09:36:27
-- Έκδοση διακομιστή: 10.1.21-MariaDB
-- Έκδοση PHP: 7.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `translator`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `contributions`
--

CREATE TABLE `contributions` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `translator` int(11) NOT NULL,
  `proofreader` int(11) DEFAULT NULL,
  `link` tinytext NOT NULL,
  `submit` timestamp NULL DEFAULT NULL,
  `review` timestamp NULL DEFAULT NULL,
  `review-status` int(11) DEFAULT NULL,
  `review-link` text,
  `vote-review` tinyint(4) DEFAULT '0',
  `partno` tinyint(3) UNSIGNED DEFAULT NULL,
  `wordcount` smallint(6) DEFAULT NULL,
  `vote-utopian` int(11) NOT NULL,
  `postpayout` decimal(9,4) DEFAULT NULL,
  `score` tinyint(3) UNSIGNED DEFAULT NULL,
  `difficulty` tinyint(4) DEFAULT NULL,
  `comment` text,
  `rowlock` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `crowdin` text NOT NULL,
  `github` text,
  `translator` tinytext,
  `proofreader` tinytext,
  `started` timestamp NULL DEFAULT NULL,
  `finished` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `taskmsg`
--

CREATE TABLE `taskmsg` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `parentid` int(11) NOT NULL,
  `message` text NOT NULL,
  `submitted` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `recipient` int(11) NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` tinytext NOT NULL,
  `message` text NOT NULL,
  `resolved` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(16) NOT NULL,
  `role` tinyint(4) NOT NULL COMMENT '1 = translator, 2 = proofreader, 3 = both, 0 = noaccess',
  `hired` date DEFAULT NULL,
  `dismissed` date DEFAULT NULL,
  `authkey` tinytext,
  `expiresin` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `weeklyreports`
--

CREATE TABLE `weeklyreports` (
  `id` int(11) NOT NULL,
  `weekend` date NOT NULL,
  `user` int(11) NOT NULL,
  `overview` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `taskmsg`
--
ALTER TABLE `taskmsg`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Ευρετήρια για πίνακα `weeklyreports`
--
ALTER TABLE `weeklyreports`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `contributions`
--
ALTER TABLE `contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT για πίνακα `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT για πίνακα `taskmsg`
--
ALTER TABLE `taskmsg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT για πίνακα `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT για πίνακα `weeklyreports`
--
ALTER TABLE `weeklyreports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
