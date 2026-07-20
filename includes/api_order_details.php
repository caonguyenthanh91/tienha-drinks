<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order_id']);
    exit;
}

try {
    $pdo = db();
    
    // Lấy thông tin đơn hàng
    $stmtOrder = $pdo->prepare('SELECT id, order_code, final_total FROM orders WHERE id = :id');
    $stmtOrder->execute([':id' => $orderId]);
    $order = $stmtOrder->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }
    
    // Lấy chi tiết sản phẩm trong đơn
    $stmtItems = $pdo->prepare('SELECT product_name, quantity, unit_price, line_total FROM order_items WHERE order_id = :order_id ORDER BY id ASC');
    $stmtItems->execute([':order_id' => $orderId]);
    $items = $stmtItems->fetchAll();
    
    echo json_encode([
        'success' => true,
        'order_code' => $order['order_code'],
        'items' => $items,
        'final_total' => (float)$order['final_total'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
