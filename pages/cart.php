<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('coupon_code', $_POST)) {
        $couponCode = strtoupper(trim((string)$_POST['coupon_code']));
        $_SESSION['cart_coupon_code'] = $couponCode;
    }

    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] ?? [] as $productId => $qty) {
            update_cart_quantity((int)$productId, (int)$qty);
        }
    }

    if (isset($_POST['remove'])) {
        update_cart_quantity((int)$_POST['remove'], 0);
    }
}

$items = cart_items();
$totals = cart_totals();
$availableCoupons = [];

try {
    $stmt = db()->query("SELECT code, title, discount_type, discount_value, min_order_value FROM coupons WHERE is_active = 1 AND NOW() BETWEEN start_at AND end_at ORDER BY created_at DESC");
    $availableCoupons = $stmt->fetchAll();
} catch (Throwable $e) {
    $availableCoupons = [];
}

$selectedCouponCode = strtoupper(trim((string)($_SESSION['cart_coupon_code'] ?? '')));
$hasSelectedCoupon = false;
foreach ($availableCoupons as $coupon) {
    if (strtoupper((string)$coupon['code']) === $selectedCouponCode) {
        $hasSelectedCoupon = true;
        break;
    }
}

if (!$hasSelectedCoupon) {
    $selectedCouponCode = '';
    $_SESSION['cart_coupon_code'] = '';
}
?>
<div class="container" data-reveal>
    <h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

    <?php if (!$items): ?>
        <div class="alert alert-info text-center py-4">
            <p class="mb-2">Giỏ hàng đang trống. Hãy chọn món bạn yêu thích!</p>
            <a class="btn btn-success" href="<?= e(app_url('index.php?page=products')) ?>">→ Mua ngay</a>
        </div>
    <?php else: ?>
        <form method="post" id="cartForm">
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-end">Giá</th>
                        <th style="width: 120px;">Số lượng</th>
                        <th class="text-end">Tạm tính</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="js-cart-body">
                    <?php foreach ($items as $item): ?>
                        <?php $linePrice = effective_price($item) * (int)$item['quantity']; ?>
                        <tr class="js-cart-row" data-product-id="<?= (int)$item['product_id'] ?>" data-unit-price="<?= e((string)effective_price($item)) ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['name']) ?>" width="64" height="64" class="rounded-2 object-fit-cover">
                                    <div>
                                        <strong><?= e($item['name']) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end"><?= e(format_currency(effective_price($item))) ?></td>
                            <td>
                                <input type="number" min="0" class="form-control form-control-sm js-cart-qty" name="qty[<?= (int)$item['product_id'] ?>]" value="<?= (int)$item['quantity'] ?>" style="max-width: 100px;">
                            </td>
                            <td class="text-end fw-bold js-line-total"><?= e(format_currency($linePrice)) ?></td>
                            <td class="text-end">
                                <button name="remove" value="<?= (int)$item['product_id'] ?>" class="btn btn-sm btn-outline-danger js-remove-item" type="submit">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-lg-5">
                    <div class="card border-0">
                        <div class="card-body">
                            <h6 class="mb-3">Tóm tắt đơn hàng</h6>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                <span>Tạm tính</span>
                                <strong id="js-cart-subtotal"><?= e(format_currency($totals['subtotal'])) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span id="js-cart-shipping-label"><?= $totals['shipping'] > 0 ? 'Vận chuyển' : 'Vận chuyển (miễn phí)' ?></span>
                                <strong id="js-cart-shipping"><?= e(format_currency($totals['shipping'])) ?></strong>
                            </div>
                            <div class="mb-3 pb-3 border-bottom">
                                <label for="js-coupon-select" class="form-label mb-2">Mã coupon</label>
                                <select id="js-coupon-select" name="coupon_code" class="form-select form-select-sm">
                                    <option value="">-- Chọn mã giảm giá --</option>
                                    <?php foreach ($availableCoupons as $coupon): ?>
                                        <?php
                                        $couponCode = strtoupper((string)$coupon['code']);
                                        $isPercent = (string)$coupon['discount_type'] === 'percent';
                                        $discountLabel = $isPercent
                                            ? rtrim(rtrim(number_format((float)$coupon['discount_value'], 2, '.', ''), '0'), '.') . '%'
                                            : format_currency((float)$coupon['discount_value']);
                                        ?>
                                        <option value="<?= e($couponCode) ?>" <?= $couponCode === $selectedCouponCode ? 'selected' : '' ?>>
                                            <?= e($couponCode . ' - ' . (string)$coupon['title'] . ' (Giảm ' . $discountLabel . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="js-coupon-hint" class="form-text mt-2"></div>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-success" id="js-coupon-discount-row" style="display: none;">
                                <span id="js-coupon-discount-label">Giảm giá coupon</span>
                                <strong id="js-coupon-discount-amount">-0 VND</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-4 fw-bold">
                                <span>Tổng cộng</span>
                                <span class="text-success fs-5" id="js-cart-total"><?= e(format_currency($totals['total'])) ?></span>
                            </div>
                            <div class="d-grid gap-2">
                                <button name="update" value="1" class="btn btn-outline-success" type="submit">Cập nhật giỏ</button>
                                <a class="btn btn-success btn-lg" href="<?= e(app_url('index.php?page=checkout')) ?>">Tiếp tục thanh toán</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php if ($items): ?>
<script>
(function () {
    const form = document.getElementById('cartForm');
    if (!form) {
        return;
    }

    const currency = new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    });

    const removedProductIds = new Set();
    let syncTimer = null;
    let syncing = false;
    let pendingSync = false;

    const subtotalEl = document.getElementById('js-cart-subtotal');
    const shippingEl = document.getElementById('js-cart-shipping');
    const shippingLabelEl = document.getElementById('js-cart-shipping-label');
    const couponSelectEl = document.getElementById('js-coupon-select');
    const couponHintEl = document.getElementById('js-coupon-hint');
    const couponDiscountRowEl = document.getElementById('js-coupon-discount-row');
    const couponDiscountLabelEl = document.getElementById('js-coupon-discount-label');
    const couponDiscountAmountEl = document.getElementById('js-coupon-discount-amount');
    const totalEl = document.getElementById('js-cart-total');

    const coupons = <?= json_encode(array_reduce($availableCoupons, static function (array $carry, array $coupon): array {
        $carry[strtoupper((string)$coupon['code'])] = [
            'code' => strtoupper((string)$coupon['code']),
            'type' => (string)$coupon['discount_type'],
            'value' => (float)$coupon['discount_value'],
            'minOrderValue' => (float)$coupon['min_order_value'],
        ];
        return $carry;
    }, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    function toVndText(value) {
        return formatMoney(value).replace('₫', 'VND').trim();
    }

    function updateCouponHint(message, isError) {
        if (!couponHintEl) {
            return;
        }
        couponHintEl.textContent = message;
        couponHintEl.classList.remove('text-danger', 'text-success');
        couponHintEl.classList.add(isError ? 'text-danger' : 'text-success');
    }

    function getSelectedCoupon() {
        if (!couponSelectEl) {
            return null;
        }
        const code = String(couponSelectEl.value || '').toUpperCase();
        if (!code || !Object.prototype.hasOwnProperty.call(coupons, code)) {
            return null;
        }
        return coupons[code];
    }

    const formatMoney = (value) => currency.format(Math.max(0, value));

    function sanitizeQuantity(input) {
        const numeric = Number.parseInt(input.value, 10);
        const quantity = Number.isFinite(numeric) && numeric >= 0 ? numeric : 0;
        input.value = String(quantity);
        return quantity;
    }

    function cartRows() {
        return Array.from(form.querySelectorAll('.js-cart-row'));
    }

    function extractProductId(inputName) {
        const match = inputName.match(/qty\[(\d+)\]/);
        return match ? match[1] : '';
    }

    function recalculateTotals() {
        const rows = cartRows();
        let subtotal = 0;
        let activeItems = 0;

        rows.forEach((row) => {
            const qtyInput = row.querySelector('.js-cart-qty');
            if (!qtyInput) {
                return;
            }

            const quantity = sanitizeQuantity(qtyInput);
            const unitPrice = Number.parseFloat(row.dataset.unitPrice || '0') || 0;
            const lineTotal = unitPrice * quantity;

            const lineTotalEl = row.querySelector('.js-line-total');
            if (lineTotalEl) {
                lineTotalEl.textContent = formatMoney(lineTotal);
            }

            subtotal += lineTotal;
            if (quantity > 0) {
                activeItems += 1;
            }
        });

        let shipping = activeItems > 0 ? 20000 : 0;
        if (activeItems === 0) {
            shipping = 0;
        }
        const selectedCoupon = getSelectedCoupon();
        const totalBeforeCoupon = subtotal + shipping;
        let couponDiscount = 0;

        if (selectedCoupon) {
            if (subtotal < selectedCoupon.minOrderValue) {
                updateCouponHint('Đơn tối thiểu ' + toVndText(selectedCoupon.minOrderValue) + ' để áp dụng coupon này.', true);
            } else {
                if (selectedCoupon.type === 'percent') {
                    couponDiscount = totalBeforeCoupon * (selectedCoupon.value / 100);
                } else {
                    couponDiscount = selectedCoupon.value;
                }
                couponDiscount = Math.min(couponDiscount, totalBeforeCoupon);
                updateCouponHint('Đã áp dụng mã ' + selectedCoupon.code + '.', false);
            }
        } else {
            updateCouponHint('Chọn mã giảm giá để được chiết khấu.', false);
        }

        const total = Math.max(0, totalBeforeCoupon - couponDiscount);

        if (subtotalEl) {
            subtotalEl.textContent = formatMoney(subtotal);
        }
        if (shippingEl) {
            shippingEl.textContent = formatMoney(shipping);
        }
        if (shippingLabelEl) {
            shippingLabelEl.textContent = shipping > 0 ? 'Vận chuyển' : 'Vận chuyển (miễn phí)';
        }
        if (couponDiscountRowEl && couponDiscountLabelEl && couponDiscountAmountEl) {
            if (couponDiscount > 0 && selectedCoupon) {
                couponDiscountRowEl.style.display = '';
                couponDiscountLabelEl.textContent = 'Giảm giá coupon (' + selectedCoupon.code + ')';
                couponDiscountAmountEl.textContent = '-' + formatMoney(couponDiscount);
            } else {
                couponDiscountRowEl.style.display = 'none';
                couponDiscountAmountEl.textContent = '-' + formatMoney(0);
            }
        }
        if (totalEl) {
            totalEl.textContent = formatMoney(total);
        }
    }

    function buildPayload() {
        const payload = new URLSearchParams();
        payload.append('update', '1');

        form.querySelectorAll('.js-cart-qty').forEach((input) => {
            payload.append(input.name, String(sanitizeQuantity(input)));
        });

        removedProductIds.forEach((productId) => {
            payload.append(`qty[${productId}]`, '0');
        });

        payload.append('coupon_code', couponSelectEl ? String(couponSelectEl.value || '') : '');

        return payload;
    }

    async function syncCartToServer() {
        if (syncing) {
            pendingSync = true;
            return;
        }

        syncing = true;
        pendingSync = false;

        try {
            await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                },
                credentials: 'same-origin',
                body: buildPayload().toString(),
            });

            if (cartRows().length === 0) {
                window.location.reload();
                return;
            }
        } catch (error) {
            // Keep UI responsive even if sync fails; next interaction will retry.
        } finally {
            syncing = false;
            if (pendingSync) {
                syncCartToServer();
            }
        }
    }

    function queueSync() {
        if (syncTimer) {
            clearTimeout(syncTimer);
        }
        syncTimer = window.setTimeout(syncCartToServer, 250);
    }

    form.querySelectorAll('.js-cart-qty').forEach((input) => {
        input.addEventListener('input', function () {
            sanitizeQuantity(this);
            recalculateTotals();
            queueSync();
        });
    });

    if (couponSelectEl) {
        couponSelectEl.addEventListener('change', function () {
            recalculateTotals();
            queueSync();
        });
    }

    form.querySelectorAll('.js-remove-item').forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const row = this.closest('.js-cart-row');
            if (!row) {
                return;
            }

            const qtyInput = row.querySelector('.js-cart-qty');
            if (qtyInput) {
                const productId = extractProductId(qtyInput.name);
                if (productId !== '') {
                    removedProductIds.add(productId);
                }
            }

            row.remove();
            recalculateTotals();
            queueSync();
        });
    });

    recalculateTotals();
})();
</script>
<?php endif; ?>
