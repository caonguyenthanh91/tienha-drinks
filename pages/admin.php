<?php
declare(strict_types=1);

$overview = get_admin_overview();
$recentOrders = db()->query('SELECT order_code, customer_name, final_total, status, created_at FROM orders ORDER BY id DESC LIMIT 6')->fetchAll();
$products = db()->query('SELECT p.id, p.name, p.price, p.sale_price, p.stock, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT 8')->fetchAll();
?>
<div class="container">
    <h2 class="mb-4">Bang dieu khien quan tri</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metric-card"><span>San pham</span><strong><?= (int)$overview['total_products'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Don hang</span><strong><?= (int)$overview['total_orders'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Khach hang</span><strong><?= (int)$overview['total_customers'] ?></strong></div></div>
        <div class="col-md-3"><div class="metric-card"><span>Doanh thu</span><strong><?= e(format_currency((float)$overview['total_revenue'])) ?></strong></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>Don hang gan day</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Ma don</th><th>Khach</th><th>Trang thai</th><th class="text-end">Tong</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?= e($order['order_code']) ?></td>
                                    <td><?= e($order['customer_name']) ?></td>
                                    <td><?= e($order['status']) ?></td>
                                    <td class="text-end"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5>San pham quan ly nhanh</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Ten</th><th>Danh muc</th><th class="text-end">Gia</th><th class="text-end">Ton</th></tr></thead>
                            <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= e($product['name']) ?></td>
                                    <td><?= e($product['category_name']) ?></td>
                                    <td class="text-end"><?= e(format_currency((float)($product['sale_price'] ?: $product['price']))) ?></td>
                                    <td class="text-end"><?= (int)$product['stock'] ?></td>
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
