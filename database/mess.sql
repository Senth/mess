-- phpMyAdmin SQL Dump
-- version 3.3.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 12, 2011 at 08:15 AM
-- Server version: 5.1.56
-- PHP Version: 5.3.6-pl0-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mess`
--
CREATE DATABASE IF NOT EXISTS `mess` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `mess`;

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE IF NOT EXISTS `budget` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `amount` double NOT NULL,
  `type` enum('yearly','monthly') COLLATE utf8_general_ci NOT NULL DEFAULT 'monthly',
  `date` date NOT NULL COMMENT 'This is used for when a new budget is created, or is updated. Then a new budget.',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `budget`
--


-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `parent_id` int(11) unsigned NULL DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `categories`
--


-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `code` int(11) unsigned NOT NULL,
  `name` varchar(200) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`code`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `items`
--


-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_general_ci NOT NULL,
  `link` varchar(80) COLLATE utf8_general_ci NOT NULL,
  `level` int(3) NOT NULL,
  `order` int(3) unsigned NOT NULL,
  `only_logged_out` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `link`, `level`, `order`, `only_logged_out`) VALUES
(1, 'Login', 'login', -1, 5, 1),
(2, 'Logout', 'login/logout', 1, 6, 0),
(3, 'Budget', 'budget', 1, 2, 0),
(4, 'Purchase', 'purchase', 1, 3, 0),
(5, 'Home', 'home', -1, 1, 0),
(6, 'Categories', 'categories', 1, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `date` date NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `place` varchar(40) COLLATE utf8_general_ci NOT NULL,
  `comment` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `total` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `store_id` (`store_id`),
  KEY `date` (`date`),
  KEY `place` (`place`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `purchases`
--


-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE IF NOT EXISTS `purchase_items` (
  `purchase_id` int(11) unsigned NOT NULL,
  `item_id` int(11) unsigned NOT NULL,
  `item_price` double NOT NULL,
  `item_qty` double NOT NULL,
  `item_discount` double NOT NULL DEFAULT '0',
  `category_id` int(11) unsigned NOT NULL,
  KEY `purchase_id` (`purchase_id`),
  KEY `item_id` (`item_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `purchase_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `reoccuring_purchases`
--

CREATE TABLE IF NOT EXISTS `reoccuring_purchases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `day` int(3) NOT NULL,
  `name` varchar(60) COLLATE utf8_general_ci NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `place` varchar(40) COLLATE utf8_general_ci NOT NULL,
  `comment` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `total` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `reoccuring_purchases`
--


-- --------------------------------------------------------

--
-- Table structure for table `reoccuring_purchase_items`
--

CREATE TABLE IF NOT EXISTS `reoccuring_purchase_items` (
  `purchase_id` int(11) unsigned NOT NULL,
  `item_id` int(11) unsigned NOT NULL,
  KEY `purchase_id` (`purchase_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reoccuring_purchase_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `name` varchar(60) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stores`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_general_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `created` int(11) NOT NULL,
  `level` int(3) NOT NULL,
  `email` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `first_name` varchar(25) COLLATE utf8_general_ci NOT NULL,
  `last_name` varchar(25) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE IF NOT EXISTS `user_groups` (
  `id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_general_ci NOT NULL,
  `level` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `name`, `level`) VALUES
(1, 'User', 1),
(2, 'Admin', 10);

-- --------------------------------------------------------

--
-- Table structure for table `user_items`
--

CREATE TABLE IF NOT EXISTS `user_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(10))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `user_items`
--

