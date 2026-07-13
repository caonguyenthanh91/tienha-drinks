<?php
declare(strict_types=1);

$bestSellers = get_best_sellers(8);
$categories = get_categories();
$testimonials = get_testimonials();

$banners = [
    [
        'image' => app_url('assets/img/banners/signature-banner.webp'),
        'badge' => 'Signature 2026',
        'title' => 'Thức uống signature pha chế mỗi ngày',
        'desc'  => 'Nguyên liệu tươi tuyển chọn, công thức độc quyền của M&T Quán.',
        'link'  => app_url('index.php?page=products'),
        'cta'   => 'Khám phá Menu',
    ],
    [
        'image' => app_url('assets/img/banners/ca-phe-banner.webp'),
        'badge' => 'Cà phê rang mộc',
        'title' => 'Đậm đà từng giọt, tỉnh táo cả ngày',
        'desc'  => 'Cà phê phin, bạc xỉu, latte đá — chuẩn gu người Việt.',
        'link'  => app_url('index.php?page=products&category=ca-phe'),
        'cta'   => 'Xem cà phê',
    ],
    [
        'image' => app_url('assets/img/banners/nuoc-ep-banner.webp'),
        'badge' => 'Healthy & tươi',
        'title' => 'Nước ép & sinh tố xanh mát mỗi ngày',
        'desc'  => 'Bổ sung vitamin, thanh lọc cơ thể, giao tận nơi nhanh chóng.',
        'link'  => app_url('index.php?page=products&category=nuoc-ep'),
        'cta'   => 'Xem nước ép',
    ],
];

$features = [
    ['icon' => '🥤', 'title' => 'Nguyên liệu tươi', 'desc' => 'Trái cây tuyển chọn trong ngày'],
    ['icon' => '⚡', 'title' => 'Giao hàng nhanh', 'desc' => 'Nội thành trong 30 phút'],
    ['icon' => '🎁', 'title' => 'Tích điểm đổi quà', 'desc' => 'Ưu đãi cho thành viên'],
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
                                <a href="<?= e($banner['link']) ?>" class="btn btn-success btn-lg btn-glow"><?= e($banner['cta']) ?></a>
                                <a href="<?= e(app_url('index.php?page=contact')) ?>" class="btn btn-outline-light btn-lg">Liên hệ đặt hàng</a>
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

<!-- ============ CATEGORIES ============ -->
<section class="container mt-5" data-reveal>
    <div class="section-heading text-center mb-4">
        <span class="section-eyebrow">Danh mục</span>
        <h2>Chọn thức uống theo gu của bạn</h2>
        <p class="text-muted mb-0">Từ cà phê đậm đà đến sinh tố mát lạnh — luôn có món dành cho bạn.</p>
    </div>
    <div class="row g-3 g-lg-4">
        <?php foreach ($categories as $index => $cat): ?>
            <div class="col-6 col-lg" data-reveal style="--delay: <?= $index * 0.08 ?>s">
                <a class="cat-tile text-decoration-none" href="<?= e(app_url('index.php?page=products&category=' . urlencode($cat['slug']))) ?>">
                    <div class="cat-tile-img" style="background-image:url('<?= e($cat['image']) ?>')"></div>
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
                        <button class="btn btn-success btn-sm mt-auto add-cart-btn" onclick="addToCart(<?= (int)$product['id'] ?>, 1)">
                            <span>Thêm vào giỏ</span>
                        </button>
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

<!-- ============ CTA ============ -->
<section class="container mt-5" data-reveal>
    <div class="cta-banner">
        <div>
            <h3 class="mb-1">Sẵn sàng thưởng thức?</h3>
            <p class="mb-0">Đặt ngay hôm nay — giảm 15% cho đơn hàng đầu tiên & freeship từ 300.000đ.</p>
        </div>
        <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-light btn-lg btn-glow">Đặt hàng ngay</a>
    </div>
</section>
