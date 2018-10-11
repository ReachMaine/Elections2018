-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 11, 2018 at 04:49 PM
-- Server version: 5.6.40-84.0-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reachmai_electst`
--

-- --------------------------------------------------------

--
-- Table structure for table `votes2018`
--

CREATE TABLE `votes2018` (
  `electionrecordid` int(11) NOT NULL,
  `raceorder` int(11) NOT NULL,
  `votes` int(11) DEFAULT NULL,
  `reported` bit(1) NOT NULL,
  `race` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `candidate` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `party` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `town` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precinct` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `r_d` int(11) NOT NULL,
  `r_g` int(11) NOT NULL,
  `r_r` int(11) NOT NULL,
  `r_u` int(11) NOT NULL,
  `pct_d` decimal(4,2) NOT NULL,
  `pct_r` decimal(4,2) NOT NULL,
  `pct_u` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `votes2018`
--
ALTER TABLE `votes2018`
  ADD PRIMARY KEY (`electionrecordid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `votes2018`
--
ALTER TABLE `votes2018`
  MODIFY `electionrecordid` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
