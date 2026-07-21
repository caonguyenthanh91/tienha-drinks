<?php
declare(strict_types=1);

$bestSellers = get_best_sellers(8);
$categories = get_categories();
$testimonials = get_testimonials();

$banners = [
    [
        'image' => app_url('assets/img/banners/tra-sua.png'),
        'badge' => 'Trá sữa',
        'title' => 'Trà sữa hương vị đặc trưng',
        'desc'  => 'Công thức độc quyền của M&T Quán.',
        'link'  => '#',
        'modal' => '#menuModal1',
        'cta'   => 'Menu trà sữa',
    ],
    [
        'image' => app_url('assets/img/banners/tra-trai-cay.png'),
        'badge' => 'Trà trái cây tươi',
        'title' => 'Cảm giác tươi mới mỗi ngày',
        'desc'  => 'Nguyên liệu tươi ngon tuyển chọn.',
        'link'  => '#',
        'modal' => '#menuModal2',
        'cta'   => 'Menu trà trái cây',
    ]
];

$features = [
    ['icon' => '🥤', 'title' => 'Nguyên liệu tươi', 'desc' => 'Trái cây tuyển chọn trong ngày'],
    ['icon' => '⚡', 'title' => 'Freeship từ 10 ly', 'desc' => 'Trong KCN Long Đức, An Phước'],
    ['icon' => '🎁', 'title' => 'Tích điểm giảm giá', 'desc' => 'Tích 1% trên tổng hóa đơn, trừ thẳng vào giá'],
    ['icon' => '💳', 'title' => 'Thanh toán linh hoạt', 'desc' => 'COD, chuyển khoản, Momo'],
];
?>

<!-- ============ HERO CAROUSEL ============ -->
<section class="hero-carousel">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <?php foreach ($banners as $i => $banner): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($banners as $i => $banner): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img src="<?= e($banner['image']) ?>" class="d-block w-100 hero-slide-img" alt="<?= e($banner['title']) ?>">
                    <div class="hero-overlay"></div>
                    <div class="container hero-caption">
                        <div class="hero-caption-inner">
                            <span class="hero-badge"><?= e($banner['badge']) ?></span>
                            <h1><?= e($banner['title']) ?></h1>
                            <p><?= e($banner['desc']) ?></p>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php if (!empty($banner['modal'])): ?>
                                    <a href="#" class="btn btn-success btn-lg btn-glow" data-bs-toggle="modal" data-bs-target="<?= e($banner['modal']) ?>"><?= e($banner['cta']) ?></a>
                                <?php else: ?>
                                    <a href="<?= e($banner['link']) ?>" class="btn btn-success btn-lg btn-glow"><?= e($banner['cta']) ?></a>
                                <?php endif; ?>
                                <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-outline-light btn-lg">Đặt hàng</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</section>

<!-- ============ CTA ============ -->
<section class="container mt-5" data-reveal>
    <div class="cta-banner">
        <div>
            <h3 class="mb-1">🎉 Tưng bừng khai trương 🎉</h3>
            <p class="mb-0">Từ 28.7 đến 31.7 đồng loạt giảm giá 20% cho tất cả đơn hàng.</p>
        </div>
        <!-- <a href="#" class="btn btn-light btn-lg btn-glow" data-bs-toggle="modal" data-bs-target="#menuModal1">Trà sữa</a> -->
        <!-- <a href="#" class="btn btn-light btn-lg btn-glow" data-bs-toggle="modal" data-bs-target="#menuModal2">Trà trái cây</a> -->
        <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-light btn-lg btn-glow">Đặt ngay</a>
    </div>
</section>

<!-- ============ CATEGORIES ============ -->
<section class="container mt-5" data-reveal>
    <div class="section-heading text-center mb-4">
        <span class="section-eyebrow">Danh mục</span>
        <h2>Chọn thức uống theo gu của bạn</h2>
        <p class="text-muted mb-0">Từ trà sữa đậm vị đến trà trái cây mát lạnh — luôn có món dành cho bạn.</p>
    </div>
    <div class="row g-3 g-lg-4">
        <?php foreach ($categories as $index => $cat): ?>
            <div class="col-6 col-lg" data-reveal style="--delay: <?= $index * 0.08 ?>s">
                <a class="cat-tile text-decoration-none" href="<?= e(app_url('index.php?page=products&category=' . urlencode($cat['slug']))) ?>">
                    <?php $catBg = !empty($cat['image']) ? "url('" . e($cat['image']) . "')" : 'linear-gradient(135deg, #1f8a4c, #0b5d2e)'; ?>
                    <div class="cat-tile-img" style="background-image:<?= $catBg ?>"></div>
                    <div class="cat-tile-overlay"></div>
                    <div class="cat-tile-body">
                        <h5><?= e($cat['name']) ?></h5>
                        <span class="cat-tile-cta">Xem ngay →</span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ BEST SELLERS ============ -->
<section class="container mt-5" data-reveal>
    <div class="section-heading d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
        <div>
            <span class="section-eyebrow">Được yêu thích nhất</span>
            <h2>Best seller tuần này</h2>
        </div>
        <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-outline-success">Xem tất cả sản phẩm</a>
    </div>
    <div class="row g-4">
        <?php foreach ($bestSellers as $index => $product): ?>
            <?php $price = effective_price($product); ?>
            <div class="col-6 col-lg-3" data-reveal style="--delay: <?= ($index % 4) * 0.08 ?>s">
                <div class="product-card card h-100 border-0">
                    <div class="product-thumb">
                        <img src="<?= e($product['thumbnail']) ?>" class="card-img-top" alt="<?= e($product['name']) ?>">
                        <?php if ($product['sale_price']): ?>
                            <span class="badge-sale">Giảm giá</span>
                        <?php endif; ?>
                        <?php if ($index < 3): ?>
                            <span class="badge-hot">🔥 Hot</span>
                        <?php endif; ?>
                        <button class="quick-view-btn" onclick="openQuickView(<?= (int)$product['id'] ?>)">Xem nhanh</button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <small class="text-success fw-semibold"><?= e($product['category_name']) ?></small>
                        <h6 class="mt-1"><?= e($product['name']) ?></h6>
                        <div class="price-wrap mb-3">
                            <?php if ($product['sale_price']): ?>
                                <span class="old-price"><?= e(format_currency((float)$product['price'])) ?></span>
                            <?php endif; ?>
                            <span class="new-price"><?= e(format_currency($price)) ?></span>
                        </div>
                        <?php if ((int)($product['is_active'] ?? 1) === 1): ?>
                            <button class="btn btn-success btn-sm mt-auto add-cart-btn" onclick="addToCart(<?= (int)$product['id'] ?>, 1)">
                                <span>Thêm vào giỏ</span>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm mt-auto add-cart-btn" type="button" disabled>
                                <span>Tạm ngưng</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="testimonial-section mt-5" data-reveal>
    <div class="container">
        <div class="section-heading text-center mb-4">
            <span class="section-eyebrow">Cảm nhận khách hàng</span>
            <h2>Hàng nghìn ly hạnh phúc mỗi ngày</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $index => $t): ?>
                <div class="col-lg-4" data-reveal style="--delay: <?= $index * 0.1 ?>s">
                    <div class="testimonial-card h-100">
                        <div class="testimonial-stars">
                            <?= str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']) ?>
                        </div>
                        <p class="testimonial-text">“<?= e($t['content']) ?>”</p>
                        <div class="testimonial-author">
                            <span class="testimonial-avatar"><?= e(mb_substr($t['name'], 0, 1)) ?></span>
                            <div>
                                <strong><?= e($t['name']) ?></strong>
                                <small class="d-block text-muted"><?= e($t['role']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ============ FEATURE STRIP ============ -->
<section class="container feature-strip" data-reveal>
    <div class="row g-3">
        <?php foreach ($features as $feature): ?>
            <div class="col-6 col-lg-3">
                <div class="feature-item">
                    <div class="feature-icon"><?= $feature['icon'] ?></div>
                    <div>
                        <h6 class="mb-0"><?= e($feature['title']) ?></h6>
                        <small class="text-muted"><?= e($feature['desc']) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ MENU MODAL ============ -->
<div class="modal fade" id="menuModal1" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thực đơn M&T Quán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <img src="<?= e(app_url('assets/img/menu/tra_sua_menu.png')) ?>" class="w-100" alt="Thực đơn">
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="menuModal2" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thực đơn M&T Quán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <img src="<?= e(app_url('assets/img/menu/tra_trai_cay_menu.png')) ?>" class="w-100" alt="Thực đơn">
            </div>
        </div>
    </div>
</div>
