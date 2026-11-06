-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 11:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shoopmoda`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon` varchar(50) DEFAULT NULL COMMENT 'nom d''icône du sprite SVG',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Femme', 'femme', 'dress', 1, 1, '2026-11-05 17:04:33'),
(2, 'Enfants', 'enfants', 'tshirt', 2, 1, '2026-11-05 17:04:33'),
(3, 'Pyjamas & Ensembles', 'pyjamas', 'moon', 3, 1, '2026-11-05 17:04:33'),
(4, 'Robes', 'robes', 'jacket', 4, 1, '2026-11-05 17:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '["S","M","L"]' CHECK (json_valid(`sizes`)),
  `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '[{"name":"Marron","hex":"#704F38"}]' CHECK (json_valid(`colors`)),
  `is_flash` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `title`, `slug`, `description`, `price`, `sale_price`, `rating`, `stock`, `sizes`, `colors`, `is_flash`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 'Ensemble pyjama satin « Istanbul »', 'ensemble-pyjama-satin-istanbul', 'Ensemble deux pièces en satin fluide, finitions soignées et ceinture élastiquée. Confort léger pour les soirées et les matins tranquilles.', 299.00, 249.00, 4.9, 14, '[\"S\",\"M\",\"L\",\"XL\",\"XXL\"]', '[{\"name\":\"Marron\",\"hex\":\"#704F38\"},{\"name\":\"Beige\",\"hex\":\"#C2A691\"},{\"name\":\"Noir\",\"hex\":\"#1F2029\"}]', 1, 1, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(2, 3, 'Pyjama coton « Bosphore »', 'pyjama-coton-bosphore', 'Pyjama en coton doux, coupe ample et respirante. Idéal pour dormir confortablement en toute saison.', 229.00, NULL, 4.8, 20, '[\"S\",\"M\",\"L\",\"XL\"]', '[{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"},{\"name\":\"Marron\",\"hex\":\"#704F38\"}]', 1, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(3, 4, 'Robe longue « Sultan »', 'robe-longue-sultan', 'Robe longue à la coupe fluide, manches longues et ceinture assortie. Une pièce élégante pour les occasions spéciales.', 349.00, NULL, 5.0, 8, '[\"S\",\"M\",\"L\",\"XL\"]', '[{\"name\":\"Noir\",\"hex\":\"#1F2029\"},{\"name\":\"Beige\",\"hex\":\"#C2A691\"}]', 0, 1, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(4, 4, 'Robe plissée « Élégance »', 'robe-plissee-elegance', 'Robe midi plissée au tombé léger. Se porte facilement au quotidien comme en soirée.', 299.00, 249.00, 4.7, 10, '[\"S\",\"M\",\"L\",\"XL\",\"XXL\"]', '[{\"name\":\"Marron\",\"hex\":\"#704F38\"},{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"}]', 1, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(5, 1, 'Chemise en soie « Perle »', 'chemise-soie-perle', 'Chemise en soie au toucher satiné, col classique et boutons nacrés. Parfaite avec un pantalon taille haute.', 259.00, NULL, 4.8, 12, '[\"S\",\"M\",\"L\",\"XL\"]', '[{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"},{\"name\":\"Beige\",\"hex\":\"#C2A691\"}]', 0, 1, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(6, 1, 'Pantalon taille haute « Moda »', 'pantalon-taille-haute-moda', 'Pantalon taille haute à jambe droite, matière stretch confortable. Une base indispensable du dressing.', 179.00, NULL, 4.6, 25, '[\"S\",\"M\",\"L\",\"XL\",\"XXL\"]', '[{\"name\":\"Noir\",\"hex\":\"#1F2029\"},{\"name\":\"Marron\",\"hex\":\"#704F38\"}]', 0, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(7, 1, 'Trench « Automne »', 'trench-automne', 'Trench mi-long à ceinture, doublure légère et poches passepoilées. La pièce de mi-saison.', 429.00, 379.00, 4.9, 6, '[\"S\",\"M\",\"L\",\"XL\"]', '[{\"name\":\"Beige\",\"hex\":\"#C2A691\"},{\"name\":\"Noir\",\"hex\":\"#1F2029\"}]', 0, 1, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(8, 1, 'T-shirt coton « Douceur »', 't-shirt-coton-douceur', 'T-shirt en coton peigné, col rond et coupe droite. Doux, simple, à porter tous les jours.', 99.00, NULL, 4.5, 40, '[\"S\",\"M\",\"L\",\"XL\",\"XXL\"]', '[{\"name\":\"Blanc\",\"hex\":\"#FFFFFF\"},{\"name\":\"Marron\",\"hex\":\"#704F38\"}]', 0, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(9, 2, 'Ensemble enfant « Petit Prince »', 'ensemble-enfant-petit-prince', 'Ensemble deux pièces pour enfant, coton doux et ceinture élastiquée. Confort toute la journée.', 149.00, NULL, 4.8, 18, '[\"2-3 ans\",\"4-5 ans\",\"6-7 ans\",\"8-9 ans\"]', '[{\"name\":\"Bleu nuit\",\"hex\":\"#1F2029\"},{\"name\":\"Beige\",\"hex\":\"#C2A691\"}]', 0, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(10, 2, 'Pyjama enfant « Étoile »', 'pyjama-enfant-etoile', 'Pyjama enfant en coton respirant, motifs discrets et poignets doux. Pensé pour bien dormir.', 129.00, 109.00, 4.9, 22, '[\"2-3 ans\",\"4-5 ans\",\"6-7 ans\",\"8-9 ans\"]', '[{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"},{\"name\":\"Marron\",\"hex\":\"#704F38\"}]', 1, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(11, 2, 'Robe enfant « Fleur »', 'robe-enfant-fleur', 'Robe enfant légère à manches courtes, doublure coton. Jolie pour les fêtes de famille.', 169.00, NULL, 4.7, 15, '[\"2-3 ans\",\"4-5 ans\",\"6-7 ans\"]', '[{\"name\":\"Beige\",\"hex\":\"#C2A691\"},{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"}]', 0, 0, 1, '2026-11-05 17:04:33', '2026-11-05 17:04:33'),
(12, 3, 'Ensemble famille « Cocooning »', 'ensemble-famille-cocooning', 'Ensemble coordonné pour elle et les enfants, matière douce et chaude. Parfait pour les moments à la maison.', 399.00, 349.00, 5.0, 9, '[\"S\",\"M\",\"L\",\"XL\",\"XXL\"]', '[{\"name\":\"Beige\",\"hex\":\"#C2A691\"},{\"name\":\"Marron\",\"hex\":\"#704F38\"},{\"name\":\"Crème\",\"hex\":\"#EFE7DE\"}]', 1, 1, 1, '2026-11-05 21:23:35', '2026-11-05 21:23:35'),
(15, 2, 'yakuza', 'yakuza', 'it\'s yakuza btw', 100.00, 2.00, 5.0, 20, '[\"S\",\"M\"]', '[{\"name\":\"Violet\",\"hex\":\"#dc00ff\"},{\"name\":\"Noir\",\"hex\":\"#000000\"}]', 1, 0, 1, '2026-11-05 21:34:21', '2026-11-05 21:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `is_primary`) VALUES
(1, 1, 'assets/images/placeholders/ensemble-pyjama-satin-istanbul.svg', 1, 1),
(2, 1, 'assets/images/placeholders/ensemble-pyjama-satin-istanbul-2.svg', 2, 0),
(3, 2, 'assets/images/placeholders/pyjama-coton-bosphore.svg', 1, 1),
(4, 2, 'assets/images/placeholders/pyjama-coton-bosphore-2.svg', 2, 0),
(5, 3, 'assets/images/placeholders/robe-longue-sultan.svg', 1, 1),
(6, 3, 'assets/images/placeholders/robe-longue-sultan-2.svg', 2, 0),
(7, 4, 'assets/images/placeholders/robe-plissee-elegance.svg', 1, 1),
(8, 4, 'assets/images/placeholders/robe-plissee-elegance-2.svg', 2, 0),
(9, 5, 'assets/images/placeholders/chemise-soie-perle.svg', 1, 1),
(10, 5, 'assets/images/placeholders/chemise-soie-perle-2.svg', 2, 0),
(11, 6, 'assets/images/placeholders/pantalon-taille-haute-moda.svg', 1, 1),
(12, 6, 'assets/images/placeholders/pantalon-taille-haute-moda-2.svg', 2, 0),
(13, 7, 'assets/images/placeholders/trench-automne.svg', 1, 1),
(14, 7, 'assets/images/placeholders/trench-automne-2.svg', 2, 0),
(15, 8, 'assets/images/placeholders/t-shirt-coton-douceur.svg', 1, 1),
(16, 8, 'assets/images/placeholders/t-shirt-coton-douceur-2.svg', 2, 0),
(17, 9, 'assets/images/placeholders/ensemble-enfant-petit-prince.svg', 1, 1),
(18, 9, 'assets/images/placeholders/ensemble-enfant-petit-prince-2.svg', 2, 0),
(19, 10, 'assets/images/placeholders/pyjama-enfant-etoile.svg', 1, 1),
(20, 10, 'assets/images/placeholders/pyjama-enfant-etoile-2.svg', 2, 0),
(21, 11, 'assets/images/placeholders/robe-enfant-fleur.svg', 1, 1),
(22, 11, 'assets/images/placeholders/robe-enfant-fleur-2.svg', 2, 0),
(25, 12, 'assets/images/placeholders/ensemble-famille-cocooning.svg', 1, 1),
(26, 12, 'assets/images/placeholders/ensemble-famille-cocooning-2.svg', 2, 0),
(29, 15, 'uploads/products/pe3e93223e9657fa9.png', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount_type`, `discount_value`, `min_order`, `max_uses`, `used_count`, `is_active`, `expires_at`) VALUES
(1, 'BIENVENUE', 'percent', 10.00, 200.00, 100, 0, 1, '2027-12-31 23:59:59');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'store_name', 'Shoop Moda Style'),
(2, 'currency', 'DH'),
(3, 'whatsapp_number', '212725605520'),
(4, 'delivery_fee_economy', '25.00'),
(5, 'delivery_fee_express', '45.00'),
(6, 'shipping_eta_economy', '3 à 5 jours ouvrables'),
(7, 'shipping_eta_express', '24 à 48 heures'),
(8, 'flash_sale_end', '2027-01-01 23:59:00'),
(16, 'delivery_fee', '25.00'),
(17, 'announcement_text', 'Livraison rapide partout au Maroc');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`),
  ADD KEY `idx_categories_active_sort` (`is_active`,`sort_order`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_slug` (`slug`),
  ADD KEY `idx_products_active_flash` (`is_active`,`is_flash`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_created` (`created_at`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_images_product` (`product_id`,`is_primary`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_promo_code` (`code`),
  ADD KEY `idx_promo_active` (`is_active`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
