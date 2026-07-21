<?php
declare(strict_types=1);

$items = cart_items();
$totals = cart_totals();
$message = '';
$selectedCouponCode = strtoupper(trim((string)($_SESSION['cart_coupon_code'] ?? '')));
$selectedCoupon = get_active_coupon_by_code($selectedCouponCode);
$defaultScheduleDate = date('Y-m-d');
$defaultScheduleTime = '12:00';
$scheduleDateValue = $defaultScheduleDate;
$scheduleTimeValue = $defaultScheduleTime;
$rememberedPhone = preg_replace('/\D+/', '', (string)($_COOKIE['tienha_phone'] ?? '')) ?? '';
$phoneValue = $rememberedPhone;

if (!$selectedCoupon) {
    $selectedCouponCode = '';
    $_SESSION['cart_coupon_code'] = '';
}

$previewPricing = cart_totals_with_discount(0, $selectedCouponCode);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items) {
    $customerName = trim((string)($_POST['customer_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $phoneValue = $phone;
    $email = trim((string)($_POST['email'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));
    $paymentMethod = (string)($_POST['payment_method'] ?? 'cod');
    $scheduleDateValue = trim((string)($_POST['scheduled_date'] ?? $defaultScheduleDate));
    $scheduleTimeValue = trim((string)($_POST['scheduled_time'] ?? $defaultScheduleTime));

    $scheduledAt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $scheduleDateValue . ' ' . $scheduleTimeValue);
    $scheduledAtValid = $scheduledAt instanceof DateTimeImmutable
        && $scheduledAt->format('Y-m-d') === $scheduleDateValue
        && $scheduledAt->format('H:i') === $scheduleTimeValue;

    if ($customerName !== '' && $phone !== '' && $address !== '' && $scheduledAtValid) {
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

            $totalsWithDiscount = cart_totals_with_discount($discountPercent, $selectedCouponCode);

            $stmtOrder = $pdo->prepare('INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, customer_email, shipping_address, note, payment_method, status, subtotal, shipping_fee, discount_amount, final_total, created_at) VALUES (:order_code, :customer_id, :customer_name, :customer_phone, :customer_email, :shipping_address, :note, :payment_method, :status, :subtotal, :shipping_fee, :discount_amount, :final_total, :created_at)');
            $orderCode = 'TH' . $scheduledAt->format('YmdHis') . random_int(10, 99);
            $stmtOrder->execute([
                ':order_code' => $orderCode,
                ':customer_id' => $customerId,
                ':customer_name' => $customerName,
                ':customer_phone' => $phone,
                ':customer_email' => $email,
                ':shipping_address' => $address,
                ':note' => $note,
                ':payment_method' => $paymentMethod,
                ':status' => 'pending',
                ':subtotal' => $totalsWithDiscount['subtotal'],
                ':shipping_fee' => $totalsWithDiscount['shipping'],
                ':discount_amount' => $totalsWithDiscount['discount'],
                ':final_total' => $totalsWithDiscount['total'],
                ':created_at' => $scheduledAt->format('Y-m-d H:i:s'),
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
            $_SESSION['cart_coupon_code'] = '';
            $message = 'Đặt hàng thành công! Mã đơn: ' . $orderCode;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = 'Không thể tạo đơn hàng: ' . $e->getMessage();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = 'Vui lòng nhập đầy đủ thông tin và chọn ngày giờ đặt trước hợp lệ.';
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
                        <small>Hãy cung cấp đầy đủ thông tin bên dưới để tích điểm thành viên</small>
                        <form method="post" class="row g-3" id="checkoutForm">
                            
                            <div class="col-md-6">
                                <label class="form-label fw-600">Số điện thoại</label>
                                <input type="text" name="phone" id="customerPhone" class="form-control" placeholder="0912345678" value="<?= e($phoneValue) ?>" required>
                                <div id="tierDisplay" class="mt-2" style="display: none;">
                                    <small class="d-block text-success fw-600">
                                        <span id="tierIcon">⭐</span>
                                        Hạng: <span id="tierName"></span>
                                        <span id="discountBadge" class="badge bg-success"></span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Người nhận</label>
                                <input type="text" name="customer_name" id="customerName" class="form-control" placeholder="Họ và tên" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Email</label>
                                <input type="email" name="email" id="customerEmail" class="form-control" placeholder="your@email.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Địa chỉ giao hàng</label>
                                <textarea name="address" id="customerAddress" class="form-control" rows="3" placeholder="Ví dụ: 123 Nguyễn Huệ, Quận 1, TP.HCM" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Ghi chú cho đơn hàng</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Ít đá, giao giờ hành chính..." maxlength="255"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="schedule-picker-card">
                                    <div class="schedule-picker-copy">
                                        <span class="schedule-chip">Đặt trước</span>
                                        <h6 class="mb-1">Chọn thời điểm giao đơn thật vừa ý</h6>
                                        <p class="mb-0">Mặc định là hôm nay lúc 12:00 trưa. Hệ thống sẽ dùng mốc này cho ngày giờ đơn hàng.</p>
                                    </div>
                                    <div class="row g-3 align-items-end mt-1">
                                        <div class="col-md-7">
                                            <label class="form-label fw-600">Ngày nhận hàng</label>
                                            <input
                                                type="date"
                                                name="scheduled_date"
                                                class="form-control schedule-input"
                                                value="<?= e($scheduleDateValue) ?>"
                                                min="<?= e($defaultScheduleDate) ?>"
                                                required
                                            >
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label fw-600">Giờ nhận hàng</label>
                                            <input
                                                type="time"
                                                name="scheduled_time"
                                                class="form-control schedule-input"
                                                value="<?= e($scheduleTimeValue) ?>"
                                                step="300"
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-600">Phương thức thanh toán</label>
                                <select class="form-select" name="payment_method" id="paymentMethodSelect">
                                    <option value="cod">💵 Thanh toán khi nhận hàng (COD)</option>
                                    <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                                    <option value="momo">📱 Ví MoMo</option>
                                </select>
                            </div>
                            <div class="col-12" id="bankTransferQrWrap" style="display: none;">
                                <div class="p-3 border rounded-3 bg-light text-center">
                                    <p class="mb-2 fw-600">Quét mã QR để chuyển khoản</p>
                                    <img src="<?= e(app_url('assets/img/checkout_qr.jpg')) ?>" alt="QR chuyển khoản ngân hàng" class="img-fluid rounded" style="max-width: 320px;">
                                </div>
                            </div>
                            <div class="col-12 pt-3 d-grid d-md-flex justify-content-md-end gap-2">
                                <a href="<?= e(app_url('index.php?page=cart')) ?>" class="btn btn-outline-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-success btn-lg" id="checkoutSubmitBtn">✓ Xác nhận đặt hàng</button>
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
                            <span>Tạm tính:</span>
                            <span><?= e(format_currency($totals['subtotal'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>Vận chuyển:</span>
                            <span><?= e(format_currency($totals['shipping'])) ?></span>
                        </div>
                        <div id="couponDiscountRow" class="d-flex justify-content-between mb-2 text-success small" style="<?= $previewPricing['coupon_discount'] > 0 ? '' : 'display: none;' ?>">
                            <span id="couponDiscountLabel">Mã giảm giá<?= $previewPricing['coupon_code'] !== '' ? ' (' . e($previewPricing['coupon_code']) . ')' : '' ?>:</span>
                            <span id="couponDiscountAmount" class="text-danger">-<?= e(format_currency($previewPricing['coupon_discount'])) ?></span>
                        </div>
                        <div id="tierDiscountRow" class="d-flex justify-content-between mb-2 text-success small" style="display: none;">
                            <span>Chiết khấu thành viên <span id="discountPercent"></span>:</span>
                            <span id="discountAmount" class="text-danger">-0 VND</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 text-success">
                            <span>Tổng cộng:</span>
                            <span id="totalPrice"><?= e(format_currency($previewPricing['total'])) ?></span>
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
let couponDiscount = <?= (float)($previewPricing['coupon_discount'] ?? 0) ?>;
let currentDiscount = 0;
let currentDiscountPercent = 0;

const phoneInput = document.getElementById('customerPhone');
const customerNameInput = document.getElementById('customerName');
const customerEmailInput = document.getElementById('customerEmail');
const customerAddressInput = document.getElementById('customerAddress');
const tierDisplay = document.getElementById('tierDisplay');
const tierDiscountRow = document.getElementById('tierDiscountRow');
const couponDiscountRow = document.getElementById('couponDiscountRow');
const paymentMethodSelect = document.getElementById('paymentMethodSelect');
const bankTransferQrWrap = document.getElementById('bankTransferQrWrap');
const checkoutForm = document.getElementById('checkoutForm');
const checkoutSubmitBtn = document.getElementById('checkoutSubmitBtn');
const scheduledDateInput = checkoutForm?.querySelector('input[name="scheduled_date"]') || null;
const scheduledTimeInput = checkoutForm?.querySelector('input[name="scheduled_time"]') || null;
const rememberedPhoneCookieName = 'tienha_phone';
const rememberedPhoneCookiePath = '<?= e(rtrim(app_url(''), '/')) !== '' ? e(rtrim(app_url(''), '/')) : '/' ?>';
let allowCheckoutSubmit = false;

function persistPhoneCookie(value) {
    const normalizedValue = String(value || '').replace(/\D+/g, '');
    if (normalizedValue === '') {
        return;
    }

    const expires = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString();
    document.cookie = `${rememberedPhoneCookieName}=${encodeURIComponent(normalizedValue)}; expires=${expires}; path=${rememberedPhoneCookiePath}; SameSite=Lax`;
}

if (checkoutSubmitBtn) {
    checkoutSubmitBtn.addEventListener('click', function () {
        if (phoneInput) {
            persistPhoneCookie(phoneInput.value);
        }
        allowCheckoutSubmit = true;
    });
}

if (checkoutForm) {
    checkoutForm.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }

        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.tagName === 'TEXTAREA') {
            return;
        }

        event.preventDefault();
    });

    checkoutForm.addEventListener('submit', function (event) {
        syncScheduledTimeConstraints();

        if (scheduledTimeInput && !scheduledTimeInput.checkValidity()) {
            event.preventDefault();
            scheduledTimeInput.reportValidity();
            allowCheckoutSubmit = false;
            return;
        }

        if (!allowCheckoutSubmit) {
            event.preventDefault();
            return;
        }

        if (phoneInput) {
            persistPhoneCookie(phoneInput.value);
        }

        allowCheckoutSubmit = false;
    });
}

function getRoundedCurrentTime() {
    const now = new Date();
    now.setSeconds(0, 0);

    const minutes = now.getMinutes();
    const roundedMinutes = Math.ceil(minutes / 5) * 5;
    now.setMinutes(roundedMinutes);

    const tomorrow = new Date();
    tomorrow.setHours(24, 0, 0, 0);

    return {
        minTime: `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`,
        spillsToNextDay: now >= tomorrow,
    };
}

function syncScheduledTimeConstraints() {
    if (!scheduledDateInput || !scheduledTimeInput) {
        return;
    }

    const today = new Date();
    const todayValue = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    if (scheduledDateInput.value === todayValue) {
        const { minTime, spillsToNextDay } = getRoundedCurrentTime();

        if (spillsToNextDay) {
            scheduledTimeInput.min = '00:00';
            scheduledTimeInput.setCustomValidity('Hôm nay đã qua khung giờ nhận đơn. Vui lòng chọn ngày giao tiếp theo.');
            return;
        }

        scheduledTimeInput.min = minTime;

        if (scheduledTimeInput.value !== '' && scheduledTimeInput.value < minTime) {
            scheduledTimeInput.setCustomValidity('Vui lòng chọn giờ hiện tại hoặc muộn hơn cho đơn giao hôm nay.');
        } else {
            scheduledTimeInput.setCustomValidity('');
        }

        return;
    }

    scheduledTimeInput.min = '00:00';
    scheduledTimeInput.setCustomValidity('');
}

function toggleBankTransferQr() {
    if (!paymentMethodSelect || !bankTransferQrWrap) {
        return;
    }
    bankTransferQrWrap.style.display = paymentMethodSelect.value === 'bank_transfer' ? '' : 'none';
}

if (paymentMethodSelect) {
    paymentMethodSelect.addEventListener('change', toggleBankTransferQr);
    toggleBankTransferQr();
}

if (scheduledDateInput && scheduledTimeInput) {
    scheduledDateInput.addEventListener('change', syncScheduledTimeConstraints);
    scheduledTimeInput.addEventListener('input', syncScheduledTimeConstraints);
    syncScheduledTimeConstraints();
}

if (phoneInput) {
    let checkTimer;
    phoneInput.addEventListener('input', function() {
        persistPhoneCookie(this.value);
        clearTimeout(checkTimer);
        const phone = this.value.trim();

        if (phone.length < 7) {
            tierDisplay.style.display = 'none';
            clearCustomerInfo();
            resetDiscount();
            return;
        }

        checkTimer = setTimeout(() => checkCustomerTier(phone), 500);
    });

    if (phoneInput.value.trim().length >= 7) {
        checkCustomerTier(phoneInput.value.trim());
    }
}

async function checkCustomerTier(phone) {
    try {
        const response = await fetch(`config/api.php?action=check_customer_tier&phone=${encodeURIComponent(phone)}`);
        const json = await response.json();

        if (json.success && json.tier) {
            fillCustomerInfo(json.customer || null);
            showTier(json.tier);
            applyDiscount(json.tier.discount_percent || 0);
        } else {
            tierDisplay.style.display = 'none';
            clearCustomerInfo();
            resetDiscount();
        }
    } catch (error) {
        console.error('Error checking tier:', error);
    }
}

function fillCustomerInfo(customer) {
    if (!customer) {
        clearCustomerInfo();
        return;
    }

    if (customerNameInput) {
        customerNameInput.value = customer.full_name || '';
    }
    if (customerEmailInput) {
        customerEmailInput.value = customer.email || '';
    }
    if (customerAddressInput) {
        customerAddressInput.value = customer.default_address || '';
    }
}

function clearCustomerInfo() {
    if (customerNameInput) {
        customerNameInput.value = '';
    }
    if (customerEmailInput) {
        customerEmailInput.value = '';
    }
    if (customerAddressInput) {
        customerAddressInput.value = '';
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
        tierDiscountRow.style.display = 'flex';
    } else {
        tierDiscountRow.style.display = 'none';
    }

    updateTotal();
}

function resetDiscount() {
    currentDiscount = 0;
    currentDiscountPercent = 0;
    tierDiscountRow.style.display = 'none';
    updateTotal();
}

function updateTotal() {
    if (couponDiscountRow) {
        couponDiscountRow.style.display = couponDiscount > 0 ? 'flex' : 'none';
    }
    const newTotal = Math.max(0, cartSubtotal + shippingFee - couponDiscount - currentDiscount);
    document.getElementById('totalPrice').textContent = formatVnd(newTotal);
}

function formatVnd(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0)) + ' VND';
}
</script>

<style>
.schedule-picker-card {
    position: relative;
    padding: 1.25rem;
    border: 1px solid rgba(29, 111, 66, 0.16);
    border-radius: 1.25rem;
    background:
        radial-gradient(circle at top right, rgba(255, 214, 102, 0.35), transparent 35%),
        linear-gradient(135deg, rgba(242, 117, 76, 0.12), rgba(29, 111, 66, 0.08) 55%, rgba(255, 255, 255, 0.96));
    box-shadow: 0 20px 45px rgba(29, 111, 66, 0.08);
    overflow: hidden;
}

.schedule-picker-copy {
    margin-bottom: 0.75rem;
}

.schedule-picker-copy h6 {
    font-size: 1.05rem;
    color: #174c33;
}

.schedule-picker-copy p {
    color: #5f6f66;
    font-size: 0.95rem;
}

.schedule-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.78);
    color: #ba5a2a;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.schedule-input {
    min-height: 3rem;
    border: 1px solid rgba(29, 111, 66, 0.18);
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.9);
}

.schedule-input:focus {
    border-color: rgba(29, 111, 66, 0.45);
    box-shadow: 0 0 0 0.2rem rgba(29, 111, 66, 0.12);
}

@media (max-width: 767.98px) {
    .schedule-picker-card {
        padding: 1rem;
    }
}
</style>
