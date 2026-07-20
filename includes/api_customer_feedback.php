<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = trim((string)($_GET['action'] ?? $_POST['action'] ?? ''));

try {
    $pdo = db();

    // GET - Lấy feedback của một đơn hàng
    if ($method === 'GET' && $action === 'get_feedback') {
        $orderId = (int)($_GET['order_id'] ?? 0);
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order_id']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, rating, feedback_text, admin_reply, admin_reply_at, status FROM customer_feedback WHERE order_id = :order_id LIMIT 1');
        $stmt->execute([':order_id' => $orderId]);
        $feedback = $stmt->fetch();

        if (!$feedback) {
            echo json_encode(['success' => true, 'feedback' => null]);
        } else {
            echo json_encode(['success' => true, 'feedback' => $feedback]);
        }
        exit;
    }

    // POST - Gửi feedback mới
    if ($method === 'POST' && $action === 'submit_feedback') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $feedbackText = trim((string)($_POST['feedback_text'] ?? ''));

        if ($orderId <= 0 || $rating < 1 || $rating > 5 || $feedbackText === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        // Kiểm tra đơn hàng tồn tại
        $stmtCheck = $pdo->prepare('SELECT id FROM orders WHERE id = :id');
        $stmtCheck->execute([':id' => $orderId]);
        if (!$stmtCheck->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        // Kiểm tra đã có feedback chưa
        $stmtExist = $pdo->prepare('SELECT id FROM customer_feedback WHERE order_id = :order_id');
        $stmtExist->execute([':order_id' => $orderId]);
        if ($stmtExist->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Feedback already exists']);
            exit;
        }

        // Lưu feedback
        $stmt = $pdo->prepare('INSERT INTO customer_feedback (order_id, customer_id, rating, feedback_text, status, created_at, updated_at) 
                              VALUES (:order_id, :customer_id, :rating, :feedback_text, "pending", NOW(), NOW())');
        $stmt->execute([
            ':order_id' => $orderId,
            ':customer_id' => $customerId > 0 ? $customerId : null,
            ':rating' => $rating,
            ':feedback_text' => $feedbackText,
        ]);

        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
