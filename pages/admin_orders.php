<?php
declare(strict_types=1);

$message = '';
$messageType = 'success';

$selectedDate = trim((string)($_GET['date'] ?? ''));
$preset = trim((string)($_GET['preset'] ?? ''));
$deliveryMode = trim((string)($_GET['delivery_mode'] ?? ''));

$validDeliveryModes = ['scheduled', 'immediate'];
if ($deliveryMode !== '' && !in_array($deliveryMode, $validDeliveryModes, true)) {
    $deliveryMode = '';
}

if ($selectedDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = '';
}

$rangeStart = null;
$rangeEnd = null;

$today = new DateTimeImmutable('today');
switch ($preset) {
    case 'yesterday':
        $rangeStart = $today->modify('-1 day')->format('Y-m-d 00:00:00');
        $rangeEnd = $today->format('Y-m-d 00:00:00');
        break;
    case 'today':
        $rangeStart = $today->format('Y-m-d 00:00:00');
        $rangeEnd = $today->modify('+1 day')->format('Y-m-d 00:00:00');
        break;
    case 'week':
        $rangeStart = $today->modify('monday this week')->format('Y-m-d 00:00:00');
        $rangeEnd = $today->modify('+1 day')->format('Y-m-d 00:00:00');
        break;
    default:
        $preset = '';
        if ($selectedDate !== '') {
            $pickedDay = DateTimeImmutable::createFromFormat('Y-m-d', $selectedDate);
            if ($pickedDay instanceof DateTimeImmutable) {
                $rangeStart = $pickedDay->format('Y-m-d 00:00:00');
                $rangeEnd = $pickedDay->modify('+1 day')->format('Y-m-d 00:00:00');
            } else {
                $selectedDate = '';
            }
        }
        break;
}

$buildAdminOrdersUrl = static function (array $overrides = []) use ($selectedDate, $preset, $deliveryMode): string {
    $params = [
        'page' => 'admin_orders',
    ];

    if ($selectedDate !== '') {
        $params['date'] = $selectedDate;
    }
    if ($preset !== '') {
        $params['preset'] = $preset;
    }
    if ($deliveryMode !== '') {
        $params['delivery_mode'] = $deliveryMode;
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }
        $params[$key] = (string)$value;
    }

    return app_url('index.php?' . http_build_query($params));
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_note') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $note = trim((string)($_POST['note'] ?? ''));

    if ($orderId <= 0) {
        $message = 'Dữ liệu phản hồi không hợp lệ.';
        $messageType = 'danger';
    } else {
        $stmt = db()->prepare('UPDATE orders SET note = :note WHERE id = :id');
        $stmt->execute([
            ':note' => $note !== '' ? $note : null,
            ':id' => $orderId,
        ]);
        $message = 'Đã cập nhật phản hồi đơn hàng.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $nextStatus = (string)($_POST['next_status'] ?? '');

    if ($orderId <= 0 || $nextStatus === '') {
        $message = 'Dữ liệu cập nhật trạng thái không hợp lệ.';
        $messageType = 'danger';
    } else {
        $result = update_order_status_by_admin($orderId, $nextStatus);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

$whereClauses = ['1=1'];
$queryParams = [];

if ($rangeStart !== null && $rangeEnd !== null) {
    $whereClauses[] = 'created_at >= :range_start AND created_at < :range_end';
    $queryParams[':range_start'] = $rangeStart;
    $queryParams[':range_end'] = $rangeEnd;
}

if ($deliveryMode === 'scheduled') {
    $whereClauses[] = 'created_at > NOW()';
} elseif ($deliveryMode === 'immediate') {
    $whereClauses[] = 'created_at <= NOW()';
}

$query = 'SELECT id, order_code, customer_name, customer_phone, customer_email, shipping_address, payment_method, final_total, status, note, created_at, updated_at FROM orders WHERE ' . implode(' AND ', $whereClauses);

$query .= ' ORDER BY CASE WHEN status IN ("completed", "cancelled") THEN 1 ELSE 0 END ASC, FIELD(status, "pending", "confirmed", "shipping", "completed", "cancelled") ASC, id DESC LIMIT 120';

$stmt = db()->prepare($query);
$stmt->execute($queryParams);
$incomingOrders = $stmt->fetchAll();

$activeOrders = [];
$archivedOrders = [];

$orderTimingLabel = static function (string $scheduledAt): string {
    $scheduledTimestamp = strtotime($scheduledAt);
    if ($scheduledTimestamp === false) {
        return 'Giao ngay';
    }

    return $scheduledTimestamp > time() ? 'Đặt trước' : 'Giao ngay';
};

$orderTimingBadgeClass = static function (string $scheduledAt) use ($orderTimingLabel): string {
    return $orderTimingLabel($scheduledAt) === 'Đặt trước'
        ? 'text-bg-primary'
        : 'text-bg-secondary';
};

foreach ($incomingOrders as $order) {
    if (in_array((string)$order['status'], ['completed', 'cancelled'], true)) {
        $archivedOrders[] = $order;
        continue;
    }
    $activeOrders[] = $order;
}
?>
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <span class="section-eyebrow">Order Desk</span>
            <h2 class="mb-0">Theo dõi và phản hồi đơn hàng</h2>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="<?= e(app_url('index.php?page=admin_dashboard')) ?>">Xem dashboard</a>
            <a class="btn btn-success" href="<?= e(app_url('index.php?page=admin_products')) ?>">Quản lý sản phẩm</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= e($messageType) ?> alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-2">Lọc theo ngày</h5>
            <p class="text-muted small mb-3">Chọn ngày cụ thể hoặc dùng nút nhanh để xem đơn hôm qua, hôm nay và tuần này.</p>
            <form method="get" class="row g-2 align-items-end admin-order-filter">
                <input type="hidden" name="page" value="admin_orders">
                <input type="hidden" name="preset" value="">
                <div class="col-sm-6 col-lg-3">
                    <label for="adminOrderDate" class="form-label mb-1">Ngày</label>
                    <input id="adminOrderDate" type="date" name="date" class="form-control" value="<?= e($selectedDate) ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="adminDeliveryMode" class="form-label mb-1">Loại giao</label>
                    <select id="adminDeliveryMode" name="delivery_mode" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="scheduled" <?= $deliveryMode === 'scheduled' ? 'selected' : '' ?>>Đặt trước</option>
                        <option value="immediate" <?= $deliveryMode === 'immediate' ? 'selected' : '' ?>>Giao ngay</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3 d-grid d-sm-flex gap-2">
                    <button class="btn btn-success" type="submit">Áp dụng</button>
                    <a class="btn btn-outline-secondary" href="<?= e($buildAdminOrdersUrl(['date' => null, 'preset' => null, 'delivery_mode' => null])) ?>">Tất cả</a>
                </div>
                <div class="col-12 col-lg-3 d-flex flex-wrap gap-2">
                    <a class="btn <?= $preset === 'yesterday' ? 'btn-dark' : 'btn-outline-dark' ?>" href="<?= e($buildAdminOrdersUrl(['preset' => 'yesterday', 'date' => null])) ?>">Hôm qua</a>
                    <a class="btn <?= $preset === 'today' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= e($buildAdminOrdersUrl(['preset' => 'today', 'date' => null])) ?>">Hôm nay</a>
                    <a class="btn <?= $preset === 'week' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= e($buildAdminOrdersUrl(['preset' => 'week', 'date' => null])) ?>">Tuần này</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-2">Đơn cần xử lý trước (<?= count($activeOrders) ?>)</h5>
            <p class="text-muted small mb-3">Nút trạng thái được thiết kế dạng chạm nhanh. Phản hồi hỗ trợ nhiều dòng và hiển thị cho khách ở trang tài khoản.</p>
            <?php if ($activeOrders === []): ?>
                <p class="text-muted mb-0">Không có đơn cần xử lý trong bộ lọc hiện tại.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Địa chỉ</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Tổng</th>
                                <th>Xử lý nhanh</th>
                                <th>Phản hồi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($activeOrders as $order): ?>
                            <tr>
                                <td>
                                    <?php
                                        $scheduledAtRaw = (string)$order['created_at'];
                                        $scheduledAtLabel = date('d/m/Y H:i', strtotime($scheduledAtRaw));
                                    ?>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none order-detail-trigger" data-order-id="<?= (int)$order['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal" style="font-size: inherit;">
                                        <strong><?= e($order['order_code']) ?></strong>
                                    </button>
                                    <small class="d-block admin-scheduled-label">Thời gian đặt trước</small>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                        <small class="d-inline-flex align-items-center gap-1 admin-scheduled-pill"><?= e($scheduledAtLabel) ?></small>
                                        <span class="badge <?= e($orderTimingBadgeClass($scheduledAtRaw)) ?>"><?= e($orderTimingLabel($scheduledAtRaw)) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?= e($order['customer_name']) ?>
                                    <small class="d-block text-muted"><?= e($order['customer_phone']) ?></small>
                                    <small class="d-block text-muted"><?= e((string)($order['customer_email'] ?? '')) ?></small>
                                </td>
                                <td>
                                    <small><?= e($order['shipping_address']) ?></small>
                                    <small class="d-block text-muted">Thanh toán: <?= e(payment_method_label((string)$order['payment_method'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge <?= e(order_status_badge_class((string)$order['status'])) ?>">
                                        <?= e(order_status_label((string)$order['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                <td style="min-width: 190px;">
                                    <?php $nextOptions = allowed_next_order_statuses((string)$order['status']); ?>
                                    <?php if ($nextOptions): ?>
                                        <div class="admin-status-actions">
                                            <?php foreach ($nextOptions as $status): ?>
                                                <?php
                                                    $touchBtnClass = match ($status) {
                                                        'confirmed' => 'btn-outline-info',
                                                        'shipping' => 'btn-outline-primary',
                                                        'completed' => 'btn-outline-success',
                                                        'cancelled' => 'btn-outline-danger',
                                                        default => 'btn-outline-secondary',
                                                    };
                                                ?>
                                                <form method="post" action="<?= e($buildAdminOrdersUrl()) ?>">
                                                    <input type="hidden" name="action" value="update_order_status">
                                                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                                    <input type="hidden" name="next_status" value="<?= e($status) ?>">
                                                    <button class="btn <?= e($touchBtnClass) ?> admin-touch-btn w-100" type="submit">
                                                        <?= e(order_status_label($status)) ?>
                                                    </button>
                                                </form>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">Đã khóa</small>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width: 280px;">
                                    <form method="post" action="<?= e($buildAdminOrdersUrl()) ?>" class="d-flex flex-column gap-2">
                                        <input type="hidden" name="action" value="update_order_note">
                                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                        <textarea name="note" class="form-control admin-note-input" rows="2" placeholder="Nhập phản hồi khách hàng...\nVí dụ: Đơn giao trong 30 phút."><?= e((string)($order['note'] ?? '')) ?></textarea>
                                        <button class="btn btn-primary admin-touch-btn" type="submit">Gửi phản hồi</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-2">Đơn đã hoàn thành hoặc đã hủy (<?= count($archivedOrders) ?>)</h5>
            <p class="text-muted small mb-3">Khu vực riêng cho các đơn đã kết thúc để tránh che khuất đơn đang cần xử lý.</p>
            <?php if ($archivedOrders === []): ?>
                <p class="text-muted mb-0">Không có đơn đã hoàn thành hoặc đã hủy trong bộ lọc hiện tại.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Tổng</th>
                                <th>Phản hồi đã gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($archivedOrders as $order): ?>
                            <tr>
                                <td>
                                    <?php
                                        $scheduledAtRaw = (string)$order['created_at'];
                                        $scheduledAtLabel = date('d/m/Y H:i', strtotime($scheduledAtRaw));
                                    ?>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none order-detail-trigger" data-order-id="<?= (int)$order['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal" style="font-size: inherit;">
                                        <strong><?= e($order['order_code']) ?></strong>
                                    </button>
                                    <small class="d-block admin-scheduled-label">Thời gian đặt trước</small>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                        <small class="d-inline-flex align-items-center gap-1 admin-scheduled-pill"><?= e($scheduledAtLabel) ?></small>
                                        <span class="badge <?= e($orderTimingBadgeClass($scheduledAtRaw)) ?>"><?= e($orderTimingLabel($scheduledAtRaw)) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?= e($order['customer_name']) ?>
                                    <small class="d-block text-muted"><?= e($order['customer_phone']) ?></small>
                                </td>
                                <td>
                                    <span class="badge <?= e(order_status_badge_class((string)$order['status'])) ?>">
                                        <?= e(order_status_label((string)$order['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold"><?= e(format_currency((float)$order['final_total'])) ?></td>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
                <div id="orderDetailContent" style="display: none;">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Mã đơn</h6>
                            <p class="mb-0 fw-bold" id="orderCode"></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Thời gian đặt trước</h6>
                            <p class="mb-1 fw-semibold" id="orderScheduledAt"></p>
                            <span class="badge" id="orderDeliveryTimingBadge"></span>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Phương thức thanh toán</h6>
                            <p class="mb-0" id="orderPaymentMethod"></p>
                        </div>
                    </div>
                    <h6 class="text-muted mb-3">Danh sách sản phẩm</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên hàng</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsTable">
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-light border">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Tổng cộng:</span>
                            <span class="fw-bold text-success fs-5" id="orderTotal"></span>
                        </div>
                    </div>
                </div>
                <div id="orderDetailError" class="alert alert-danger" style="display: none;">
                    Không thể tải thông tin đơn hàng. Vui lòng thử lại.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderDetailModal = document.getElementById('orderDetailModal');
    if (orderDetailModal) {
        orderDetailModal.addEventListener('show.bs.modal', async (e) => {
            const trigger = e.relatedTarget;
            const orderId = trigger?.dataset?.orderId;

            if (!orderId) return;

            const loadingEl = document.getElementById('orderDetailLoading');
            const contentEl = document.getElementById('orderDetailContent');
            const errorEl = document.getElementById('orderDetailError');

            loadingEl.style.display = 'block';
            contentEl.style.display = 'none';
            errorEl.style.display = 'none';

            try {
                const response = await fetch(`<?= app_url('includes/api_order_details.php') ?>?order_id=${orderId}`);
                if (!response.ok) throw new Error('Network error');
                
                const data = await response.json();
                if (!data.success) throw new Error('Invalid data');

                // Điền dữ liệu
                document.getElementById('orderCode').textContent = data.order_code;
                document.getElementById('orderScheduledAt').textContent = new Date(data.scheduled_at).toLocaleString('vi-VN');
                const deliveryBadge = document.getElementById('orderDeliveryTimingBadge');
                deliveryBadge.className = `badge ${data.delivery_timing_badge_class}`;
                deliveryBadge.textContent = data.delivery_timing_label;
                document.getElementById('orderPaymentMethod').textContent = data.payment_method_label;
                document.getElementById('orderTotal').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.final_total);

                const itemsTable = document.getElementById('orderItemsTable');
                itemsTable.innerHTML = '';
                
                data.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.unit_price)}</td>
                        <td class="text-end fw-semibold">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.line_total)}</td>
                    `;
                    itemsTable.appendChild(row);
                });

                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
            } catch (err) {
                console.error('Error loading order details:', err);
                loadingEl.style.display = 'none';
                errorEl.style.display = 'block';
            }
        });
    }
});
</script>

<style>
.admin-scheduled-label {
    margin-top: 0.35rem;
    color: #6c7a72;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.admin-scheduled-pill {
    margin-top: 0.2rem;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    background: rgba(24, 119, 73, 0.1);
    color: #174c33;
    font-size: 0.82rem;
    font-weight: 600;
}
</style>
