-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 30, 2025 at 07:53 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `type` enum('coffee','online') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'coffee',
  `cup_size` json DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `add_ons` json DEFAULT NULL,
  `ingredients` json DEFAULT NULL,
  `special_instruction` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `status` enum('pending','processing','shipped','otw','delivered','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_general_ci NOT NULL,
  `stock` int NOT NULL,
  `unit` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_consumable` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `stock`, `unit`, `status`, `is_consumable`) VALUES
(1, 'Sugar', 247, 'grams', 'active', 1),
(2, 'Ice', 247, 'grams', 'active', 1),
(3, 'Cup Large', 285, 'pieces', 'active', 0),
(4, 'Cup Regular', 297, 'pieces', 'active', 0),
(5, 'Frappe', 277, 'grams', 'active', 1),
(6, 'Cup Small', 292, 'pieces', 'active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`) VALUES
(8, NULL, 'Dean Buckley', 'qwe', 'qwe', 'asd');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','received','preparing','pick-up','on the way','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `type` enum('coffee','religious') COLLATE utf8mb4_general_ci NOT NULL,
  `updated_by_cashier` text COLLATE utf8mb4_general_ci,
  `updated_by_barista` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `receipt` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_walk_in` tinyint(1) NOT NULL DEFAULT '0',
  `placed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `status`, `type`, `updated_by_cashier`, `updated_by_barista`, `receipt`, `is_walk_in`, `placed_on`, `created_at`, `updated_at`) VALUES
(9, 45, 'Roy Joseph Mendoza Latayan', '09512370553', 'royjosephlatayan16@gmail.com', 'gcash', 'Lamot 2 Calauan, Laguna\r\nLamot 2 Calauan, Laguna', 'completed', 'coffee', NULL, NULL, NULL, 0, '2025-09-30 07:32:41', '2025-09-30 05:59:48', '2025-09-30 07:32:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_products`
--

CREATE TABLE `order_products` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cup_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `add_ons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `order_products`
--

INSERT INTO `order_products` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `ingredients`, `cup_sizes`, `add_ons`, `created_at`, `updated_at`) VALUES
(7, 9, 37, 1, '100.00', '105.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"Regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"Regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"Regular\"}}', '{\"size\":\"Regular\",\"price\":5}', '[]', '2025-09-30 05:59:48', '2025-09-30 05:59:48');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `details` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `stock` int DEFAULT '0',
  `image` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `type` enum('coffee','religious') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'coffee',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cup_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `details`, `price`, `status`, `stock`, `image`, `type`, `is_featured`, `ingredients`, `cup_sizes`) VALUES
(24, 'yana', 'Frappe', 'sdasdsd', 100, 'active', 0, 'prod_1745145022.jpg', 'coffee', 0, '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}'),
(30, 'yeye', 'Frappe Extreme', 'sweet', 89, 'active', 0, 'prod_1745071708.jpg', 'coffee', 0, '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}'),
(37, 'Cafe Frappe', 'Frappe', 'idk', 100, 'active', 0, 'prod_1746071881.jpg', 'coffee', 0, '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}'),
(38, 'Frappe 2', 'Frappe', 'asd', 250, 'active', 0, 'prod_1746071904.jpg', 'coffee', 0, '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}'),
(39, 'frappuchino', 'Frappe', 'frappe ng mga Chinese', 300, 'active', 0, 'prod_1746071952.jpg', 'coffee', 0, '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}'),
(40, 'Brusko', 'Espresso', 'N-word Coffee', 100, 'active', 0, 'prod_1746098396.jpg', 'coffee', 0, '[1, 2]', '{\"large\": 20, \"small\": 5}'),
(45, 'Tapioca Pearls', 'Add-ons', 'extra', 20, 'active', 0, 'prod_1748083606.jpg', 'coffee', 0, NULL, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `image` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `image`, `role`) VALUES
(34, 'code and u', 'admin@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', '849e3efc50b82dc788815d2b02d9c7ce.jpg', 'barista'),
(35, 'dominic', 'yanabaho@gmail.conm', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'barista', 'galaxy.png', 'barista'),
(36, 'Gay Man', '1234@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', 'Groot.jpg', 'admin'),
(38, 'admin1', 'admin1@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', 'Groot.jpg', 'cashier'),
(39, 'user', 'user@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', 'Portgas D Ace.jpg', 'user'),
(40, 'Keely Dejesus', 'coniv@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', '479963487_1521305715207066_7193388786170096206_n.jpg', 'user'),
(41, 'Leandra Short', 'boxatesut@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', 'dashboard.jpg', 'user'),
(42, 'Melinda Downs1234', 'muwybave@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', 'Archana+Minev-+15.jpg', 'user'),
(45, 'Roy Joseph Mendoza Latayan', 'royjosephlatayan16@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', 'user', '', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `type` enum('coffee','online') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'coffee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_products`
--
ALTER TABLE `order_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_products`
--
ALTER TABLE `order_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_products`
--
ALTER TABLE `order_products`
  ADD CONSTRAINT `order_products_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
