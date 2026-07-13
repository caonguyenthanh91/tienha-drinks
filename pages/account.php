<?php
declare(strict_types=1);

$tiers = get_customer_tiers();
$customer = db()->query('SELECT c.*, t.name AS tier_name FROM customers c LEFT JOIN customer_tiers t ON t.id = c.tier_id ORDER BY c.id LIMIT 1')->fetch();
$orders = db()->query('SELECT order_code, status, final_total, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<div class="container" data-reveal>
    <div class="mb-4">
        <span class="section-eyebrow">Khu vực cá nhân</span>
        <h2>Tài khoản của bạn</h2>
    </div>

    <div class="row g-4">
        <!-- Thông tin tài khoản -->
        <div class="col-lg-4" data-reveal>
            <div class="card border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">👤 Thông tin cá nhân</h5>
                    <?php if ($customer): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted">Họ tên</small>
                            <p class="fw-600 mb-0"><?= e($customer['full_name']) ?></p>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted">Email</small>
                            <p class="mb-0"><?= e($customer['email']) ?></p>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted">Số điện thoại</small>
                            <p class="mb-0"><?= e($customer['phone']) ?></p>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted">Địa chỉ mặc định</small>
                            <p class="mb-0"><?= e($customer['default_address'] ?? 'Chưa cập nhật') ?></p>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Hạng hiện tại</small>
                            <p class="mb-0">
                                <span class="badge text-bg-success fw-bold"><?= e($customer['tier_name'] ?? 'Đồng') ?></span>
                                <span class="text-success fw-600">● <?= (int)$customer['points'] ?> điểm</span>
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Chưa có thông tin tài khoản.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lịch sử đơn hàng -->
        <div class="col-lg-8" data-reveal style="--delay: 0.1s">
            <div class="card border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">📦 Lịch sử đơn hàng</h5>
                    <?php if ($orders): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th>Ngày tạo</th>
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
                                                    'pending' => '⏳ Chờ xác nhận',
                                                    'confirmed' => '✓ Đã xác nhận',
                                                    'shipping' => '🚚 Đang giao',
                                                    'completed' => '✅ Đã giao',
                                                    'cancelled' => '❌ Hủy',
                                                    default => e($order['status'])
                                                } ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                        <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Chưa có đơn hàng nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Các hạng thành viên -->
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="mb-3">⭐ Chương trình thành viên</h5>
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
</div>
