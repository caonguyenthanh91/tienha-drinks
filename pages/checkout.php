<?php
declare(strict_types=1);

$items = cart_items();
$totals = cart_totals();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items) {
    $customerName = trim((string)($_POST['customer_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $paymentMethod = (string)($_POST['payment_method'] ?? 'cod');

    if ($customerName !== '' && $phone !== '' && $address !== '') {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $customerId = 1;
            $stmtOrder = $pdo->prepare('INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, customer_email, shipping_address, payment_method, status, subtotal, shipping_fee, discount_amount, final_total, created_at) VALUES (:order_code, :customer_id, :customer_name, :customer_phone, :customer_email, :shipping_address, :payment_method, :status, :subtotal, :shipping_fee, :discount_amount, :final_total, NOW())');
            $orderCode = 'TH' . date('YmdHis') . random_int(10, 99);
            $stmtOrder->execute([
                ':order_code' => $orderCode,
                ':customer_id' => $customerId,
                ':customer_name' => $customerName,
                ':customer_phone' => $phone,
                ':customer_email' => $email,
                ':shipping_address' => $address,
                ':payment_method' => $paymentMethod,
                ':status' => 'pending',
                ':subtotal' => $totals['subtotal'],
                ':shipping_fee' => $totals['shipping'],
                ':discount_amount' => $totals['discount'],
                ':final_total' => $totals['total'],
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)');
            foreach ($items as $item) {
                $unitPrice = effective_price($item);
                $quantity = (int)$item['quantity'];
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':product_name' => $item['name'],
                    ':unit_price' => $unitPrice,
                    ':quantity' => $quantity,
                    ':line_total' => $unitPrice * $quantity,
                ]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            $message = 'Đặt hàng thành công! Mã đơn: ' . $orderCode;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = 'Không thể tạo đơn hàng: ' . $e->getMessage();
        }
    }
}
?>
<div class="container">
    <h2 class="mb-4">Thanh toán</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (!$items): ?>
        <div class="alert alert-warning">Không có sản phẩm trong giỏ hàng.</div>
        <a class="btn btn-success" href="<?= e(app_url('index.php?page=products')) ?>">Chọn sản phẩm</a>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Người nhận</label>
                                <input type="text" name="customer_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Địa chỉ giao hàng</label>
                                <textarea name="address" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" name="payment_method">
                                    <option value="cod">Thanh toán khi nhận hàng</option>
                                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                    <option value="momo">Ví MoMo</option>
                                </select>
                            </div>
                            <div class="col-12 d-grid d-md-flex justify-content-md-end">
                                <button class="btn btn-success">Xác nhận đặt hàng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Đơn hàng của bạn</h5>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span><?= e($item['name']) ?> x <?= (int)$item['quantity'] ?></span>
                                <span><?= e(format_currency(effective_price($item) * (int)$item['quantity'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between"><span>Tổng</span><strong class="text-success"><?= e(format_currency($totals['total'])) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
