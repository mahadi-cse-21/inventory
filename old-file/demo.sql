--
-- Database: `database_course`
--

-- --------------------------------------------------------
--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL PRIMARY KEY,
  `first_name` varchar(50) NOT NULL,  
  `last_name` varchar(50) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- ------------------------------------------



--
-- Table structure for table `admin`
--



CREATE TABLE `admins` (
  `user_id` varchar(20) NOT NULL,
  `permission` tinyint(1) DEFAULT NULL, 
  `designation` VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
);

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE `generals` (
  `user_id` varchar(20) NOT NULL,
  `gender` text NOT NULL,
  `date_of_birth` date NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
   FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ;

-- -------------------------------
--
-- Table structure for table `category`
--
CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `cat_parent` int(11) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cat_id`),
  FOREIGN KEY (`cat_parent`) REFERENCES `categories` (`cat_id`)
);



--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,  
  `item_name` VARCHAR(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `item_image` VARCHAR(500) DEFAULT 'default.jpg',
  `item_description` TEXT NOT NULL,
  `cat_id` int(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`)
);



-- --------------------------------------------------------
CREATE TABLE `damage` (
  `damage_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `reason_type` VARCHAR(255) NOT NULL,
  `damage_date` DATE NOT NULL,
  `details` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`damage_id`),
  FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- Create borrow_requests table
CREATE TABLE borrow_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL,
    status ENUM('cart', 'pending', 'approved', 'rejected') DEFAULT 'cart',
    req_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    purpose VARCHAR(500) DEFAULT NULL,
    duration date DEFAULT NULL, -- Changed from DATE to INT for better duration handling
    returned_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) -- Ensure this column exists in the users table
);


CREATE TABLE borrow_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (request_id) REFERENCES borrow_requests(request_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- CREATE TABLE borrow_request_status (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     request_id INT NOT NULL,
--     status enum('pending', 'approved', 'rejected') DEFAULT 'pending',
--     FOREIGN KEY (request_id) REFERENCES borrow_requests(request_id)
-- );

-- --------------------------------------------------------




--
-- Table structure for table `borrow_request`
--


-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `damage`
--



-- --------------------------------------------------------
-- ---CREATE TABLE `location` (
--   `location_id` int(11) NOT NULL,
--   `building_name` text NOT NULL,
--   `room_no` int(11) NOT NULL
-- --) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Table structure for table `location`
--


-- --------------------------------------------------------

--
-- Table structure for table `puchase_order`
--

-- CREATE TABLE `puchase_order` (
--   `purch_id` int(11) NOT NULL,
--   `item_id` int(11) NOT NULL,
--   `user_id` varchar(12) NOT NULL,
--   `order_date` date NOT NULL,
--   `status` text NOT NULL,
--   `total_cost` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `returns`
-- --

-- CREATE TABLE `returns` (
--   `return_id` int(11) NOT NULL,
--   `item_id` int(11) NOT NULL,
--   `return_date` date NOT NULL,
--   `return_condition` text NOT NULL,
--   `penalty` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `transactions`
-- --

-- CREATE TABLE `transactions` (
--   `transact_id` int(11) NOT NULL,
--   `item_id` int(11) NOT NULL,
--   `transact_date` date NOT NULL,
--   `type` text NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -- --------------------------------------------------------


-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `vendor`
-- --

-- CREATE TABLE `vendor` (
--   `vendor_id` int(11) NOT NULL,
--   `vendor_name` text NOT NULL,
--   `contact` decimal(10,0) NOT NULL,
--   `email` varchar(25) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --
-- -- Indexes for dumped tables
-- --

-- --
-- -- Indexes for table `admin`
-- --
-- ALTER TABLE `admin`
--   ADD PRIMARY KEY (`user_id`);

-- --
-- -- Indexes for table `borrow_request`
-- --
-- ALTER TABLE `borrow_request`
--   ADD PRIMARY KEY (`req_id`);

-- --
-- -- Indexes for table `category`
-- --
-- ALTER TABLE `category`
--   ADD PRIMARY KEY (`cat_id`);

-- --
-- -- Indexes for table `damage`
-- --
-- ALTER TABLE `damage`
--   ADD PRIMARY KEY (`damage_id`,`item_id`),
--   ADD KEY `item_id` (`item_id`);

-- --
-- -- Indexes for table `general`
-- --
-- ALTER TABLE `general`
--   ADD PRIMARY KEY (`user_id`);

-- --
-- -- Indexes for table `items`
-- --
-- ALTER TABLE `items`
--   ADD PRIMARY KEY (`item_id`);

-- --
-- -- Indexes for table `location`
-- --
-- ALTER TABLE `location`
--   ADD PRIMARY KEY (`location_id`,`item_id`),
--   ADD KEY `item_id` (`item_id`);

-- --
-- -- Indexes for table `puchase_order`
-- --
-- ALTER TABLE `puchase_order`
--   ADD PRIMARY KEY (`purch_id`,`item_id`,`user_id`),
--   ADD KEY `item_id` (`item_id`),
--   ADD KEY `user_id` (`user_id`);

-- --
-- -- Indexes for table `returns`
-- --
-- ALTER TABLE `returns`
--   ADD PRIMARY KEY (`return_id`,`item_id`),
--   ADD KEY `item_id` (`item_id`);

-- --
-- -- Indexes for table `transactions`
-- --
-- ALTER TABLE `transactions`
--   ADD PRIMARY KEY (`transact_id`,`item_id`),
--   ADD KEY `item_id` (`item_id`);

-- --
-- -- Indexes for table `users`
-- --
-- ALTER TABLE `users`
--   ADD PRIMARY KEY (`user_id`);

-- --
-- -- Indexes for table `vendor`
-- --
-- ALTER TABLE `vendor`
--   ADD PRIMARY KEY (`vendor_id`);

-- --
-- -- AUTO_INCREMENT for dumped tables
-- --

-- --
-- -- AUTO_INCREMENT for table `borrow_request`
-- --
-- ALTER TABLE `borrow_request`
--   MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `category`
-- --
-- ALTER TABLE `category`
--   MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `damage`
-- --
-- ALTER TABLE `damage`
--   MODIFY `damage_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `items`
-- --
-- ALTER TABLE `items`
--   MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

-- --
-- -- AUTO_INCREMENT for table `location`
-- --
-- ALTER TABLE `location`
--   MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `puchase_order`
-- --
-- ALTER TABLE `puchase_order`
--   MODIFY `purch_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `returns`
-- --
-- ALTER TABLE `returns`
--   MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `transactions`
-- --
-- ALTER TABLE `transactions`
--   MODIFY `transact_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- AUTO_INCREMENT for table `vendor`
-- --
-- ALTER TABLE `vendor`
--   MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT;

-- --
-- -- Constraints for dumped tables
-- --

-- --
-- -- Constraints for table `admin`
-- --
-- ALTER TABLE `admin`
--   ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

-- --
-- -- Constraints for table `damage`
-- --
-- ALTER TABLE `damage`
--   ADD CONSTRAINT `damage_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

-- --
-- -- Constraints for table `general`
-- --
-- ALTER TABLE `general`
--   ADD CONSTRAINT `general_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

-- --
-- -- Constraints for table `location`
-- --
-- ALTER TABLE `location`
--   ADD CONSTRAINT `location_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

-- --
-- -- Constraints for table `puchase_order`
-- --
-- ALTER TABLE `puchase_order`
--   ADD CONSTRAINT `puchase_order_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
--   ADD CONSTRAINT `puchase_order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `admin` (`user_id`);

-- --
-- -- Constraints for table `returns`
-- --
-- ALTER TABLE `returns`
--   ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

-- --
-- -- Constraints for table `transactions`
-- --
-- ALTER TABLE `transactions`
--   ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
-- COMMIT;

-- /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
-- /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
-- /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
