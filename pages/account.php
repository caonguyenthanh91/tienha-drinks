<?php
declare(strict_types=1);

$tiers = get_customer_tiers();
$phoneInput = trim((string)($_GET['phone'] ?? ''));
$normalizedPhone = preg_replace('/\D+/', '', $phoneInput) ?? '';
$searched = array_key_exists('phone', $_GET);
$errorMessage = '';
$customer = null;
$orders = [];
$fallbackProfile = null;

if ($searched) {
    if ($normalizedPhone === '') {
        $errorMessage = 'Vui lòng nhập số điện thoại hợp lệ (chỉ gồm chữ số).';
    } else {
        $customer = get_customer_by_phone($normalizedPhone);

        if ($customer) {
            $stmtOrders = db()->prepare('SELECT order_code, status, final_total, created_at, customer_name, customer_email, shipping_address, note FROM orders WHERE customer_id = :customer_id OR customer_phone = :phone ORDER BY id DESC LIMIT 10');
            $stmtOrders->execute([
                ':customer_id' => (int)$customer['id'],
                ':phone' => $normalizedPhone,
            ]);
        } else {
            $stmtOrders = db()->prepare('SELECT order_code, status, final_total, created_at, customer_name, customer_email, shipping_address, note FROM orders WHERE customer_phone = :phone ORDER BY id DESC LIMIT 10');
            $stmtOrders->execute([':phone' => $normalizedPhone]);
        }

        $orders = $stmtOrders->fetchAll();

        if (!$customer && $orders) {
            $latestOrder = $orders[0];
            $fallbackProfile = [
                'full_name' => (string)($latestOrder['customer_name'] ?? ''),
                'email' => (string)($latestOrder['customer_email'] ?? ''),
                'phone' => $normalizedPhone,
                'default_address' => (string)($latestOrder['shipping_address'] ?? ''),
                'tier_name' => 'Khách mới',
                'points' => 0,
            ];
        }

        if (!$customer && !$orders) {
            $errorMessage = 'Không tìm thấy đơn hàng với số điện thoại này.';
        }
    }
}

$currentOrder = null;
foreach ($orders as $order) {
    if (!in_array((string)$order['status'], ['completed', 'cancelled'], true)) {
        $currentOrder = $order;
        break;
    }
}

if ($currentOrder === null && $orders !== []) {
    $currentOrder = $orders[0];
}
?>
<div class="container" data-reveal>
    <div class="mb-4">
        <span class="section-eyebrow">Khu vực cá nhân</span>
        <h2>Tra cứu tài khoản theo số điện thoại</h2>
        <p class="text-muted mb-0">Nhập đúng số điện thoại đã đặt hàng</p>
    </div>

    <div class="card border-0 mb-4" data-reveal>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end" id="accountLookupForm" novalidate>
                <input type="hidden" name="page" value="account">
                <div class="col-md-8">
                    <label for="lookupPhone" class="form-label fw-600">Số điện thoại</label>
                    <input
                        type="text"
                        class="form-control"
                        id="lookupPhone"
                        name="phone"
                        value="<?= e($phoneInput) ?>"
                        placeholder="Ví dụ: 0912345678"
                        inputmode="numeric"
                        autocomplete="tel"
                        required
                    >
                    <!-- <small class="text-muted">Chỉ giữ lại chữ số khi tìm kiếm.</small> -->
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-success">Tra cứu</button>
                </div>
            </form>
            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-warning mt-3 mb-0"><?= e($errorMessage) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$searched): ?>
        <div class="alert alert-info">Vui lòng nhập số điện thoại để xem thông tin tài khoản, đơn hàng và chương trình thành viên.</div>
    <?php elseif ($customer || $orders): ?>

    <?php $profile = $customer ?: $fallbackProfile; ?>

    <div class="row g-4">
        <!-- Thông tin tài khoản -->
        <div class="col-lg-4" data-reveal>
            <div class="card border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin khách hàng</h5>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Họ tên</small>
                        <p class="fw-600 mb-0"><?= e((string)($profile['full_name'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Email</small>
                        <p class="mb-0"><?= e((string)($profile['email'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Số điện thoại</small>
                        <p class="mb-0"><?= e((string)($profile['phone'] ?? $normalizedPhone)) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Địa chỉ mặc định</small>
                        <p class="mb-0"><?= e((string)($profile['default_address'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Hạng hiện tại</small>
                        <p class="mb-0">
                            <span class="badge text-bg-success fw-bold"><?= e((string)($profile['tier_name'] ?? 'Khách mới')) ?></span>
                            <span class="text-success fw-600">● <?= (int)($profile['points'] ?? 0) ?> điểm</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lịch sử đơn hàng -->
        <div class="col-lg-8" data-reveal style="--delay: 0.1s">
            <div class="card border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Trạng thái đơn hàng</h5>
                    <?php if ($currentOrder): ?>
                        <div class="alert alert-light border mb-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Đơn hiện tại</small>
                                    <strong><?= e($currentOrder['order_code']) ?></strong>
                                </div>
                                <span class="badge <?= match($currentOrder['status']) {
                                    'pending' => 'text-bg-warning',
                                    'confirmed' => 'text-bg-info',
                                    'shipping' => 'text-bg-primary',
                                    'completed' => 'text-bg-success',
                                    'cancelled' => 'text-bg-danger',
                                    default => 'text-bg-secondary'
                                } ?>">
                                    <?= match($currentOrder['status']) {
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'shipping' => 'Đang giao',
                                        'completed' => 'Đã giao',
                                        'cancelled' => 'Đã hủy',
                                        default => e((string)$currentOrder['status'])
                                    } ?>
                                </span>
                            </div>
                            <?php if (trim((string)($currentOrder['note'] ?? '')) !== ''): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Phản hồi từ cửa hàng</small>
                                    <small class="d-block" style="white-space: pre-line;"><?= e((string)$currentOrder['note']) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($orders): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th>Ngày tạo</th>
                                    <th>Phản hồi cửa hàng</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong><?= e($order['order_code']) ?></strong></td>
                                        <td>
                                            <span class="badge <?= match($order['status']) {
                                                'pending' => 'text-bg-warning',
                                                'confirmed' => 'text-bg-info',
                                                'shipping' => 'text-bg-primary',
                                                'completed' => 'text-bg-success',
                                                'cancelled' => 'text-bg-danger',
                                                default => 'text-bg-secondary'
                                            } ?>">
                                                <?= match($order['status']) {
                                                    'pending' => 'Chờ xác nhận',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'shipping' => 'Đang giao',
                                                    'completed' => 'Đã giao',
                                                    'cancelled' => 'Đã hủy',
                                                    default => e($order['status'])
                                                } ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                        <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                                        <td>
                                            <?php if (trim((string)($order['note'] ?? '')) !== ''): ?>
                                                <small class="d-block" style="white-space: pre-line;"><?= e((string)$order['note']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Chưa có phản hồi</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Khách hàng chưa có đơn hàng nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Các hạng thành viên -->
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="mb-3">Chương trình thành viên</h5>
                    <div class="row g-3">
                        <?php foreach ($tiers as $index => $tier): ?>
                            <div class="col-md-4" data-reveal style="--delay: <?= 0.15 + $index * 0.08 ?>s">
                                <div class="tier-card h-100">
                                    <span class="tier-badge">Hạng <?= chr(65 + $index) ?></span>
                                    <h6 class="mt-2"><?= e($tier['name']) ?></h6>
                                    <p class="text-success fw-600 small">Từ <?= e(format_currency((float)$tier['min_spending'])) ?></p>
                                    <p class="small text-muted"><?= e($tier['benefits']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('accountLookupForm');
    const input = document.getElementById('lookupPhone');
    if (!form || !input) {
        return;
    }

    const normalize = () => {
        input.value = input.value.replace(/\D+/g, '');
    };

    input.addEventListener('input', normalize);
    form.addEventListener('submit', normalize);
});
</script>
