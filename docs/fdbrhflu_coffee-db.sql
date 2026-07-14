-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2026 at 01:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fdbrhflu_coffee-db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','editor','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Quản trị viên', 'admin@tienhadrinks.vn', '$2y$10$abcdefghijklmnopqrstuv', 'super_admin', 1, '2026-07-07 20:48:33', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(220) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `author_name` varchar(120) NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `thumbnail`, `author_name`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, '5 lý do nên chọn nước ép tươi mỗi ngày', '5-ly-do-nen-chon-nuoc-ep-tuoi-moi-ngay', 'Nước ép tươi giúp bổ sung vitamin và năng lượng nhanh.', 'Nội dung bài viết chi tiết về lợi ích của nước ép tươi, cách kết hợp nguyên liệu và thời điểm uống phù hợp.', 'https://images.unsplash.com/photo-1553530979-fbb9e4aee36f', 'Tienha Team', 'published', '2026-06-10 09:30:00', '2026-07-07 20:48:33', '2026-07-07 20:48:33'),
(2, 'Bí quyết giữ vị ngon cho cà phê đá xay', 'bi-quyet-giu-vi-ngon-cho-ca-phe-da-xay', 'Hướng dẫn chọn hạt, xây dựng công thức và canh nhiệt độ.', 'Nội dung bài viết chia sẻ cách cân đối giữa độ đậm và độ ngọt trong món cà phê đá xay.', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 'Tienha Team', 'published', '2026-06-22 14:00:00', '2026-07-07 20:48:33', '2026-07-07 20:48:33'),
(3, 'Combo bữa sáng nhanh cho dân văn phòng', 'combo-bua-sang-nhanh-cho-dan-van-phong', 'Gợi ý combo smoothie + yogurt cho buổi sáng đầy năng lượng.', 'Nội dung bài viết gợi ý các combo đơn giản, dễ mang đi và đảm bảo chất dinh dưỡng.', 'https://images.unsplash.com/photo-1488477181946-6428a0291777', 'Tienha Team', 'published', '2026-07-01 08:00:00', '2026-07-07 20:48:33', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Cà phê', 'ca-phe', 'Cà phê đen đá, bạc xỉu, latte và blend signature.', '/demo/tienha-drinks/assets/img/products/category_1.jpg', 1, 1, '2026-07-07 20:48:33'),
(2, 'Nước ép', 'nuoc-ep', 'Nước ép trái cây tươi trong ngày.', 'https://images.unsplash.com/photo-1600271886742-f049cd5bba3f', 2, 1, '2026-07-07 20:48:33'),
(3, 'Sinh tố', 'sinh-to', 'Sinh tố đậm vị, bổ sung năng lượng.', 'https://images.unsplash.com/photo-1553530666-ba11a90b8c74', 3, 1, '2026-07-07 20:48:33'),
(4, 'Trà trái cây', 'tra-trai-cay', 'Trà thanh mát kết hợp topping trái cây.', '/demo/tienha-drinks/assets/img/products/category_4.jpg', 4, 1, '2026-07-07 20:48:33'),
(5, 'Sữa chua', 'sua-chua', 'Sữa chua mix trái cây và granola.', '/demo/tienha-drinks/assets/img/products/category_5.jpg', 5, 1, '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','processing','done') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `full_name`, `phone`, `email`, `message`, `status`, `created_at`) VALUES
(1, 'Phạm Nhật Linh', '0908000010', 'linh.pham@example.com', 'Tôi muốn đặt 20 ly cho sự kiện văn phòng vào thứ 6.', 'new', '2026-07-05 11:00:00'),
(2, 'Đỗ Minh Quân', '0908000011', NULL, 'Cho mình xin bảng giá giao số lượng lớn cho khách đoàn.', 'processing', '2026-07-06 08:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `title` varchar(120) NOT NULL,
  `discount_type` enum('fixed','percent') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `title`, `discount_type`, `discount_value`, `min_order_value`, `start_at`, `end_at`, `usage_limit`, `used_count`, `is_active`, `created_at`) VALUES
(1, 'HELLOTIENHA', 'Giảm cho khách mới', 'percent', 15.00, 100000.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 3000, 124, 1, '2026-07-07 20:48:33'),
(2, 'FREESHIP300', 'Miễn phí giao hàng từ 300K', 'fixed', 20000.00, 300000.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', NULL, 332, 1, '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `tier_id` int(10) UNSIGNED DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `total_spending` decimal(12,2) NOT NULL DEFAULT 0.00,
  `default_address` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `phone`, `password_hash`, `tier_id`, `points`, `total_spending`, `default_address`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Minh Thu', 'thu.nguyen@example.com', '0908000001', '$2y$10$abcdefghijklmnopqrstuv', 2, 260, 2850000.00, '45 Nguyễn Huệ, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-07 20:48:33'),
(2, 'Trần Hoàng Nam', 'nam.tran@example.com', '0908000002', '$2y$10$abcdefghijklmnopqrstuv', 1, 90, 780000.00, '21 Lê Lai, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-07 20:48:33'),
(3, 'Lê Thu Hà', 'ha.le@example.com', '0908000003', '$2y$10$abcdefghijklmnopqrstuv', 3, 520, 6120000.00, '99 Bùi Viện, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `customer_tiers`
--

CREATE TABLE `customer_tiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `min_spending` decimal(12,2) NOT NULL DEFAULT 0.00,
  `benefits` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_tiers`
--

INSERT INTO `customer_tiers` (`id`, `name`, `min_spending`, `benefits`, `created_at`) VALUES
(1, 'Đồng', 0.00, 'Tích 1 điểm/10.000 VND', '2026-07-07 20:48:33'),
(2, 'Bạc', 2000000.00, 'Tăng 5% cho đơn từ 200.000 VND', '2026-07-07 20:48:33'),
(3, 'Vàng', 5000000.00, 'Tăng 10% + ưu tiên giao hàng', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(40) NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(180) DEFAULT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `payment_method` enum('cod','bank_transfer','momo') NOT NULL DEFAULT 'cod',
  `status` enum('pending','confirmed','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `final_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `final_total`, `note`, `created_at`, `updated_at`) VALUES
(1, 'TH20260701001', 1, 'Nguyễn Minh Thu', '0908000001', 'thu.nguyen@example.com', '45 Nguyễn Huệ, Q1, TP.HCM', 'momo', 'completed', 152000.00, 20000.00, 22000.00, 150000.00, 'Giao giờ hành chính', '2026-07-01 10:15:00', '2026-07-07 20:48:33'),
(2, 'TH20260702001', 2, 'Trần Hoàng Nam', '0908000002', 'nam.tran@example.com', '21 Lê Lai, Q1, TP.HCM', 'cod', 'shipping', 98000.00, 20000.00, 0.00, 118000.00, NULL, '2026-07-02 15:20:00', '2026-07-07 20:48:33'),
(3, 'TH20260703001', 3, 'Lê Thu Hà', '0908000003', 'ha.le@example.com', '99 Bùi Viện, Q1, TP.HCM', 'bank_transfer', 'confirmed', 224000.00, 0.00, 15000.00, 209000.00, NULL, '2026-07-03 09:40:00', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `line_total`, `created_at`) VALUES
(1, 1, 1, 'Cà phê đen đá', 29000.00, 2, 58000.00, '2026-07-07 20:48:33'),
(2, 1, 4, 'Nước ép cam dứa', 43000.00, 2, 86000.00, '2026-07-07 20:48:33'),
(3, 1, 9, 'Trà đào cam sả', 42000.00, 1, 42000.00, '2026-07-07 20:48:33'),
(4, 2, 2, 'Bạc xỉu ngọc trai', 39000.00, 1, 39000.00, '2026-07-07 20:48:33'),
(5, 2, 8, 'Sinh tố dâu chuối', 58000.00, 1, 58000.00, '2026-07-07 20:48:33'),
(6, 3, 5, 'Nước ép ổi kiwi', 52000.00, 2, 104000.00, '2026-07-07 20:48:33'),
(7, 3, 7, 'Sinh tố bơ xoài', 51000.00, 2, 102000.00, '2026-07-07 20:48:33'),
(8, 3, 10, 'Sữa chua granola trái cây', 59000.00, 1, 59000.00, '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `points_transactions`
--

CREATE TABLE `points_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('earn','redeem','adjust') NOT NULL,
  `points` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points_transactions`
--

INSERT INTO `points_transactions` (`id`, `customer_id`, `order_id`, `type`, `points`, `note`, `created_at`) VALUES
(1, 1, 1, 'earn', 15, 'Cộng điểm đơn TH20260701001', '2026-07-07 20:48:33'),
(2, 2, 2, 'earn', 11, 'Cộng điểm đơn TH20260702001', '2026-07-07 20:48:33'),
(3, 3, 3, 'earn', 20, 'Cộng điểm đơn TH20260703001', '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sku` varchar(40) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `thumbnail` varchar(255) DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `nutrition_info` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `price`, `sale_price`, `stock`, `thumbnail`, `short_description`, `description`, `nutrition_info`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cà phê đen đá', 'ca-phe-den-da', 'CF001', 32000.00, 29000.00, 120, '/demo/tienha-drinks/assets/img/products/product_1.jpg', 'Vị đậm, thơm mạnh, ít ngọt.', 'Cà phê nguyên chất phơi đá viên, phù hợp cho ngày cần tỉnh táo.', 'Calories: 40 | Đường: 6g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:11'),
(2, 1, 'Bạc xỉu ngọc trai', 'bac-xiu-ngoc-trai', 'CF002', 42000.00, 39000.00, 90, '/demo/tienha-drinks/assets/img/products/product_2.jpg', 'Sữa nhiều hơn cà phê, vị nhẹ.', 'Bạc xỉu phối sữa tươi và top cream mịn, dễ uống cho người mới bắt đầu.', 'Calories: 180 | Đường: 16g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:12'),
(3, 1, 'Latte đá', 'latte-da', 'CF003', 45000.00, NULL, 70, '/demo/tienha-drinks/assets/img/products/product_3.jpg', 'Cân bằng giữa sữa và espresso.', 'Latte đá cho vị mềm, hậu vị cà phê rõ nhưng không gắt.', 'Calories: 150 | Đường: 10g', 0, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:13'),
(4, 2, 'Nước ép cam dứa', 'nuoc-ep-cam-dua', 'NE001', 48000.00, 43000.00, 80, '/demo/tienha-drinks/assets/img/products/product_4.jpg', 'Vitamin C cao, vị thanh mát.', 'Kết hợp cam ngọt và dứa tươi, giúp giải nhiệt nhanh.', 'Calories: 120 | Đường: 18g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:14'),
(5, 2, 'Nước ép ổi kiwi', 'nuoc-ep-oi-kiwi', 'NE002', 52000.00, NULL, 65, '/demo/tienha-drinks/assets/img/products/product_5.jpg', 'Hương vị nhiệt đới, chua ngọt hài hòa.', 'Nước ép ổi và kiwi tươi, bổ sung chất xơ và khoáng chất.', 'Calories: 140 | Đường: 19g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:14'),
(6, 2, 'Nước ép táo cần tây', 'nuoc-ep-tao-can-tay', 'NE003', 54000.00, 50000.00, 50, '/demo/tienha-drinks/assets/img/products/product_6.jpg', 'Thanh lọc cơ thể, ít ngọt.', 'Công thức thanh lọc với táo xanh, cần tây và lá bạc hà.', 'Calories: 95 | Đường: 11g', 0, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:15'),
(7, 3, 'Sinh tố bơ xoài', 'sinh-to-bo-xoai', 'ST001', 56000.00, 51000.00, 75, '/demo/tienha-drinks/assets/img/products/product_7.jpg', 'Kem mịn, béo vừa phải.', 'Bơ chín và xoài cắt hỏa lọc xay cùng yogurt không đường.', 'Calories: 260 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:16'),
(8, 3, 'Sinh tố dâu chuối', 'sinh-to-dau-chuoi', 'ST002', 58000.00, NULL, 60, '/demo/tienha-drinks/assets/img/products/product_8.jpg', 'Ngọt dịu, no lâu.', 'Dâu tây tươi, chuối chín, sữa hạt hạnh nhân.', 'Calories: 240 | Đường: 19g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:16'),
(9, 4, 'Trà đào cam sả', 'tra-dao-cam-sa', 'TT001', 46000.00, 42000.00, 95, '/demo/tienha-drinks/assets/img/products/product_9.jpg', 'Thơm mùi cam sả, trà đen nhẹ.', 'Trà đào với topping đào ngâm, thích hợp ngày nắng nóng.', 'Calories: 130 | Đường: 15g', 1, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:17'),
(10, 5, 'Sữa chua granola trái cây', 'sua-chua-granola-trai-cay', 'SC001', 62000.00, 59000.00, 40, '/demo/tienha-drinks/assets/img/products/product_10.jpg', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 280 | Đường: 22g', 0, 1, '2026-07-07 20:48:33', '2026-07-14 06:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `reward_catalog`
--

CREATE TABLE `reward_catalog` (
  `id` int(10) UNSIGNED NOT NULL,
  `reward_name` varchar(120) NOT NULL,
  `required_points` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_catalog`
--

INSERT INTO `reward_catalog` (`id`, `reward_name`, `required_points`, `stock`, `is_active`, `created_at`) VALUES
(1, 'Voucher 20K', 120, 500, 1, '2026-07-07 20:48:33'),
(2, 'Voucher 50K', 300, 200, 1, '2026-07-07 20:48:33'),
(3, 'Bình giữ nhiệt mini', 600, 50, 1, '2026-07-07 20:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `reward_redemptions`
--

CREATE TABLE `reward_redemptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `reward_id` int(10) UNSIGNED NOT NULL,
  `points_used` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_redemptions`
--

INSERT INTO `reward_redemptions` (`id`, `customer_id`, `reward_id`, `points_used`, `status`, `created_at`) VALUES
(1, 1, 1, 120, 'approved', '2026-07-07 20:48:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `fk_customers_tier` (`tier_id`);

--
-- Indexes for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `fk_orders_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `points_transactions`
--
ALTER TABLE `points_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_points_customer` (`customer_id`),
  ADD KEY `fk_points_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Indexes for table `reward_catalog`
--
ALTER TABLE `reward_catalog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reward_redemptions`
--
ALTER TABLE `reward_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_redemption_customer` (`customer_id`),
  ADD KEY `fk_redemption_reward` (`reward_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_tiers`
--
ALTER TABLE `customer_tiers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `points_transactions`
--
ALTER TABLE `points_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reward_catalog`
--
ALTER TABLE `reward_catalog`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reward_redemptions`
--
ALTER TABLE `reward_redemptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_tier` FOREIGN KEY (`tier_id`) REFERENCES `customer_tiers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `points_transactions`
--
ALTER TABLE `points_transactions`
  ADD CONSTRAINT `fk_points_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_points_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `reward_redemptions`
--
ALTER TABLE `reward_redemptions`
  ADD CONSTRAINT `fk_redemption_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_redemption_reward` FOREIGN KEY (`reward_id`) REFERENCES `reward_catalog` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
