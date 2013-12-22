-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 22, 2013 at 12:36 AM
-- Server version: 5.5.31
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ogame-lemonade`
--

-- --------------------------------------------------------

--
-- Table structure for table `build`
--

CREATE TABLE IF NOT EXISTS `build` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;

--
-- Dumping data for table `build`
--

INSERT INTO `build` (`id`, `name`, `type`, `content`) VALUES
(60, 'EndGame', 'Infra', 'RL:300;LL:150;HL:100;IC:100;GC:75;PT:60'),
(59, 'BigGunHeavy', 'Infra', 'RL:100;LL:50;HL:10;IC:4;GC:2;PT:1'),
(58, 'Overload', 'Infra', 'LL:1'),
(57, 'Mobile', 'Infra', 'LF:1;BS:1;LL:4'),
(56, 'Bruiser', 'Fleet', 'DS:1;DT:1'),
(55, 'Bourgeois', 'Fleet', 'DT:1'),
(54, 'Blitzkrieg', 'Fleet', 'BB:1;PR:1'),
(52, 'Emperor', 'Fleet', 'LF:1;BS:1'),
(53, 'Devourer', 'Fleet', 'BC:1'),
(51, 'LightSpeed', 'Fleet', 'CR:1'),
(50, 'Pirate', 'Fleet', 'LF:1;HF:1'),
(49, 'Swarm', 'Fleet', 'LF:1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
