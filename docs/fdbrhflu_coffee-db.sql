-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 17, 2026 at 08:01 AM
-- Server version: 5.7.41-cll-lve
-- PHP Version: 7.2.34

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
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `display_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Trà sữa', 'tra-sua', 'Trà sữa Đài loan ngon xỉu', '/demo/tienha-drinks/assets/img/categories/tra-sua.png', 1, 1, '2026-07-07 20:48:33'),
(2, 'Trà trái cây', 'tra-trai-cay', 'Trà thanh mát kết hợp topping trái cây.', '/demo/tienha-drinks/assets/img/categories/tra-trai-cay.png', 2, 1, '2026-07-07 20:48:33'),
(3, 'Sinh tố', 'sinh-to', 'Sẽ sớm ra mắt', NULL, 3, 1, '2026-07-07 20:48:33'),
(4, 'Ăn vặt', 'an-vat', 'Bánh tráng trộn, rau câu ngon tuyệt', NULL, 4, 1, '2026-07-07 20:48:33');

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `min_order_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `title`, `discount_type`, `discount_value`, `min_order_value`, `start_at`, `end_at`, `usage_limit`, `used_count`, `is_active`, `created_at`) VALUES
(1, 'HELLOTIENHA', 'Giảm cho khách mới', 'percent', '15.00', '100000.00', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 3000, 124, 1, '2026-07-07 20:48:33'),
(2, 'FREESHIP300', 'Miễn phí giao hàng từ 300K', 'fixed', '20000.00', '300000.00', '2026-01-01 00:00:00', '2026-12-31 23:59:59', NULL, 332, 1, '2026-07-07 20:48:33');

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
  `points` int(11) NOT NULL DEFAULT '0',
  `total_spending` decimal(12,2) NOT NULL DEFAULT '0.00',
  `default_address` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `phone`, `password_hash`, `tier_id`, `points`, `total_spending`, `default_address`, `created_at`, `updated_at`) VALUES
(1, 'Hà Trần Mỹ Tiên', 'thu.nguyen@example.com', '0368166816', '$2y$10$abcdefghijklmnopqrstuv', 3, 260, '2850000.00', '45 Nguyễn Huệ, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-15 06:21:34'),
(2, 'Cao Nguyễn Thạnh', 'nam.tran@example.com', '0398802109', '$2y$10$abcdefghijklmnopqrstuv', 2, 90, '780000.00', '21 Lê Lai, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-15 06:21:53'),
(3, 'Hà Trần Mỹ Linh', 'ha.le@example.com', '0937909290', '$2y$10$abcdefghijklmnopqrstuv', 1, 520, '412000.00', '99 Bùi Viện, Q1, TP.HCM', '2026-07-07 20:48:33', '2026-07-15 06:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `customer_tiers`
--

CREATE TABLE `customer_tiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `min_spending` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `benefits` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer_tiers`
--

INSERT INTO `customer_tiers` (`id`, `name`, `min_spending`, `discount_percent`, `benefits`, `created_at`) VALUES
(1, 'Đồng', '0.00', '0.00', 'Tích 1 điểm/10.000 VND', '2026-07-07 20:48:33'),
(2, 'Bạc', '2000000.00', '5.00', 'Tăng 5% cho đơn từ 200.000 VND', '2026-07-07 20:48:33'),
(3, 'Vàng', '5000000.00', '10.00', 'Tăng 10% + ưu tiên giao hàng', '2026-07-07 20:48:33');

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
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `final_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `customer_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `final_total`, `note`, `created_at`, `updated_at`) VALUES
(1, 'TH20260701001', 1, 'Nguyễn Minh Thu', '0908000001', 'thu.nguyen@example.com', '45 Nguyễn Huệ, Q1, TP.HCM', 'momo', 'completed', '152000.00', '20000.00', '22000.00', '150000.00', 'Giao giờ hành chính', '2026-07-01 10:15:00', '2026-07-07 20:48:33'),
(2, 'TH20260702001', 2, 'Trần Hoàng Nam', '0908000002', 'nam.tran@example.com', '21 Lê Lai, Q1, TP.HCM', 'cod', 'shipping', '98000.00', '20000.00', '0.00', '118000.00', NULL, '2026-07-02 15:20:00', '2026-07-07 20:48:33'),
(3, 'TH20260703001', 3, 'Lê Thu Hà', '0908000003', 'ha.le@example.com', '99 Bùi Viện, Q1, TP.HCM', 'bank_transfer', 'confirmed', '224000.00', '0.00', '15000.00', '209000.00', NULL, '2026-07-03 09:40:00', '2026-07-07 20:48:33');

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `line_total`, `created_at`) VALUES
(1, 1, 1, 'Cà phê đen đá', '29000.00', 2, '58000.00', '2026-07-07 20:48:33'),
(2, 1, 4, 'Nước ép cam dứa', '43000.00', 2, '86000.00', '2026-07-07 20:48:33'),
(3, 1, 7, 'Trà đào cam sả', '42000.00', 1, '42000.00', '2026-07-07 20:48:33'),
(4, 2, 2, 'Bạc xỉu ngọc trai', '39000.00', 1, '39000.00', '2026-07-07 20:48:33'),
(5, 2, NULL, 'Sinh tố dâu chuối', '58000.00', 1, '58000.00', '2026-07-07 20:48:33'),
(6, 3, 5, 'Nước ép ổi kiwi', '52000.00', 2, '104000.00', '2026-07-07 20:48:33'),
(7, 3, NULL, 'Sinh tố bơ xoài', '51000.00', 2, '102000.00', '2026-07-07 20:48:33'),
(8, 3, 8, 'Sữa chua granola trái cây', '59000.00', 1, '59000.00', '2026-07-07 20:48:33');

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `stock` int(11) NOT NULL DEFAULT '0',
  `thumbnail` varchar(255) DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text,
  `nutrition_info` text,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `price`, `sale_price`, `stock`, `thumbnail`, `short_description`, `description`, `nutrition_info`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Trà sữa sương sáo', 'tra-sua-suong-sao', 'TS001', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-suong-sao.png', 'Vị đậm, thơm mạnh, ít ngọt.', 'Cà phê nguyên chất phơi đá viên, phù hợp cho ngày cần tỉnh táo.', 'Calories: 100 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-17 07:27:52'),
(2, 1, 'Trà sữa Đại Hồng Bào', 'tra-sua-dai-hong-bao', 'TS002', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-dai-hong-bao.png', 'Sữa nhiều hơn cà phê, vị nhẹ.', 'Bạc xỉu phối sữa tươi và top cream mịn, dễ uống cho người mới bắt đầu.', 'Calories: 100 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:01'),
(3, 1, 'Trà sữa gạo rang Đài Loan', 'tra-sua-gao-rang-dai-loan', 'TS003', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-gao-rang-dai-loan.png', 'Cân bằng giữa sữa và espresso.', 'Latte đá cho vị mềm, hậu vị cà phê rõ nhưng không gắt.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:12'),
(4, 1, 'Trà sữa gạo matcha', 'tra-sua-gao-matcha', 'TS004', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-gao-matcha.png', 'Vitamin C cao, vị thanh mát.', 'Kết hợp cam ngọt và dứa tươi, giúp giải nhiệt nhanh.', 'Calories: 100 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:29'),
(5, 1, 'Trà sữa gạo mật ong', 'tra-sua-gao-mat-ong', 'TS005', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-gao-mat-ong.png', 'Hương vị nhiệt đới, chua ngọt hài hòa.', 'Nước ép ổi và kiwi tươi, bổ sung chất xơ và khoáng chất.', 'Calories: 100 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:42'),
(6, 1, 'Trà sữa Mật Hương (Chính Sơn tiểu chủng)', 'tra-sua-mat-huong', 'TS006', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-mat-huong.png', 'Thanh lọc cơ thể, ít ngọt.', 'Công thức thanh lọc với táo xanh, cần tây và lá bạc hà.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:52'),
(7, 1, 'Trà sữa Đài Loan hoa Sơn Trà', 'tra-sua-dai-loan-hoa-son-tra', 'TS007', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-dai-loan-hoa-son-tra.png', 'Thơm mùi cam sả, trà đen nhẹ.', 'Trà đào với topping đào ngâm, thích hợp ngày nắng nóng.', 'Calories: 100 | Đường: 20g', 1, 1, '2026-07-07 20:48:33', '2026-07-17 07:28:59'),
(8, 1, 'Trà sữa Đài Loan hoa Mộc Tê', 'tra-sua-dai-loan-hoa-moc-te', 'TS008', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-dai-loan-hoa-moc-te.png', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:29:10'),
(9, 1, 'Trà sữa Đài Loan vị Cherry', 'tra-sua-dai-loan-vi-cherry', 'TS009', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-dai-loan-vi-cherry.png', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:29:19'),
(10, 1, 'Trà sữa Đài Loan vị Nhãn', 'tra-sua-dai-loan-vi-nhan', 'TS010', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-dai-loan-vi-nhan.png', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:29:29'),
(11, 1, 'Trà sữa kem trứng', 'tra-sua-kem-trung', 'TS011', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-sua-kem-trung.png', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:29:38'),
(12, 1, 'Hồng trà sữa truyền thống (đậm vị trà)', 'hong-tra-sua-truyen-thong', 'TS012', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/hong-tra-sua-truyen-thong.png', 'Giàu chất xơ và probiotic.', 'Sữa chua nhà làm ăn kèm granola, xoài, dâu và kiwi.', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-07 20:48:33', '2026-07-17 07:29:47'),
(13, 1, 'Olong trà sữa truyền thống (đậm vị trà)', 'olong-tra-sua-truyen-thong', 'TS013', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/olong-tra-sua-truyen-thong.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(14, 1, 'Sữa tươi trân châu đường đen', 'sua-tuoi-tran-chan-duong-den', 'TS014', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/sua-tuoi-tran-chan-duong-den.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(15, 2, 'Trà đào', 'tra-dao', 'TD001', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-dao.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(16, 2, 'Trà nhãn', 'tra-nhan', 'TD002', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-nhan.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(17, 2, 'Trà tắc', 'tra-tac', 'TD003', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-tac.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(18, 2, 'Trài chanh', 'tra-chanh', 'TD004', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-chanh.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(19, 2, 'Trà mận', 'tra-man', 'TD005', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-man.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(20, 2, 'Trà hoa bụt giấm', 'tra-hoa-but-giam', 'TD006', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-hoa-but-giam.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(21, 2, 'Trà ổi hồng', 'tra-oi-hong', 'TD007', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-oi-hong.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(22, 2, 'Trà mơ đào', 'tra-mo-dao', 'TD008', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-mo-dao.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(23, 2, 'Trà thơm', 'tra-thom', 'TD009', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-thom.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(24, 2, 'Trà dưa lưới', 'tra-dua-luoi', 'TD010', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-dua-luoi.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(25, 2, 'Trà mãng cầu', 'tra-mang-cau', 'TD011', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-mang-cau.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(26, 2, 'Trà dâu tây', 'tra-dau-tay', 'TD012', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-dau-tay.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(27, 2, 'Trà me', 'tra-me', 'TD013', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-me.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(28, 2, 'Trà cóc', 'tra-coc', 'TD014', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-coc.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01'),
(29, 2, 'Trà lài đác thơm', 'tra-lai-dac-thom', 'TD015', '30000.00', '25000.00', 1000, '/demo/tienha-drinks/assets/img/products/tra-lai-dac-thom.png', '', '', 'Calories: 100 | Đường: 20g', 0, 1, '2026-07-17 07:21:40', '2026-07-17 07:24:01');

-- --------------------------------------------------------

--
-- Table structure for table `reward_catalog`
--

CREATE TABLE `reward_catalog` (
  `id` int(10) UNSIGNED NOT NULL,
  `reward_name` varchar(120) NOT NULL,
  `required_points` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
