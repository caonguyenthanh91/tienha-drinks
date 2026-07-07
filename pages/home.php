<?php
declare(strict_types=1);

$featuredProducts = get_featured_products(8);
$categories = get_categories();
?>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-badge">Khoanh khac Dang nho</span>
                <h1>Nuoc uong tuoi ngon, nang luong xanh moi ngay</h1>
                <p>TienHa Drinks mang den ca phe, nuoc ep va smoothie lam tu trai cay tuoi. Dat nhanh, giao tan noi trong khu vuc noi thanh.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-success btn-lg">Kham pha menu</a>
                    <a href="<?= e(app_url('index.php?page=contact')) ?>" class="btn btn-outline-success btn-lg">Lien he ngay</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-card">
                    <h4>Uu dai hom nay</h4>
                    <ul>
                        <li>Giam 15% cho don dau tien.</li>
                        <li>Free ship cho don tu 300.000 VND.</li>
                        <li>Tang 2x diem thuong cho thanh vien moi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container mt-5">
    <div class="section-heading d-flex justify-content-between align-items-center mb-3">
        <h2>Danh muc noi bat</h2>
        <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-sm btn-outline-success">Xem tat ca</a>
    </div>
    <div class="row g-3">
        <?php foreach ($categories as $category): ?>
            <div class="col-sm-6 col-lg-3">
                <a class="category-card text-decoration-none" href="<?= e(app_url('index.php?page=products&category=' . urlencode($category['slug']))) ?>">
                    <div class="category-icon">🍹</div>
                    <h5><?= e($category['name']) ?></h5>
                    <p class="mb-0 text-muted small"><?= e($category['description']) ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container mt-5">
    <div class="section-heading d-flex justify-content-between align-items-center mb-3">
        <h2>San pham noi bat</h2>
    </div>
    <div class="row g-4">
        <?php foreach ($featuredProducts as $product): ?>
            <?php $price = effective_price($product); ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card card h-100 border-0 shadow-sm">
                    <img src="<?= e($product['thumbnail']) ?>" class="card-img-top" alt="<?= e($product['name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <small class="text-success fw-semibold"><?= e($product['category_name']) ?></small>
                        <h6 class="mt-1"><?= e($product['name']) ?></h6>
                        <div class="price-wrap mb-3">
                            <?php if ($product['sale_price']): ?>
                                <span class="old-price"><?= e(format_currency((float)$product['price'])) ?></span>
                            <?php endif; ?>
                            <span class="new-price"><?= e(format_currency($price)) ?></span>
                        </div>
                        <div class="d-grid gap-2 mt-auto">
                            <button class="btn btn-outline-success btn-sm" onclick="openQuickView(<?= (int)$product['id'] ?>)">Xem nhanh</button>
                            <button class="btn btn-success btn-sm" onclick="addToCart(<?= (int)$product['id'] ?>, 1)">Them gio hang</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
