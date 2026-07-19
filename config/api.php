<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {
	if ($action === 'product') {
		$id = (int)($_GET['id'] ?? 0);
		$product = get_product_by_id_for_display($id);

		if (!$product) {
			http_response_code(404);
			echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
			exit;
		}

		echo json_encode([
			'success' => true,
			'data' => [
				'id' => (int)$product['id'],
				'name' => $product['name'],
				'price' => (float)$product['price'],
				'sale_price' => $product['sale_price'] !== null ? (float)$product['sale_price'] : null,
				'effective_price' => effective_price($product),
				'thumbnail' => $product['thumbnail'],
				'description' => $product['description'],
				'category_name' => $product['category_name'],
				'is_active' => (int)($product['is_active'] ?? 1) === 1,
			],
		]);
		exit;
	}

	if ($action === 'add_to_cart' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$payload = json_decode((string)file_get_contents('php://input'), true);
		$id = (int)($payload['product_id'] ?? 0);
		$quantity = (int)($payload['quantity'] ?? 1);

		if ($id <= 0) {
			http_response_code(422);
			echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
			exit;
		}

		$productForCart = get_product_by_id_for_display($id);
		if (!$productForCart) {
			http_response_code(404);
			echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
			exit;
		}

		if ((int)($productForCart['is_active'] ?? 1) !== 1) {
			http_response_code(409);
			echo json_encode(['success' => false, 'message' => 'Món này đang tạm ngưng bán']);
			exit;
		}

		if (!add_to_cart($id, $quantity)) {
			http_response_code(404);
			echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
			exit;
		}

		echo json_encode([
			'success' => true,
			'message' => 'Đã thêm vào giỏ hàng',
			'cart_count' => cart_count(),
		]);
		exit;
	}

	if ($action === 'migrate_discount' && $_GET['token'] === 'migrate_now_2026') {
		try {
			$pdo = db();
			$stmt = $pdo->query("SHOW COLUMNS FROM customer_tiers LIKE 'discount_percent'");
			$columnExists = $stmt->fetch();

			if (!$columnExists) {
				$pdo->exec("ALTER TABLE customer_tiers ADD COLUMN discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER min_spending");
				$pdo->exec("UPDATE customer_tiers SET discount_percent = 0 WHERE name = 'Đồng'");
				$pdo->exec("UPDATE customer_tiers SET discount_percent = 5 WHERE name = 'Bạc'");
				$pdo->exec("UPDATE customer_tiers SET discount_percent = 10 WHERE name = 'Vàng'");
				echo json_encode(['success' => true, 'message' => 'Migration completed']);
			} else {
				echo json_encode(['success' => true, 'message' => 'Column already exists']);
			}
		} catch (Throwable $e) {
			http_response_code(500);
			echo json_encode(['success' => false, 'message' => 'Migration failed: ' . $e->getMessage()]);
		}
		exit;
	}

	if ($action === 'check_customer_tier') {
		$phone = (string)($_GET['phone'] ?? '');
		$phone = preg_replace('/\s+/', '', $phone);

		if (strlen($phone) < 7) {
			echo json_encode([
				'success' => false,
				'message' => 'Số điện thoại không hợp lệ',
				'tier' => null,
				'customer' => null,
				'discount' => 0,
			]);
			exit;
		}

		$customer = get_customer_by_phone($phone);

		if (!$customer) {
			echo json_encode([
				'success' => false,
				'message' => 'Khách hàng mới',
				'tier' => null,
				'customer' => null,
				'discount' => 0,
			]);
			exit;
		}

		$tierId = (int)($customer['tier_id'] ?? 0);
		$tierName = $customer['tier_name'] ?? 'Khách hàng mới';
		$discount = (float)($customer['discount_percent'] ?? 0);

		echo json_encode([
			'success' => true,
			'message' => 'Tìm thấy hạng thành viên',
			'tier' => [
				'id' => $tierId,
				'name' => $tierName,
				'discount_percent' => $discount,
			],
			'customer' => [
				'full_name' => (string)($customer['full_name'] ?? ''),
				'email' => (string)($customer['email'] ?? ''),
				'default_address' => (string)($customer['default_address'] ?? ''),
				'phone' => (string)($customer['phone'] ?? ''),
			],
			'discount' => $discount,
		]);
		exit;
	}

	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Thao tác không được hỗ trợ']);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống', 'debug' => $e->getMessage()]);
}

