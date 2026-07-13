<?php
declare(strict_types=1);

$category = $_GET['category'] ?? null;
$keyword = trim((string)($_GET['q'] ?? ''));
$sort = $_GET['sort'] ?? 'newest';
$products = get_products($category ?: null, $keyword !== '' ? $keyword : null, $sort);
?>
<div class="container">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php require __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form class="row g-2" method="get" action="<?= e(app_url('index.php')) ?>">
                        <input type="hidden" name="page" value="products">
                        <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?= e($category) ?>">
                        <?php endif; ?>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="q" value="<?= e($keyword) ?>" placeholder="Tìm kiếm sản phẩm...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="sort">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-success">Lọc</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <?php if (!$products): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong>Không tìm thấy sản phẩm phù hợp.</strong> Hãy thử lại với từ khóa khác hoặc quay lại danh mục.
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($products as $index => $product): ?>
                    <?php $price = effective_price($product); ?>
                    <div class="col-sm-6 col-xl-4" data-reveal style="--delay: <?= ($index % 3) * 0.08 ?>s">
                        <div class="product-card card h-100 border-0">
                            <div class="product-thumb">
                                <img src="<?= e($product['thumbnail']) ?>" class="card-img-top" alt="<?= e($product['name']) ?>">
                                <?php if ($product['sale_price']): ?>
                                    <span class="badge-sale">Giảm giá</span>
                                <?php endif; ?>
                                <button class="quick-view-btn" onclick="openQuickView(<?= (int)$product['id'] ?>)">Xem nhanh</button>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <small class="text-success fw-semibold"><?= e($product['category_name']) ?></small>
                                <h6 class="mt-1"><?= e($product['name']) ?></h6>
                                <p class="small text-muted"><?= e($product['short_description']) ?></p>
                                <div class="price-wrap mb-3">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="old-price"><?= e(format_currency((float)$product['price'])) ?></span>
                                    <?php endif; ?>
                                    <span class="new-price"><?= e(format_currency($price)) ?></span>
                                </div>
                                <button class="btn btn-success btn-sm mt-auto add-cart-btn" onclick="addToCart(<?= (int)$product['id'] ?>, 1)">
                                    <span>Thêm vào giỏ</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
