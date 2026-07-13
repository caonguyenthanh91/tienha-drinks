<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
<div class="container" data-reveal>
    <h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

    <?php if (!$items): ?>
        <div class="alert alert-info text-center py-4">
            <p class="mb-2">Giỏ hàng đang trống. Hãy chọn món bạn yêu thích!</p>
            <a class="btn btn-success" href="<?= e(app_url('index.php?page=products')) ?>">→ Mua ngay</a>
        </div>
    <?php else: ?>
        <form method="post">
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
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php $linePrice = effective_price($item) * (int)$item['quantity']; ?>
                        <tr>
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
                                <input type="number" min="0" class="form-control form-control-sm" name="qty[<?= (int)$item['product_id'] ?>]" value="<?= (int)$item['quantity'] ?>" style="max-width: 100px;">
                            </td>
                            <td class="text-end fw-bold"><?= e(format_currency($linePrice)) ?></td>
                            <td class="text-end">
                                <button name="remove" value="<?= (int)$item['product_id'] ?>" class="btn btn-sm btn-outline-danger" type="submit">Xóa</button>
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
                                <strong><?= e(format_currency($totals['subtotal'])) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span><?= $totals['shipping'] > 0 ? 'Vận chuyển' : 'Vận chuyển (miễn phí)' ?></span>
                                <strong><?= e(format_currency($totals['shipping'])) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-4 fw-bold">
                                <span>Tổng cộng</span>
                                <span class="text-success fs-5"><?= e(format_currency($totals['total'])) ?></span>
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
