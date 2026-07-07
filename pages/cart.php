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
<div class="container">
    <h2 class="mb-4">Gio hang</h2>

    <?php if (!$items): ?>
        <div class="alert alert-info">Gio hang dang trong. Hay chon mon ban yeu thich.</div>
        <a class="btn btn-success" href="<?= e(app_url('index.php?page=products')) ?>">Mua ngay</a>
    <?php else: ?>
        <form method="post">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>San pham</th>
                        <th class="text-end">Gia</th>
                        <th style="width: 120px;">So luong</th>
                        <th class="text-end">Tam tinh</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php $linePrice = effective_price($item) * (int)$item['quantity']; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= e($item['thumbnail']) ?>" alt="<?= e($item['name']) ?>" width="56" height="56" class="rounded object-fit-cover">
                                    <div><?= e($item['name']) ?></div>
                                </div>
                            </td>
                            <td class="text-end"><?= e(format_currency(effective_price($item))) ?></td>
                            <td>
                                <input type="number" min="0" class="form-control" name="qty[<?= (int)$item['product_id'] ?>]" value="<?= (int)$item['quantity'] ?>">
                            </td>
                            <td class="text-end fw-semibold"><?= e(format_currency($linePrice)) ?></td>
                            <td class="text-end">
                                <button name="remove" value="<?= (int)$item['product_id'] ?>" class="btn btn-sm btn-outline-danger">Xoa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Tam tinh</span><strong><?= e(format_currency($totals['subtotal'])) ?></strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Van chuyen</span><strong><?= e(format_currency($totals['shipping'])) ?></strong></div>
                            <div class="d-flex justify-content-between border-top pt-2 mb-3"><span>Tong cong</span><strong class="text-success"><?= e(format_currency($totals['total'])) ?></strong></div>
                            <div class="d-grid gap-2">
                                <button name="update" value="1" class="btn btn-outline-success">Cap nhat gio</button>
                                <a class="btn btn-success" href="<?= e(app_url('index.php?page=checkout')) ?>">Thanh toan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
