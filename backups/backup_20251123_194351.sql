-- DATABASE BACKUP: shop_db
-- Generated at: 2025-11-23 19:43:51

DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `size_mb` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `type` enum('coffee','religious') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'coffee',
  `cup_size` json DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `add_ons` json DEFAULT NULL,
  `ingredients` json DEFAULT NULL,
  `special_instruction` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cart` VALUES ('45', '45', '30', '1', 'coffee', '{\"size\": \"regular\", \"price\": 5}', '94.00', NULL, '{\"1\": {\"name\": \"Sugar\", \"level\": \"regular\"}, \"2\": {\"name\": \"Ice\", \"level\": \"regular\"}, \"5\": {\"name\": \"Frappe\", \"level\": \"regular\"}}', NULL);


DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` enum('pending','processing','shipped','otw','delivered','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE `ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_general_ci NOT NULL,
  `stock` int NOT NULL,
  `unit` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_consumable` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ingredients` VALUES ('1', 'Sugar', '233', 'grams', 'active', '1');
INSERT INTO `ingredients` VALUES ('2', 'Ice', '233', 'grams', 'active', '1');
INSERT INTO `ingredients` VALUES ('3', 'Cup Large', '272', 'pieces', 'active', '0');
INSERT INTO `ingredients` VALUES ('4', 'Cup Regular', '296', 'pieces', 'active', '0');
INSERT INTO `ingredients` VALUES ('5', 'Frappe', '276', 'grams', 'active', '1');
INSERT INTO `ingredients` VALUES ('6', 'Cup Small', '292', 'pieces', 'active', '0');


DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `message` VALUES ('8', NULL, 'Dean Buckley', 'qwe', 'qwe', 'asd');


DROP TABLE IF EXISTS `order_products`;
CREATE TABLE `order_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cup_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `add_ons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_products_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_products_chk_1` CHECK (json_valid(`ingredients`)),
  CONSTRAINT `order_products_chk_2` CHECK (json_valid(`cup_sizes`)),
  CONSTRAINT `order_products_chk_3` CHECK (json_valid(`add_ons`))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_products` VALUES ('7', '9', '37', '1', '100.00', '105.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"Regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"Regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"Regular\"}}', '{\"size\":\"Regular\",\"price\":5}', '[]', '2025-09-30 13:59:48', '2025-09-30 13:59:48');
INSERT INTO `order_products` VALUES ('9', '10', '39', '1', '300.00', '325.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"regular\"}}', '{\"size\":\"regular\",\"price\":5}', '{\"45\":{\"name\":\"Tapioca Pearls\",\"price\":20}}', '2025-10-08 16:29:31', '2025-10-08 16:29:31');
INSERT INTO `order_products` VALUES ('10', '10', '40', '13', '100.00', '1820.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"extra\"},\"2\":{\"name\":\"Ice\",\"level\":\"extra\"}}', '{\"size\":\"large\",\"price\":20}', '{\"45\":{\"name\":\"Tapioca Pearls\",\"price\":20}}', '2025-10-08 16:29:31', '2025-10-08 16:29:31');
INSERT INTO `order_products` VALUES ('12', '11', '30', '1', '89.00', '114.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"less\"}}', '{\"size\":\"regular\",\"price\":5}', '{\"45\":{\"name\":\"Tapioca Pearls\",\"price\":20}}', '2025-10-11 00:07:15', '2025-10-11 00:07:15');
INSERT INTO `order_products` VALUES ('13', '11', '37', '2', '100.00', '250.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"extra\"},\"2\":{\"name\":\"Ice\",\"level\":\"extra\"},\"5\":{\"name\":\"Frappe\",\"level\":\"extra\"}}', '{\"size\":\"regular\",\"price\":5}', '{\"45\":{\"name\":\"Tapioca Pearls\",\"price\":20}}', '2025-10-11 00:07:15', '2025-10-11 00:07:15');
INSERT INTO `order_products` VALUES ('14', '11', '24', '1', '100.00', '105.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"regular\"}}', '{\"size\":\"regular\",\"price\":5}', '[]', '2025-10-11 00:07:15', '2025-10-11 00:07:15');
INSERT INTO `order_products` VALUES ('16', '13', '24', '1', '100.00', '105.00', '{\"1\":{\"name\":\"Sugar\",\"level\":\"Regular\"},\"2\":{\"name\":\"Ice\",\"level\":\"Regular\"},\"5\":{\"name\":\"Frappe\",\"level\":\"Regular\"}}', '{\"size\":\"Regular\",\"price\":5}', '[]', '2025-11-13 02:47:05', '2025-11-13 02:47:05');


DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','received','preparing','pick-up','on the way','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `type` enum('coffee','religious') COLLATE utf8mb4_general_ci NOT NULL,
  `delivery_fee` int NOT NULL DEFAULT '0',
  `updated_by_cashier` text COLLATE utf8mb4_general_ci,
  `updated_by_barista` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `receipt` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_walk_in` tinyint(1) NOT NULL DEFAULT '0',
  `placed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` VALUES ('9', '45', 'Roy Joseph Mendoza Latayan', '09512370553', 'royjosephlatayan16@gmail.com', 'gcash', 'Lamot 2 Calauan, Laguna
Lamot 2 Calauan, Laguna', 'completed', 'coffee', '10', NULL, NULL, 'receipt_38_20251112_174953_6914c8c1547c7.pdf', '0', '2025-11-13 01:49:53', '2025-09-30 13:59:48', '2025-11-13 01:49:53');
INSERT INTO `orders` VALUES ('10', '45', 'Roy Joseph Mendoza Latayan', '09512370553', 'royjosephlatayan16@gmail.com', 'cash on delivery', 'Lamot 2 Calauan, Laguna
Lamot 2 Calauan, Laguna', 'completed', 'coffee', '10', NULL, 'dominic', NULL, '0', '2025-11-13 04:22:29', '2025-10-08 16:29:31', '2025-11-13 04:22:29');
INSERT INTO `orders` VALUES ('11', '45', 'Roy Joseph Mendoza Latayan', '09512370553', 'royjosephlatayan16@gmail.com', 'cash on delivery', '3920 - tapat ng sdadwwsd, Lamot 2, ', 'completed', 'coffee', '10', NULL, NULL, NULL, '0', '2025-11-13 04:22:35', '2025-10-11 00:07:15', '2025-11-13 04:22:35');
INSERT INTO `orders` VALUES ('13', '38', 'Walk-in Customer', '', '', 'cash', 'N/A', 'completed', 'coffee', '0', 'dominic', NULL, '', '1', '2025-11-13 04:01:44', '2025-11-13 02:47:05', '2025-11-13 04:01:44');


DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `cup_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  CONSTRAINT `products_chk_1` CHECK (json_valid(`ingredients`)),
  CONSTRAINT `products_chk_2` CHECK (json_valid(`cup_sizes`))
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES ('24', 'yana', 'Frappe', 'sdasdsd', '100', 'active', '0', 'prod_1745145022.jpg', 'coffee', '0', '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}');
INSERT INTO `products` VALUES ('30', 'yeye', 'Frappe Extreme', 'sweet', '89', 'active', '0', 'prod_1745071708.jpg', 'coffee', '0', '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}');
INSERT INTO `products` VALUES ('37', 'Cafe Frappe', 'Frappe', 'idk', '100', 'active', '0', 'prod_1746071881.jpg', 'coffee', '0', '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}');
INSERT INTO `products` VALUES ('38', 'Frappe 2', 'Frappe', 'asd', '250', 'active', '0', 'prod_1746071904.jpg', 'coffee', '0', '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}');
INSERT INTO `products` VALUES ('39', 'frappuchino', 'Frappe', 'frappe ng mga Chinese', '300', 'active', '0', 'prod_1746071952.jpg', 'coffee', '0', '[1, 2, 5]', '{\"large\": 10, \"small\": 2, \"regular\": 5}');
INSERT INTO `products` VALUES ('40', 'Brusko', 'Espresso', 'N-word Coffee', '100', 'active', '0', 'prod_1746098396.jpg', 'coffee', '0', '[1, 2]', '{\"large\": 20, \"small\": 5}');
INSERT INTO `products` VALUES ('45', 'Tapioca Pearls', 'Add-ons', 'extra', '20', 'active', '0', 'prod_1748083606.jpg', 'coffee', '0', NULL, '[]');
INSERT INTO `products` VALUES ('49', 'Illana Bailey', 'Angels', 'qweqwe', '333', 'active', '231', 'prod_1759289051.png', 'religious', '0', NULL, NULL);


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `image` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('34', 'code and u', 'admin@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', '849e3efc50b82dc788815d2b02d9c7ce.jpg', 'barista');
INSERT INTO `users` VALUES ('35', 'dominic', 'yanabaho@gmail.conm', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'barista', 'galaxy.png', 'barista');
INSERT INTO `users` VALUES ('36', 'Gay Man', '1234@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', 'Groot.jpg', 'admin');
INSERT INTO `users` VALUES ('38', 'admin1', 'admin1@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', 'Groot.jpg', 'cashier');
INSERT INTO `users` VALUES ('39', 'user', 'user@gmail.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', 'Portgas D Ace.jpg', 'user');
INSERT INTO `users` VALUES ('40', 'Keely Dejesus', 'coniv@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', '479963487_1521305715207066_7193388786170096206_n.jpg', 'user');
INSERT INTO `users` VALUES ('41', 'Leandra Short', 'boxatesut@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', 'dashboard.jpg', 'user');
INSERT INTO `users` VALUES ('42', 'Melinda Downs1234', 'muwybave@mailinator.com', '$2y$10$JVlPT.omgV1w4u9a4GTMUe3BrTcFP6pV68sdvt9ZblS0dIkOSPZZe', '2025-11-23 11:01:31', 'user', 'Archana+Minev-+15.jpg', 'user');
INSERT INTO `users` VALUES ('50', 'Roy Joseph Mendoza Latayan', 'royjosephlatayan16@gmail.com', '$2y$10$b2eBvNcWBb2RRGmXbET6PuCOcyo4GVquhIiGagzVxyfBZfTamuxTi', '2025-11-23 11:54:50', 'user', 'IMG_6922857a520c03.82556896.jpg', 'user');


DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `type` enum('coffee','online') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'coffee',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



