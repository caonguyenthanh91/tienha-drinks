<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function e(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

function get_categories(): array
{
	$stmt = db()->query('SELECT id, name, slug, description, image FROM categories WHERE is_active = 1 ORDER BY display_order, name');
	return $stmt->fetchAll();
}

function get_featured_products(int $limit = 8): array
{
	$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.id DESC LIMIT :limit');
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll();
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
			WHERE p.is_active = 1';
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
		$subtotal += (float)$item['price'] * (int)$item['quantity'];
	}

	$shipping = $subtotal > 300000 ? 0 : 20000;
	$discount = 0.0;
	$total = max(0, $subtotal + $shipping - $discount);

	return [
		'subtotal' => $subtotal,
		'shipping' => (float)$shipping,
		'discount' => $discount,
		'total' => $total,
	];
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

