<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

try {
	if ($action === 'product') {
		$id = (int)($_GET['id'] ?? 0);
		$product = get_product_by_id($id);

		if (!$product) {
			http_response_code(404);
			echo json_encode(['success' => false, 'message' => 'Khong tim thay san pham']);
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
			echo json_encode(['success' => false, 'message' => 'Du lieu khong hop le']);
			exit;
		}

		if (!add_to_cart($id, $quantity)) {
			http_response_code(404);
			echo json_encode(['success' => false, 'message' => 'San pham khong ton tai']);
			exit;
		}

		echo json_encode([
			'success' => true,
			'message' => 'Da them vao gio hang',
			'cart_count' => cart_count(),
		]);
		exit;
	}

	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Action khong duoc ho tro']);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Loi he thong', 'debug' => $e->getMessage()]);
}

