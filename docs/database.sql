-- =============================================
-- TIENHA DRINKS DATABASE INIT SCRIPT
-- MySQL 8+
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reward_redemptions;
DROP TABLE IF EXISTS reward_catalog;
DROP TABLE IF EXISTS points_transactions;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS customer_tiers;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(120) NOT NULL,
	email VARCHAR(180) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('super_admin', 'editor', 'staff') NOT NULL DEFAULT 'staff',
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customer_tiers (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(80) NOT NULL,
	min_spending DECIMAL(12,2) NOT NULL DEFAULT 0,
	benefits VARCHAR(255) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(120) NOT NULL,
	email VARCHAR(180) NOT NULL UNIQUE,
	phone VARCHAR(20) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	tier_id INT UNSIGNED NULL,
	points INT NOT NULL DEFAULT 0,
	total_spending DECIMAL(12,2) NOT NULL DEFAULT 0,
	default_address VARCHAR(255) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_customers_tier FOREIGN KEY (tier_id) REFERENCES customer_tiers(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(120) NOT NULL,
	slug VARCHAR(160) NOT NULL UNIQUE,
	description VARCHAR(255) NULL,
	image VARCHAR(255) NULL,
	display_order INT NOT NULL DEFAULT 0,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	category_id INT UNSIGNED NOT NULL,
	name VARCHAR(180) NOT NULL,
	slug VARCHAR(220) NOT NULL UNIQUE,
	sku VARCHAR(40) NOT NULL UNIQUE,
	price DECIMAL(12,2) NOT NULL,
	sale_price DECIMAL(12,2) NULL,
	stock INT NOT NULL DEFAULT 0,
	thumbnail VARCHAR(255) NULL,
	short_description VARCHAR(255) NULL,
	description TEXT NULL,
	nutrition_info TEXT NULL,
	is_featured TINYINT(1) NOT NULL DEFAULT 0,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE blog_posts (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(220) NOT NULL,
	slug VARCHAR(255) NOT NULL UNIQUE,
	excerpt VARCHAR(255) NOT NULL,
	content LONGTEXT NOT NULL,
	thumbnail VARCHAR(255) NULL,
	author_name VARCHAR(120) NOT NULL,
	status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
	published_at DATETIME NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE coupons (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	code VARCHAR(40) NOT NULL UNIQUE,
	title VARCHAR(120) NOT NULL,
	discount_type ENUM('fixed', 'percent') NOT NULL,
	discount_value DECIMAL(10,2) NOT NULL,
	min_order_value DECIMAL(12,2) NOT NULL DEFAULT 0,
	start_at DATETIME NOT NULL,
	end_at DATETIME NOT NULL,
	usage_limit INT NULL,
	used_count INT NOT NULL DEFAULT 0,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	order_code VARCHAR(40) NOT NULL UNIQUE,
	customer_id INT UNSIGNED NULL,
	customer_name VARCHAR(120) NOT NULL,
	customer_phone VARCHAR(20) NOT NULL,
	customer_email VARCHAR(180) NULL,
	shipping_address VARCHAR(255) NOT NULL,
	payment_method ENUM('cod', 'bank_transfer', 'momo') NOT NULL DEFAULT 'cod',
	status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
	subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
	shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
	discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
	final_total DECIMAL(12,2) NOT NULL DEFAULT 0,
	note VARCHAR(255) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	order_id INT UNSIGNED NOT NULL,
	product_id INT UNSIGNED NULL,
	product_name VARCHAR(180) NOT NULL,
	unit_price DECIMAL(12,2) NOT NULL,
	quantity INT NOT NULL,
	line_total DECIMAL(12,2) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE points_transactions (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	customer_id INT UNSIGNED NOT NULL,
	order_id INT UNSIGNED NULL,
	type ENUM('earn', 'redeem', 'adjust') NOT NULL,
	points INT NOT NULL,
	note VARCHAR(255) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_points_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_points_order FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reward_catalog (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	reward_name VARCHAR(120) NOT NULL,
	required_points INT NOT NULL,
	stock INT NOT NULL DEFAULT 0,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reward_redemptions (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	customer_id INT UNSIGNED NOT NULL,
	reward_id INT UNSIGNED NOT NULL,
	points_used INT NOT NULL,
	status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_redemption_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_redemption_reward FOREIGN KEY (reward_id) REFERENCES reward_catalog(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_messages (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	full_name VARCHAR(120) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	email VARCHAR(180) NULL,
	message TEXT NOT NULL,
	status ENUM('new', 'processing', 'done') NOT NULL DEFAULT 'new',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SAMPLE DATA
-- =============================================

INSERT INTO admins (full_name, email, password_hash, role) VALUES
('Quan tri he thong', 'admin@tienhadrinks.vn', '$2y$10$abcdefghijklmnopqrstuv', 'super_admin');

INSERT INTO customer_tiers (name, min_spending, benefits) VALUES
('Dong', 0, 'Tich 1 diem/10.000 VND'),
('Bac', 2000000, 'Tang 5% cho don tu 200.000 VND'),
('Vang', 5000000, 'Tang 10% + uu tien giao hang');

INSERT INTO customers (full_name, email, phone, password_hash, tier_id, points, total_spending, default_address) VALUES
('Nguyen Minh Thu', 'thu.nguyen@example.com', '0908000001', '$2y$10$abcdefghijklmnopqrstuv', 2, 260, 2850000, '45 Nguyen Hue, Q1, TP.HCM'),
('Tran Hoang Nam', 'nam.tran@example.com', '0908000002', '$2y$10$abcdefghijklmnopqrstuv', 1, 90, 780000, '21 Le Lai, Q1, TP.HCM'),
('Le Thu Ha', 'ha.le@example.com', '0908000003', '$2y$10$abcdefghijklmnopqrstuv', 3, 520, 6120000, '99 Bui Vien, Q1, TP.HCM');

INSERT INTO categories (name, slug, description, image, display_order) VALUES
('Ca phe', 'ca-phe', 'Ca phe den da, bac xiu, latte va blend signature.', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 1),
('Nuoc ep', 'nuoc-ep', 'Nuoc ep trai cay tuoi trong ngay.', 'https://images.unsplash.com/photo-1600271886742-f049cd5bba3f', 2),
('Sinh to', 'sinh-to', 'Sinh to dam vi, bo sung nang luong.', 'https://images.unsplash.com/photo-1553530666-ba11a90b8c74', 3),
('Tra trai cay', 'tra-trai-cay', 'Tra thanh mat ket hop topping trai cay.', 'https://images.unsplash.com/photo-1497534446932-c925b458314e', 4),
('Sua chua', 'sua-chua', 'Mon sua chua mix trai cay va granola.', 'https://images.unsplash.com/photo-1488477181946-6428a0291777', 5);

-- 10+ sample products for testing
INSERT INTO products (category_id, name, slug, sku, price, sale_price, stock, thumbnail, short_description, description, nutrition_info, is_featured) VALUES
(1, 'Ca phe den da', 'ca-phe-den-da', 'CF001', 32000, 29000, 120, 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 'Vi dam, thom manh, it ngot.', 'Ca phe nguyen chat phoi da vien, phu hop cho ngay can tinh tao.', 'Calories: 40 | Duong: 6g', 1),
(1, 'Bac xiu ngoc trai', 'bac-xiu-ngoc-trai', 'CF002', 42000, 39000, 90, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735', 'Sua nhieu hon ca phe, vi nhe.', 'Bac xiu phoi sua tuoi va top cream min, de uong cho nguoi moi bat dau.', 'Calories: 180 | Duong: 16g', 1),
(1, 'Latte da', 'latte-da', 'CF003', 45000, NULL, 70, 'https://images.unsplash.com/photo-1497636577773-f1231844b336', 'Can bang giua sua va espresso.', 'Latte da cho vi mem, hau vi ca phe ro nhung khong gắt.', 'Calories: 150 | Duong: 10g', 0),
(2, 'Nuoc ep cam dua', 'nuoc-ep-cam-dua', 'NE001', 48000, 43000, 80, 'https://images.unsplash.com/photo-1553530979-fbb9e4aee36f', 'Vitamin C cao, vi thanh mat.', 'Ket hop cam ngot va dua tuoi, giup giai nhiet nhanh.', 'Calories: 120 | Duong: 18g', 1),
(2, 'Nuoc ep oi kiwi', 'nuoc-ep-oi-kiwi', 'NE002', 52000, NULL, 65, 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b', 'Huong vi nhiet doi, chua ngot hai hoa.', 'Nuoc ep oi va kiwi tuoi, bo sung chat xo va khoang chat.', 'Calories: 140 | Duong: 19g', 1),
(2, 'Nuoc ep tao can tay', 'nuoc-ep-tao-can-tay', 'NE003', 54000, 50000, 50, 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8', 'Thanh loc co the, it ngot.', 'Cong thuc thanh loc voi tao xanh, can tay va la bac ha.', 'Calories: 95 | Duong: 11g', 0),
(3, 'Sinh to bo xoai', 'sinh-to-bo-xoai', 'ST001', 56000, 51000, 75, 'https://images.unsplash.com/photo-1505252585461-04db1eb84625', 'Kem min, beo vua phai.', 'Bo chin va xoai cat hoa loc xay cung yogurt khong duong.', 'Calories: 260 | Duong: 20g', 1),
(3, 'Sinh to dau chuoi', 'sinh-to-dau-chuoi', 'ST002', 58000, NULL, 60, 'https://images.unsplash.com/photo-1553909489-cd47e0907980', 'Ngot diu, no lau.', 'Dau tay tuoi, chuoi chin, sua hat hanh nhan.', 'Calories: 240 | Duong: 19g', 1),
(4, 'Tra dao cam sa', 'tra-dao-cam-sa', 'TT001', 46000, 42000, 95, 'https://images.unsplash.com/photo-1594679552209-9db3f0fdb3f1', 'Thom mui cam sa, tra den nhe.', 'Tra dao voi topping dao ngâm, thich hop ngay nang nong.', 'Calories: 130 | Duong: 15g', 1),
(5, 'Sua chua granola trai cay', 'sua-chua-granola-trai-cay', 'SC001', 62000, 59000, 40, 'https://images.unsplash.com/photo-1488477181946-6428a0291777', 'Giau chat xo va probiotic.', 'Sua chua nha lam an kem granola, xoai, dau va kiwi.', 'Calories: 280 | Duong: 22g', 0);

INSERT INTO blog_posts (title, slug, excerpt, content, thumbnail, author_name, status, published_at) VALUES
('5 ly do nen chon nuoc ep tuoi moi ngay', '5-ly-do-nen-chon-nuoc-ep-tuoi-moi-ngay', 'Nuoc ep tuoi giup bo sung vitamin va nang luong nhanh.', 'Noi dung bai viet chi tiet ve loi ich cua nuoc ep tuoi, cach ket hop nguyen lieu va thoi diem uong phu hop.', 'https://images.unsplash.com/photo-1553530979-fbb9e4aee36f', 'Tien Ha Team', 'published', '2026-06-10 09:30:00'),
('Bi quyet giu vi ngon cho ca phe da xay', 'bi-quyet-giu-vi-ngon-cho-ca-phe-da-xay', 'Huong dan chon hat, xay dung cong thuc va canh nhiet do.', 'Noi dung bai viet chia se cach can doi giua do dam va do ngot trong mon ca phe da xay.', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 'Tien Ha Team', 'published', '2026-06-22 14:00:00'),
('Combo bua sang nhanh cho dan van phong', 'combo-bua-sang-nhanh-cho-dan-van-phong', 'Goi y combo smoothie + yogurt cho buoi sang day nang luong.', 'Noi dung bai viet goi y cac combo don gian, de mang di va dam bao chat dinh duong.', 'https://images.unsplash.com/photo-1488477181946-6428a0291777', 'Tien Ha Team', 'published', '2026-07-01 08:00:00');

INSERT INTO coupons (code, title, discount_type, discount_value, min_order_value, start_at, end_at, usage_limit, used_count, is_active) VALUES
('HELLOTIENHA', 'Giam cho khach moi', 'percent', 15, 100000, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 3000, 124, 1),
('FREESHIP300', 'Mien phi giao hang tu 300K', 'fixed', 20000, 300000, '2026-01-01 00:00:00', '2026-12-31 23:59:59', NULL, 332, 1);

INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, customer_email, shipping_address, payment_method, status, subtotal, shipping_fee, discount_amount, final_total, note, created_at) VALUES
('TH20260701001', 1, 'Nguyen Minh Thu', '0908000001', 'thu.nguyen@example.com', '45 Nguyen Hue, Q1, TP.HCM', 'momo', 'completed', 152000, 20000, 22000, 150000, 'Giao gio hanh chinh', '2026-07-01 10:15:00'),
('TH20260702001', 2, 'Tran Hoang Nam', '0908000002', 'nam.tran@example.com', '21 Le Lai, Q1, TP.HCM', 'cod', 'shipping', 98000, 20000, 0, 118000, NULL, '2026-07-02 15:20:00'),
('TH20260703001', 3, 'Le Thu Ha', '0908000003', 'ha.le@example.com', '99 Bui Vien, Q1, TP.HCM', 'bank_transfer', 'confirmed', 224000, 0, 15000, 209000, NULL, '2026-07-03 09:40:00');

INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES
(1, 1, 'Ca phe den da', 29000, 2, 58000),
(1, 4, 'Nuoc ep cam dua', 43000, 2, 86000),
(1, 9, 'Tra dao cam sa', 42000, 1, 42000),
(2, 2, 'Bac xiu ngoc trai', 39000, 1, 39000),
(2, 8, 'Sinh to dau chuoi', 58000, 1, 58000),
(3, 5, 'Nuoc ep oi kiwi', 52000, 2, 104000),
(3, 7, 'Sinh to bo xoai', 51000, 2, 102000),
(3, 10, 'Sua chua granola trai cay', 59000, 1, 59000);

INSERT INTO points_transactions (customer_id, order_id, type, points, note) VALUES
(1, 1, 'earn', 15, 'Cong diem don TH20260701001'),
(2, 2, 'earn', 11, 'Cong diem don TH20260702001'),
(3, 3, 'earn', 20, 'Cong diem don TH20260703001');

INSERT INTO reward_catalog (reward_name, required_points, stock, is_active) VALUES
('Voucher 20K', 120, 500, 1),
('Voucher 50K', 300, 200, 1),
('Binh giu nhiet mini', 600, 50, 1);

INSERT INTO reward_redemptions (customer_id, reward_id, points_used, status) VALUES
(1, 1, 120, 'approved');

INSERT INTO contact_messages (full_name, phone, email, message, status, created_at) VALUES
('Pham Nhat Linh', '0908000010', 'linh.pham@example.com', 'Toi muon dat 20 ly cho su kien van phong vao thu 6.', 'new', '2026-07-05 11:00:00'),
('Do Minh Quan', '0908000011', NULL, 'Cho minh xin bang gia giao so luong lon cho khach doan.', 'processing', '2026-07-06 08:30:00');

SET FOREIGN_KEY_CHECKS = 1;

