-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 06, 2013 at 10:26 AM
-- Server version: 5.5.34-0ubuntu0.13.04.1
-- PHP Version: 5.4.9-4ubuntu2.3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `isinya`
--

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

DROP TABLE IF EXISTS `chapters`;
CREATE TABLE IF NOT EXISTS `chapters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) unsigned NOT NULL,
  `chapter_name` varchar(255) NOT NULL,
  `chapter_description` text NOT NULL,
  `modify_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores metadata about chapters' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_name` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `add_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores metadata about a course' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `resource_id` int(11) unsigned DEFAULT NULL,
  `resource_permission` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `resource_id`, `resource_permission`) VALUES
(5, 'INVENTORY_EDIT', 2, 'edit'),
(6, 'INVENTORY_VIEW', 2, 'view'),
(7, 'INVENTORY_DELETE', 2, 'delete'),
(8, 'EQUIPMENT_EDIT', 3, 'edit'),
(9, 'EQUIPMENT_VIEW', 3, 'view'),
(10, 'EQUIPMENT_DELETE', 3, 'delete'),
(11, 'PERSONNEL_EDIT', 4, 'edit'),
(12, 'PERSONNEL_VIEW', 4, 'view'),
(13, 'PERSONNEL_DELETE', 4, 'delete'),
(14, 'CLIENTS_EDIT', 5, 'edit'),
(15, 'CLIENTS_VIEW', 5, 'view'),
(16, 'CLIENTS_DELETE', 5, 'delete'),
(17, 'TASKS_EDIT', 6, 'edit'),
(18, 'TASKS_VIEW', 6, 'view'),
(19, 'TASKS_DELETE', 6, 'delete'),
(20, 'JOBS_EDIT', 7, 'edit'),
(21, 'JOBS_VIEW', 7, 'view'),
(22, 'JOBS_DELETE', 7, 'delete'),
(23, 'LOCATIONS_EDIT', 8, 'edit'),
(24, 'LOCATIONS_VIEW', 8, 'view'),
(25, 'LOCATIONS_DELETE', 8, 'delete'),
(26, 'REPORTS_EDIT', 9, 'edit'),
(27, 'REPORTS_VIEW', 9, 'view'),
(28, 'REPORTS_DELETE', 9, 'delete');

-- --------------------------------------------------------

--
-- Table structure for table `permissions_roles`
--

DROP TABLE IF EXISTS `permissions_roles`;
CREATE TABLE IF NOT EXISTS `permissions_roles` (
  `role_id` int(11) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_permissions_roles_permissions1_idx` (`permission_id`),
  KEY `fk_permissions_roles_roles1_idx` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permissions_roles`
--

INSERT INTO `permissions_roles` (`role_id`, `permission_id`) VALUES
(3, 5),
(7, 5),
(3, 6),
(7, 6),
(8, 6),
(3, 7),
(6, 8),
(7, 8),
(6, 9),
(6, 10),
(2, 11),
(3, 11),
(7, 11),
(2, 12),
(3, 12),
(2, 13),
(3, 13),
(2, 14),
(7, 14),
(2, 15),
(2, 16),
(2, 17),
(7, 17),
(2, 18),
(2, 19),
(2, 20),
(7, 20),
(2, 21),
(2, 22),
(2, 23),
(7, 23),
(2, 24),
(2, 25),
(2, 26),
(7, 26),
(2, 27),
(2, 28);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
CREATE TABLE IF NOT EXISTS `resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(100) DEFAULT NULL,
  `delete_status` int(11) DEFAULT '0',
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `resource_name`, `delete_status`) VALUES
(2, 'inventory', 0),
(3, 'equipment', 0),
(4, 'personnel', 0),
(5, 'clients', 0),
(6, 'tasks', 0),
(7, 'jobs', 0),
(8, 'locations', 0),
(9, 'reports', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `delete_status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `delete_status`) VALUES
(1, 'Login', 'Login privileges, granted after account confirmation', 0),
(2, 'Admin', 'Administrative user, has access to everything.', 0),
(3, 'Inventory_staff', 'Inventory staff privileges', 0),
(7, 'Personel', 'test ', 0),
(8, 'suppliers', 'role given to suppliers in the system', 0),
(9, 'client', 'this role is mainly for clients', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles_users`
--

DROP TABLE IF EXISTS `roles_users`;
CREATE TABLE IF NOT EXISTS `roles_users` (
  `user_id` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `delete_status` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `roles_users_ibfk_4` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles_users`
--

INSERT INTO `roles_users` (`user_id`, `role_id`, `delete_status`) VALUES
(1, 1, 0),
(1, 2, 0),
(2, 1, 0),
(2, 3, 0),
(3, 1, 0),
(3, 2, 0),
(1001, 1, 0),
(1001, 2, 0),
(1002, 1, 0),
(1002, 7, 0),
(1003, 1, 0),
(1003, 9, 0);

-- --------------------------------------------------------

--
-- Table structure for table `subtopics`
--

DROP TABLE IF EXISTS `subtopics`;
CREATE TABLE IF NOT EXISTS `subtopics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` int(11) unsigned NOT NULL,
  `subtopic_name` varchar(255) NOT NULL,
  `subtopic_description` text NOT NULL,
  `subtopic_content` longtext NOT NULL,
  `modify_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='stores metadata about subtopics belonging to a particular chapter' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `logins` int(10) unsigned NOT NULL DEFAULT '0',
  `last_login` int(10) unsigned DEFAULT NULL,
  `delete_status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1004 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `logins`, `last_login`, `delete_status`) VALUES
(1, 'admin@epro.com', 'admin', 'deffc05ea1bc27c89b8bab862b6a60e3b97ae0ad49522452f46d17a47174a99c', 299, 1383584104, 0),
(2, 'kariukie@breakthrough.net', 'evans', '4e4e209a3d8a707dbe9d7bf8828b099a4082ddd2df28a30036c5750faf4d2dea', 491, 1383585686, 0),
(3, 'imadege1990@gmail.com', 'imadege', '102a9a7ed1f0b9e22abeba5c92cce8b1d6ebff31a40e932e589de53d74e620df', 7, 1378185132, 0),
(1001, 'imadege22@gmail.com', 'imadeee', 'b7a31631fdb9b76b780fc28279a1c6dc70868be64e5e7d04fe63e68d1beaa612', 0, NULL, 0),
(1002, 'MeeksE@breakthrough.net', 'meeksE', '840bcff72a133210a897fefd3512c4b57c2b9443937f47a5cc2d6e656c8abd7c', 1, 1379465735, 0),
(1003, 'imadege@gmail.com', 'ian', '6d682de3a9d336b010e254970179245ddc4e49e693a81cace997a070aadd14f2', 0, NULL, 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `permissions_roles`
--
ALTER TABLE `permissions_roles`
  ADD CONSTRAINT `fk_permissions_roles_permissions1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `roles_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_users_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
