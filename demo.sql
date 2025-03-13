-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2025 at 12:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` varchar(12) NOT NULL,
  `persmission` tinyint(1) DEFAULT NULL, 
  `designation` text DEFAULT NULL
  PRIMARY KEY (`user_id`)
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_request`
--

CREATE TABLE `borrow_request` (
  `req_id` int(11) NOT NULL,
  `purose` text NOT NULL,
  `quantitiy` int(11) NOT NULL,
  `req_date` date NOT NULL,
  `Duration` time NOT NULL,
  `conditions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL AUTOINCREMENT,
  `cat_name` text NOT NULL,
  `descirption` text NOT NULL,
  `cat_parent` int(11) DEFAULT NULL
  
  PRIMARY KEY (`cat_id`) 
  FOREIGN KEY (`cat_parent`) REFERENCES `categories` (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `damage`
--


-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE `generals` (
  `user_id` varchar(12) NOT NULL,
  `gender` text NOT NULL,
  `date_of_birth` date NOT NULL
  PRIMARY KEY (`user_id`)
   FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ---CREATE TABLE `location` (
--   `location_id` int(11) NOT NULL,
--   `building_name` text NOT NULL,
--   `room_no` int(11) NOT NULL
-- --) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTOINCREMENT,
  
  `item_name` text NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `status` enum("acitve","inactive") DEFAULT "active",
  `item_image` varchar(500) DEFAULT "default.jpg",
  `item_description` text NOT NULL,
  `cat_id` int(11) NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  PRIMARY KEY (`item_id`)
  FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`)
 
  
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
CREATE TABLE `damage` (
  `damage_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `reason_type` text NOT NULL,
  `damage_date` date NOT NULL,
  `details` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `location`
--


-- --------------------------------------------------------

--
-- Table structure for table `puchase_order`
--

CREATE TABLE `puchase_order` (
  `purch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` varchar(12) NOT NULL,
  `order_date` date NOT NULL,
  `status` text NOT NULL,
  `total_cost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `return_condition` text NOT NULL,
  `penalty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transact_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transact_date` date NOT NULL,
  `type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(12) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  
  `last_name` varchar(50) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL,
  `vendor_name` text NOT NULL,
  `contact` decimal(10,0) NOT NULL,
  `email` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `borrow_request`
--
ALTER TABLE `borrow_request`
  ADD PRIMARY KEY (`req_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `damage`
--
ALTER TABLE `damage`
  ADD PRIMARY KEY (`damage_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `general`
--
ALTER TABLE `general`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `puchase_order`
--
ALTER TABLE `puchase_order`
  ADD PRIMARY KEY (`purch_id`,`item_id`,`user_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transact_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrow_request`
--
ALTER TABLE `borrow_request`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `damage`
--
ALTER TABLE `damage`
  MODIFY `damage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puchase_order`
--
ALTER TABLE `puchase_order`
  MODIFY `purch_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `damage`
--
ALTER TABLE `damage`
  ADD CONSTRAINT `damage_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `general`
--
ALTER TABLE `general`
  ADD CONSTRAINT `general_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `puchase_order`
--
ALTER TABLE `puchase_order`
  ADD CONSTRAINT `puchase_order_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `puchase_order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `admin` (`user_id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
