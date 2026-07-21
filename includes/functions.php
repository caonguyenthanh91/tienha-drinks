<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function e(?string $value): string
{
	return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
	$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
	$base = $base === '.' ? '' : $base;
	return $base . '/' . ltrim($path, '/');
}

function format_currency(float $amount): string
{
	return number_format($amount, 0, ',', '.') . ' VND';
}

function current_page(): string
{
	return $_GET['page'] ?? 'home';
}

function normalize_phone_digits(string $phone): string
{
	return preg_replace('/\D+/', '', $phone) ?? '';
}

function remembered_phone_cookie_name(): string
{
	return 'tienha_phone';
}

function remembered_phone_value(): string
{
	return normalize_phone_digits((string)($_COOKIE[remembered_phone_cookie_name()] ?? ''));
}

function admin_session_user(): ?array
{
	$user = $_SESSION['user'] ?? null;
	if (!is_array($user) || empty($user['is_admin'])) {
		return null;
	}

	return $user;
}

function is_admin_authenticated(): bool
{
	return admin_session_user() !== null;
}

function admin_login(string $username, string $password): bool
{
	$username = trim(mb_strtolower($username));
	if ($username === '' || $password === '') {
		return false;
	}

	$stmt = db()->prepare('SELECT id, full_name, email, password_hash, role FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
	$stmt->execute([':email' => $username]);
	$admin = $stmt->fetch();

	if (!$admin || !password_verify($password, (string)$admin['password_hash'])) {
		return false;
	}

	$_SESSION['user'] = [
		'id' => (int)$admin['id'],
		'full_name' => (string)$admin['full_name'],
		'email' => (string)$admin['email'],
		'role' => (string)$admin['role'],
		'is_admin' => true,
	];
	session_regenerate_id(true);

	return true;
}

function admin_logout(): void
{
	$_SESSION['user'] = null;
	session_regenerate_id(true);
}

function get_categories(): array
{
	$stmt = db()->query('SELECT id, name, slug, description, image FROM categories WHERE is_active = 1 ORDER BY display_order, name');
	return $stmt->fetchAll();
}

function get_featured_products(int $limit = 8): array
{
	$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.is_featured = 1 ORDER BY p.id DESC LIMIT :limit');
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll();
}

function get_best_sellers(int $limit = 8): array
{
	$sql = 'SELECT p.*, c.name AS category_name, COALESCE(SUM(oi.quantity), 0) AS sold_qty
			FROM products p
			INNER JOIN categories c ON c.id = p.category_id
			LEFT JOIN order_items oi ON oi.product_id = p.id
			WHERE 1 = 1
			GROUP BY p.id
			ORDER BY sold_qty DESC, p.is_featured DESC, p.id DESC
			LIMIT :limit';
	$stmt = db()->prepare($sql);
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll();
}

/**
 * Static customer testimonials. Kept in code because the schema has no reviews table yet.
 *
 * @return array<int, array{name:string, role:string, rating:int, content:string}>
 */
function get_testimonials(): array
{
	return [
		[
			'name' => 'Nguyễn Minh Thu',
			'role' => 'Khách hàng thân thiết',
			'rating' => 5,
			'content' => 'Trà trái cây tươi, giao hàng nhanh. Mình đặt gần như mỗi ngày đi làm!',
		],
		[
			'name' => 'Trần Hoàng Nam',
			'role' => 'Dân văn phòng',
			'rating' => 5,
			'content' => 'Trà sữa đậm vị, đóng gói chắc chắn. Đặt combo cho cả phòng ai cũng khen.',
		],
		[
			'name' => 'Lê Thu Hà',
			'role' => 'Food blogger',
			'rating' => 5,
			'content' => 'Menu đa dạng, nước ép thanh mát ít ngọt đúng gu mình. Không gian đặt hàng trên web rất dễ dùng.',
		],
	];
}

function get_products(?string $categorySlug = null, ?string $keyword = null, string $sort = 'newest'): array
{
	$orderBy = 'p.id DESC';
	if ($sort === 'price_asc') {
		$orderBy = 'p.price ASC';
	} elseif ($sort === 'price_desc') {
		$orderBy = 'p.price DESC';
	} elseif ($sort === 'name_asc') {
		$orderBy = 'p.name ASC';
	}

	$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
			FROM products p
			INNER JOIN categories c ON c.id = p.category_id
			WHERE 1 = 1';
	$params = [];

	if ($categorySlug) {
		$sql .= ' AND c.slug = :category_slug';
		$params[':category_slug'] = $categorySlug;
	}

	if ($keyword) {
		$sql .= ' AND (p.name LIKE :keyword OR p.short_description LIKE :keyword)';
		$params[':keyword'] = '%' . $keyword . '%';
	}

	$sql .= " ORDER BY {$orderBy}";
	$stmt = db()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

function get_product_by_id(int $id): ?array
{
	$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.id = :id AND p.is_active = 1 LIMIT 1');
	$stmt->execute([':id' => $id]);
	$product = $stmt->fetch();
	return $product ?: null;
}

function get_product_by_id_for_display(int $id): ?array
{
	$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.id = :id LIMIT 1');
	$stmt->execute([':id' => $id]);
	$product = $stmt->fetch();
	return $product ?: null;
}

function get_blog_posts(int $limit = 12): array
{
	$stmt = db()->prepare('SELECT id, title, slug, thumbnail, excerpt, author_name, published_at FROM blog_posts WHERE status = "published" ORDER BY published_at DESC LIMIT :limit');
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll();
}

function cart_count(): int
{
	$count = 0;
	foreach (($_SESSION['cart'] ?? []) as $item) {
		$count += (int)($item['quantity'] ?? 0);
	}
	return $count;
}

function cart_items(): array
{
	return array_values($_SESSION['cart'] ?? []);
}

function cart_totals(): array
{
	$subtotal = 0.0;
	foreach (cart_items() as $item) {
		$subtotal += effective_price($item) * (int)$item['quantity'];
	}

	$shipping = $subtotal > 0 ? 20000 : 0;
	$discount = 0.0;
	$total = max(0, $subtotal + $shipping - $discount);

	return [
		'subtotal' => $subtotal,
		'shipping' => (float)$shipping,
		'discount' => $discount,
		'total' => $total,
	];
}

function get_active_coupon_by_code(string $code): ?array
{
	$normalizedCode = strtoupper(trim($code));
	if ($normalizedCode === '') {
		return null;
	}

	$stmt = db()->prepare('SELECT * FROM coupons WHERE code = :code AND is_active = 1 AND NOW() BETWEEN start_at AND end_at AND (usage_limit IS NULL OR used_count < usage_limit) LIMIT 1');
	$stmt->execute([':code' => $normalizedCode]);
	$coupon = $stmt->fetch();

	return $coupon ?: null;
}

function calculate_coupon_discount(?array $coupon, float $subtotal, float $shipping): float
{
	if (!$coupon) {
		return 0.0;
	}

	$minOrderValue = (float)($coupon['min_order_value'] ?? 0);
	if ($subtotal < $minOrderValue) {
		return 0.0;
	}

	$totalBeforeDiscount = max(0, $subtotal + $shipping);
	$discountType = (string)($coupon['discount_type'] ?? 'fixed');
	$discountValue = (float)($coupon['discount_value'] ?? 0);

	if ($discountType === 'percent') {
		$discount = $totalBeforeDiscount * ($discountValue / 100);
	} else {
		$discount = $discountValue;
	}

	return min($discount, $totalBeforeDiscount);
}

function add_to_cart(int $productId, int $quantity = 1): bool
{
	$product = get_product_by_id($productId);
	if (!$product) {
		return false;
	}

	$quantity = max(1, $quantity);
	$key = (string)$productId;

	if (isset($_SESSION['cart'][$key])) {
		$_SESSION['cart'][$key]['quantity'] += $quantity;
	} else {
		$_SESSION['cart'][$key] = [
			'product_id' => (int)$product['id'],
			'name' => $product['name'],
			'price' => (float)$product['price'],
			'sale_price' => $product['sale_price'] !== null ? (float)$product['sale_price'] : null,
			'thumbnail' => $product['thumbnail'],
			'quantity' => $quantity,
		];
	}

	return true;
}

function update_cart_quantity(int $productId, int $quantity): void
{
	$key = (string)$productId;
	if (!isset($_SESSION['cart'][$key])) {
		return;
	}

	if ($quantity <= 0) {
		unset($_SESSION['cart'][$key]);
		return;
	}

	$_SESSION['cart'][$key]['quantity'] = $quantity;
}

function effective_price(array $product): float
{
	if (isset($product['sale_price']) && $product['sale_price'] !== null && (float)$product['sale_price'] > 0) {
		return (float)$product['sale_price'];
	}
	return (float)$product['price'];
}

function get_customer_tiers(): array
{
	$stmt = db()->query('SELECT * FROM customer_tiers ORDER BY min_spending ASC');
	return $stmt->fetchAll();
}

function get_customer_by_phone(string $phone): ?array
{
	$cleanPhone = preg_replace('/\D+/', '', $phone) ?? '';
	if ($cleanPhone === '') {
		return null;
	}
	$stmt = db()->prepare('SELECT c.*, ct.id AS tier_id, ct.name AS tier_name, ct.discount_percent, ct.benefits FROM customers c LEFT JOIN customer_tiers ct ON ct.id = c.tier_id WHERE c.phone = :phone LIMIT 1');
	$stmt->execute([':phone' => $cleanPhone]);
	$customer = $stmt->fetch();
	return $customer ?: null;
}

function get_customer_tier_discount(int $tierId): float
{
	if ($tierId <= 0) {
		return 0.0;
	}
	$stmt = db()->prepare('SELECT discount_percent FROM customer_tiers WHERE id = :id LIMIT 1');
	$stmt->execute([':id' => $tierId]);
	$tier = $stmt->fetch();
	return $tier ? (float)($tier['discount_percent'] ?? 0) : 0.0;
}

function cart_totals_with_discount(float $discount_percent = 0, string $couponCode = ''): array
{
	$subtotal = 0.0;
	foreach (cart_items() as $item) {
		$subtotal += effective_price($item) * (int)$item['quantity'];
	}

	$shipping = $subtotal > 0 ? 20000 : 0;
	$totalBeforeDiscount = max(0, $subtotal + $shipping);
	$tierDiscount = $totalBeforeDiscount * ($discount_percent / 100);
	$coupon = get_active_coupon_by_code($couponCode);
	$couponDiscount = calculate_coupon_discount($coupon, $subtotal, (float)$shipping);
	$discount = min($totalBeforeDiscount, $tierDiscount + $couponDiscount);
	$total = max(0, $totalBeforeDiscount - $discount);

	return [
		'subtotal' => $subtotal,
		'shipping' => (float)$shipping,
		'discount' => $discount,
		'discount_percent' => $discount_percent,
		'tier_discount' => $tierDiscount,
		'coupon_code' => $coupon ? (string)$coupon['code'] : '',
		'coupon_discount' => $couponDiscount,
		'total' => $total,
	];
}

function get_admin_overview(): array
{
	$pdo = db();
	$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
	$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
	$totalCustomers = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
	$totalRevenue = (float)$pdo->query('SELECT COALESCE(SUM(final_total), 0) FROM orders WHERE status IN ("confirmed", "shipping", "completed")')->fetchColumn();

	return [
		'total_products' => $totalProducts,
		'total_orders' => $totalOrders,
		'total_customers' => $totalCustomers,
		'total_revenue' => $totalRevenue,
	];
}

function order_status_label(string $status): string
{
	return match ($status) {
		'pending' => 'Chờ xác nhận',
		'confirmed' => 'Đã xác nhận',
		'shipping' => 'Đang giao',
		'completed' => 'Đã hoàn thành',
		'cancelled' => 'Đã hủy',
		default => $status,
	};
}

function order_status_badge_class(string $status): string
{
	return match ($status) {
		'pending' => 'text-bg-warning',
		'confirmed' => 'text-bg-info',
		'shipping' => 'text-bg-primary',
		'completed' => 'text-bg-success',
		'cancelled' => 'text-bg-danger',
		default => 'text-bg-secondary',
	};
}

function payment_method_label(string $paymentMethod): string
{
	return match ($paymentMethod) {
		'cod' => 'Thanh toán khi nhận hàng (COD)',
		'bank_transfer' => 'Chuyển khoản ngân hàng',
		'momo' => 'Ví MoMo',
		default => $paymentMethod,
	};
}

function order_delivery_timing_label(string $scheduledAt): string
{
	$scheduledTimestamp = strtotime($scheduledAt);
	if ($scheduledTimestamp === false) {
		return 'Giao ngay';
	}

	return $scheduledTimestamp > time() ? 'Đặt trước' : 'Giao ngay';
}

function order_delivery_timing_badge_class(string $scheduledAt): string
{
	return order_delivery_timing_label($scheduledAt) === 'Đặt trước'
		? 'text-bg-primary'
		: 'text-bg-secondary';
}

function allowed_next_order_statuses(string $currentStatus): array
{
	return match ($currentStatus) {
		'pending' => ['confirmed', 'cancelled'],
		'confirmed' => ['shipping', 'cancelled'],
		'shipping' => ['completed', 'cancelled'],
		default => [],
	};
}

/**
 * @return array{success:bool,message:string}
 */
function update_order_status_by_admin(int $orderId, string $nextStatus): array
{
	$nextStatus = trim($nextStatus);
	$pdo = db();

	try {
		$pdo->beginTransaction();

		$stmt = $pdo->prepare('SELECT id, customer_id, customer_name, customer_phone, customer_email, shipping_address, status, final_total FROM orders WHERE id = :id LIMIT 1 FOR UPDATE');
		$stmt->execute([':id' => $orderId]);
		$order = $stmt->fetch();

		if (!$order) {
			$pdo->rollBack();
			return ['success' => false, 'message' => 'Không tìm thấy đơn hàng.'];
		}

		$currentStatus = (string)$order['status'];
		if (!in_array($nextStatus, allowed_next_order_statuses($currentStatus), true)) {
			$pdo->rollBack();
			return ['success' => false, 'message' => 'Trạng thái không hợp lệ cho đơn hàng này.'];
		}

		$stmtUpdate = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
		$stmtUpdate->execute([
			':status' => $nextStatus,
			':id' => $orderId,
		]);

		if ($nextStatus === 'completed') {
			award_customer_points_for_completed_order($pdo, $order);
		}

		$pdo->commit();
		return ['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công.'];
	} catch (Throwable $e) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		return ['success' => false, 'message' => 'Không thể cập nhật trạng thái: ' . $e->getMessage()];
	}
}

/**
 * @param array{id:mixed,customer_id:mixed,customer_phone:mixed,final_total:mixed} $order
 */
function award_customer_points_for_completed_order(PDO $pdo, array $order): void
{
	$customerId = isset($order['customer_id']) && $order['customer_id'] !== null ? (int)$order['customer_id'] : 0;

	if ($customerId <= 0) {
		$customer = get_customer_by_phone((string)($order['customer_phone'] ?? ''));
		if ($customer) {
			$customerId = (int)$customer['id'];
		} else {
			$customerId = create_customer_from_order($pdo, $order);
		}

		if ($customerId <= 0) {
			return;
		}

		$stmtLink = $pdo->prepare('UPDATE orders SET customer_id = :customer_id WHERE id = :id');
		$stmtLink->execute([
			':customer_id' => $customerId,
			':id' => (int)$order['id'],
		]);
	}

	$finalTotal = (float)($order['final_total'] ?? 0);
	$pointsToAdd = (int)floor($finalTotal / 100);

	$stmtCustomer = $pdo->prepare('UPDATE customers SET points = points + :points, total_spending = total_spending + :spending WHERE id = :id');
	$stmtCustomer->execute([
		':points' => $pointsToAdd,
		':spending' => $finalTotal,
		':id' => $customerId,
	]);

	$stmtTier = $pdo->prepare('SELECT id FROM customer_tiers WHERE min_spending <= (SELECT total_spending FROM customers WHERE id = :id) ORDER BY min_spending DESC LIMIT 1');
	$stmtTier->execute([':id' => $customerId]);
	$newTierId = (int)($stmtTier->fetchColumn() ?: 0);

	if ($newTierId > 0) {
		$stmtUpdateTier = $pdo->prepare('UPDATE customers SET tier_id = :tier_id WHERE id = :id');
		$stmtUpdateTier->execute([
			':tier_id' => $newTierId,
			':id' => $customerId,
		]);
	}
}

/**
 * @param array{id:mixed,customer_name:mixed,customer_phone:mixed,customer_email:mixed,shipping_address:mixed} $order
 */
function create_customer_from_order(PDO $pdo, array $order): int
{
	$phone = preg_replace('/\D+/', '', (string)($order['customer_phone'] ?? '')) ?? '';
	if ($phone === '') {
		return 0;
	}

	$fullName = trim((string)($order['customer_name'] ?? ''));
	if ($fullName === '') {
		$fullName = 'Khach hang';
	}

	$email = trim((string)($order['customer_email'] ?? ''));
	if ($email === '') {
		$email = 'guest-' . $phone . '-' . (int)$order['id'] . '@placeholder.local';
	} else {
		$stmtEmail = $pdo->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
		$stmtEmail->execute([':email' => $email]);
		if ($stmtEmail->fetchColumn()) {
			$email = 'guest-' . $phone . '-' . (int)$order['id'] . '@placeholder.local';
		}
	}

	$defaultAddress = trim((string)($order['shipping_address'] ?? ''));
	$passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

	$stmtTier = $pdo->query('SELECT id FROM customer_tiers ORDER BY min_spending ASC LIMIT 1');
	$tierId = (int)($stmtTier->fetchColumn() ?: 0);

	$stmtInsert = $pdo->prepare('INSERT INTO customers (full_name, email, phone, password_hash, tier_id, points, total_spending, default_address, created_at, updated_at) VALUES (:full_name, :email, :phone, :password_hash, :tier_id, 0, 0, :default_address, NOW(), NOW())');
	$stmtInsert->execute([
		':full_name' => $fullName,
		':email' => $email,
		':phone' => $phone,
		':password_hash' => $passwordHash,
		':tier_id' => $tierId > 0 ? $tierId : null,
		':default_address' => $defaultAddress,
	]);

	return (int)$pdo->lastInsertId();
}

