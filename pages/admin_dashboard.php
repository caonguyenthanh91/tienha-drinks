<?php
declare(strict_types=1);

$pdo = db();
$categories = get_categories();

$fromDate = trim((string)($_GET['from_date'] ?? date('Y-m-01')));
$toDate = trim((string)($_GET['to_date'] ?? date('Y-m-d')));
$categoryId = (int)($_GET['category_id'] ?? 0);
$skuKeyword = trim((string)($_GET['sku'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));

$validStatuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
if ($statusFilter !== '' && !in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}

$where = ['1=1'];
$params = [];

if ($fromDate !== '') {
    $where[] = 'DATE(o.created_at) >= :from_date';
    $params[':from_date'] = $fromDate;
}

if ($toDate !== '') {
    $where[] = 'DATE(o.created_at) <= :to_date';
    $params[':to_date'] = $toDate;
}

if ($statusFilter !== '') {
    $where[] = 'o.status = :status_filter';
    $params[':status_filter'] = $statusFilter;
}

if ($categoryId > 0) {
    $where[] = 'EXISTS (
        SELECT 1
        FROM order_items oi_cat
        INNER JOIN products p_cat ON p_cat.id = oi_cat.product_id
        WHERE oi_cat.order_id = o.id AND p_cat.category_id = :category_id
    )';
    $params[':category_id'] = $categoryId;
}

if ($skuKeyword !== '') {
    $where[] = 'EXISTS (
        SELECT 1
        FROM order_items oi_sku
        INNER JOIN products p_sku ON p_sku.id = oi_sku.product_id
        WHERE oi_sku.order_id = o.id AND p_sku.sku LIKE :sku_keyword
    )';
    $params[':sku_keyword'] = '%' . $skuKeyword . '%';
}

$whereSql = implode(' AND ', $where);

$stmtOverview = $pdo->prepare('SELECT
    COUNT(*) AS total_orders,
    COALESCE(SUM(o.final_total), 0) AS total_revenue,
    SUM(CASE WHEN o.status = "pending" THEN 1 ELSE 0 END) AS pending_orders,
    SUM(CASE WHEN o.status = "shipping" THEN 1 ELSE 0 END) AS shipping_orders,
    SUM(CASE WHEN o.status = "completed" THEN 1 ELSE 0 END) AS completed_orders
FROM orders o
WHERE ' . $whereSql);
$stmtOverview->execute($params);
$overview = $stmtOverview->fetch() ?: [
    'total_orders' => 0,
    'total_revenue' => 0,
    'pending_orders' => 0,
    'shipping_orders' => 0,
    'completed_orders' => 0,
];

$stmtOrders = $pdo->prepare('SELECT o.id, o.order_code, o.customer_name, o.customer_phone, o.status, o.final_total, o.payment_method, o.created_at
FROM orders o
WHERE ' . $whereSql . '
ORDER BY o.id DESC
LIMIT 100');
$stmtOrders->execute($params);
$orders = $stmtOrders->fetchAll();

$stmtTopProducts = $pdo->prepare('SELECT
    COALESCE(p.sku, "") AS sku,
    oi.product_name,
    SUM(oi.quantity) AS sold_qty,
    SUM(oi.line_total) AS revenue
FROM orders o
INNER JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
WHERE ' . $whereSql . '
GROUP BY p.sku, oi.product_name
ORDER BY sold_qty DESC, revenue DESC
LIMIT 10');
$stmtTopProducts->execute($params);
$topProducts = $stmtTopProducts->fetchAll();
?>

<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <span class="section-eyebrow">Admin Dashboard</span>
            <h2 class="mb-0">Thống kê đơn hàng</h2>
        </div>
        <a class="btn btn-outline-success" href="<?= e(app_url('index.php?page=admin_orders')) ?>">Sang trang xử lý đơn</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin_dashboard">
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" name="from_date" value="<?= e($fromDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" name="to_date" value="<?= e($toDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Danh mục</label>
                    <select class="form-select" name="category_id">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mã hàng (SKU)</label>
                    <input type="text" class="form-control" name="sku" value="<?= e($skuKeyword) ?>" placeholder="VD: CF-01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <?php foreach ($validStatuses as $status): ?>
                            <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                                <?= e(order_status_label($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-success" type="submit">Lọc dữ liệu</button>
                    <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin_dashboard')) ?>">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><span>Tổng đơn</span><strong><?= (int)$overview['total_orders'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Đơn chờ xác nhận</span><strong><?= (int)$overview['pending_orders'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Đơn đang giao</span><strong><?= (int)$overview['shipping_orders'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Đã hoàn thành</span><strong><?= (int)$overview['completed_orders'] ?></strong></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">Doanh thu theo bộ lọc</h5>
            <div class="fs-4 fw-bold text-success"><?= e(format_currency((float)$overview['total_revenue'])) ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>Danh sách đơn theo bộ lọc</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$orders): ?>
                                    <tr>
                                        <td colspan="4" class="text-muted">Không có đơn hàng phù hợp bộ lọc.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($order['order_code']) ?></strong>
                                            <small class="d-block text-muted"><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></small>
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>Top sản phẩm theo bộ lọc</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Mã hàng</th>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-end">SL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$topProducts): ?>
                                    <tr>
                                        <td colspan="3" class="text-muted">Không có dữ liệu sản phẩm.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($topProducts as $item): ?>
                                    <tr>
                                        <td><?= e($item['sku'] !== '' ? (string)$item['sku'] : 'N/A') ?></td>
                                        <td>
                                            <?= e($item['product_name']) ?>
                                            <small class="d-block text-muted"><?= e(format_currency((float)$item['revenue'])) ?></small>
                                        </td>
                                        <td class="text-end fw-semibold"><?= (int)$item['sold_qty'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
