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
            $cleanPhone = preg_replace('/\s+/', '', $phone);
            $customer = get_customer_by_phone($cleanPhone);
            $discountPercent = 0;
            $customerId = null;

            if ($customer) {
                $customerId = (int)$customer['id'];
                $discountPercent = (float)($customer['discount_percent'] ?? 0);
            }

            $totalsWithDiscount = cart_totals_with_discount($discountPercent);

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
                ':subtotal' => $totalsWithDiscount['subtotal'],
                ':shipping_fee' => $totalsWithDiscount['shipping'],
                ':discount_amount' => $totalsWithDiscount['discount'],
                ':final_total' => $totalsWithDiscount['total'],
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
<div class="container" data-reveal>
    <div class="mb-4">
        <span class="section-eyebrow">Hoàn thiện đơn hàng</span>
        <h2>Thanh toán</h2>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert <?= str_contains($message, 'thành công') ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$items): ?>
        <div class="alert alert-info text-center py-4">
            <p class="mb-2">Giỏ hàng của bạn đang trống.</p>
            <a class="btn btn-success" href="<?= e(app_url('index.php?page=products')) ?>">← Quay lại mua hàng</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-7" data-reveal>
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="mb-4">📋 Thông tin giao hàng</h5>
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Người nhận</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Họ và tên" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Số điện thoại</label>
                                <input type="text" name="phone" id="customerPhone" class="form-control" placeholder="0912345678" required>
                                <div id="tierDisplay" class="mt-2" style="display: none;">
                                    <small class="d-block text-success fw-600">
                                        <span id="tierIcon">⭐</span>
                                        Hạng: <span id="tierName"></span>
                                        <span id="discountBadge" class="badge bg-success"></span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Địa chỉ giao hàng</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Ví dụ: 123 Nguyễn Huệ, Quận 1, TP.HCM" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Phương thức thanh toán</label>
                                <select class="form-select" name="payment_method">
                                    <option value="cod">💵 Thanh toán khi nhận hàng (COD)</option>
                                    <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                                    <option value="momo">📱 Ví MoMo</option>
                                </select>
                            </div>
                            <div class="col-12 pt-3 d-grid d-md-flex justify-content-md-end gap-2">
                                <a href="<?= e(app_url('index.php?page=cart')) ?>" class="btn btn-outline-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-success btn-lg">✓ Xác nhận đặt hàng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5" data-reveal style="--delay: 0.1s">
                <div class="card border-0 position-sticky" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="mb-3">🛍️ Đơn hàng của bạn</h5>
                        <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($items as $item): ?>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom small">
                                    <span><?= e($item['name']) ?> <span class="text-muted">×<?= (int)$item['quantity'] ?></span></span>
                                    <span><?= e(format_currency(effective_price($item) * (int)$item['quantity'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>Vận chuyển:</span>
                            <span><?= e(format_currency($totals['shipping'])) ?></span>
                        </div>
                        <div id="discountRow" class="d-flex justify-content-between mb-2 text-success small" style="display: none;">
                            <span>Chiết khấu <span id="discountPercent"></span>:</span>
                            <span id="discountAmount" class="text-danger">-0 VND</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 text-success">
                            <span>Tổng cộng:</span>
                            <span id="totalPrice"><?= e(format_currency($totals['total'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
let cartSubtotal = <?= (float)$totals['subtotal'] ?>;
let shippingFee = <?= (float)$totals['shipping'] ?>;
let currentDiscount = 0;
let currentDiscountPercent = 0;

const phoneInput = document.getElementById('customerPhone');
const tierDisplay = document.getElementById('tierDisplay');
const discountRow = document.getElementById('discountRow');

if (phoneInput) {
    let checkTimer;
    phoneInput.addEventListener('input', function() {
        clearTimeout(checkTimer);
        const phone = this.value.trim();

        if (phone.length < 7) {
            tierDisplay.style.display = 'none';
            resetDiscount();
            return;
        }

        checkTimer = setTimeout(() => checkCustomerTier(phone), 500);
    });
}

async function checkCustomerTier(phone) {
    try {
        const response = await fetch(`config/api.php?action=check_customer_tier&phone=${encodeURIComponent(phone)}`);
        const json = await response.json();

        if (json.success && json.tier) {
            showTier(json.tier);
            applyDiscount(json.tier.discount_percent || 0);
        } else {
            tierDisplay.style.display = 'none';
            resetDiscount();
        }
    } catch (error) {
        console.error('Error checking tier:', error);
    }
}

function showTier(tier) {
    document.getElementById('tierName').textContent = tier.name || 'Khách hàng';
    document.getElementById('discountBadge').textContent = tier.discount_percent ? `${tier.discount_percent}% giảm` : '';
    tierDisplay.style.display = 'block';
}

function applyDiscount(discountPercent) {
    currentDiscountPercent = discountPercent || 0;
    const subtotalWithShip = cartSubtotal + shippingFee;
    currentDiscount = (subtotalWithShip * currentDiscountPercent) / 100;

    if (currentDiscountPercent > 0) {
        document.getElementById('discountPercent').textContent = `(${currentDiscountPercent}%)`;
        document.getElementById('discountAmount').textContent = `-${formatVnd(currentDiscount)}`;
        discountRow.style.display = 'flex';
    } else {
        discountRow.style.display = 'none';
    }

    updateTotal();
}

function resetDiscount() {
    currentDiscount = 0;
    currentDiscountPercent = 0;
    discountRow.style.display = 'none';
    updateTotal();
}

function updateTotal() {
    const newTotal = Math.max(0, cartSubtotal + shippingFee - currentDiscount);
    document.getElementById('totalPrice').textContent = formatVnd(newTotal);
}

function formatVnd(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0)) + ' VND';
}
</script>
