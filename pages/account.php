<?php
declare(strict_types=1);

$tiers = get_customer_tiers();
$customer = db()->query('SELECT c.*, t.name AS tier_name FROM customers c LEFT JOIN customer_tiers t ON t.id = c.tier_id ORDER BY c.id LIMIT 1')->fetch();
$orders = db()->query('SELECT order_code, status, final_total, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<div class="container">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h4>Tài khoản</h4>
                    <?php if ($customer): ?>
                        <p class="mb-1"><strong><?= e($customer['full_name']) ?></strong></p>
                        <p class="mb-1 text-muted"><?= e($customer['email']) ?></p>
                        <p class="mb-1 text-muted"><?= e($customer['phone']) ?></p>
                        <p class="mb-0">Hạng hiện tại: <span class="badge text-bg-success"><?= e($customer['tier_name'] ?? 'Mặc định') ?></span></p>
                        <p class="mb-0">Điểm tích lũy: <strong><?= (int)$customer['points'] ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5>Lịch sử đơn hàng</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr><th>Mã đơn</th><th>Trạng thái</th><th class="text-end">Tổng tiền</th><th>Ngày tạo</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= e($order['order_code']) ?></td>
                                    <td><span class="badge text-bg-light"><?= e($order['status']) ?></span></td>
                                    <td class="text-end"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                    <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5>Các hạng thành viên</h5>
                    <div class="row g-3">
                        <?php foreach ($tiers as $tier): ?>
                            <div class="col-md-4">
                                <div class="tier-card">
                                    <h6><?= e($tier['name']) ?></h6>
                                    <p class="small mb-1">Mốc chi tiêu: <?= e(format_currency((float)$tier['min_spending'])) ?></p>
                                    <p class="small mb-0">Quyền lợi: <?= e($tier['benefits']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
