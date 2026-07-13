<?php
declare(strict_types=1);

$tiers = get_customer_tiers();
$stats = [
    ['icon' => '☕', 'label' => 'Ly thức uống bán', 'value' => '50.000+'],
    ['icon' => '😊', 'label' => 'Khách hàng hài lòng', 'value' => '8.000+'],
    ['icon' => '🚚', 'label' => 'Đơn giao thành công', 'value' => '12.000+'],
    ['icon' => '⭐', 'label' => 'Đánh giá trung bình', 'value' => '4.8/5'],
];
?>

<!-- About Hero -->
<section class="about-hero mb-5" data-reveal>
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="section-eyebrow">Câu chuyện M&T</span>
                <h2 class="mb-3">Bắt đầu từ đam mê, tăng trưởng từ tín yêu</h2>
                <p class="text-muted mb-3">
                    M&T Quán bắt đầu từ một xe nước nhỏ tại trung tâm thành phố, với mong muốn đem đồ uống tươi và
                    thói quen sống lành mạnh đến mọi người. Chúng tôi tin rằng mỗi ly nước là một cơ hội để kết nối
                    và trao năng lượng tích cực.
                </p>
                <p class="text-muted">
                    Hôm nay, với hơn 3 năm kinh nghiệm, chúng tôi tự hào phục vụ hàng nghìn khách hàng mỗi ngày,
                    luôn giữ tiêu chí: <strong>Nguyên liệu tươi, pha chế tỉnh tế, dịch vụ tận tâm.</strong>
                </p>
            </div>
            <div class="col-lg-6">
                <div class="about-highlights">
                    <div class="about-box">
                        <span class="about-icon">🎯</span>
                        <h5>Sứ mệnh</h5>
                        <p>Khoảnh khắc đáng nhớ — mỗi ly nước là một lần kết nối và năng lượng tích cực cho khách hàng.</p>
                    </div>
                    <div class="about-box">
                        <span class="about-icon">🚀</span>
                        <h5>Tầm nhìn</h5>
                        <p>Trở thành thương hiệu juice & smoothie được yêu thích nhất tại các thành phố lớn Việt Nam.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="values-section my-5" data-reveal>
    <div class="container">
        <h3 class="text-center mb-4">Giá trị cốt lõi</h3>
        <div class="row g-4">
            <div class="col-md-3 col-6" data-reveal style="--delay: 0s">
                <div class="value-card">
                    <div class="value-icon">🌿</div>
                    <h6>Tươi & Thiên nhiên</h6>
                    <p class="small text-muted">Nguyên liệu hữu cơ, theo mùa, minh bạch nguồn gốc.</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-reveal style="--delay: 0.08s">
                <div class="value-card">
                    <div class="value-icon">❤️</div>
                    <h6>Tận tâm & Chất lượng</h6>
                    <p class="small text-muted">Mỗi ly nước đều được pha chế với tình yêu và kỹ năng.</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-reveal style="--delay: 0.16s">
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h6>Cộng đồng & Nông dân</h6>
                    <p class="small text-muted">Hỗ trợ nông sản Việt, phát triển bền vững.</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-reveal style="--delay: 0.24s">
                <div class="value-card">
                    <div class="value-icon">⚡</div>
                    <h6>Nhanh & Tiện lợi</h6>
                    <p class="small text-muted">Giao hàng 30 phút, order online dễ dàng, lúc nào cũng sẵn sàng.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-section my-5" data-reveal>
    <div class="container">
        <div class="row g-3">
            <?php foreach ($stats as $index => $stat): ?>
                <div class="col-6 col-md-3" data-reveal style="--delay: <?= $index * 0.08 ?>s">
                    <div class="metric-card h-100 text-center">
                        <div class="stat-icon"><?= $stat['icon'] ?></div>
                        <strong><?= e($stat['value']) ?></strong>
                        <span><?= e($stat['label']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Customer Tiers -->
<section class="tiers-section my-5" data-reveal>
    <div class="container">
        <h3 class="text-center mb-4">Chương trình Thành viên</h3>
        <div class="row g-3">
            <?php foreach ($tiers as $index => $tier): ?>
                <div class="col-md-4" data-reveal style="--delay: <?= $index * 0.1 ?>s">
                    <div class="tier-card h-100">
                        <span class="tier-badge">Cấp <?= chr(65 + $index) ?></span>
                        <h5 class="mt-2"><?= e($tier['name']) ?></h5>
                        <p class="text-success fw-600">Từ <?= e(format_currency((float)$tier['min_spending'])) ?></p>
                        <p class="small text-muted"><?= e($tier['benefits']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="mt-5" data-reveal>
    <div class="cta-banner">
        <div>
            <h3 class="mb-1">Sẵn sàng trở thành thành viên M&T?</h3>
            <p class="mb-0">Tích điểm sau mỗi đơn hàng, đổi quà, nhận ưu đãi độc quyền.</p>
        </div>
        <a href="<?= e(app_url('index.php?page=products')) ?>" class="btn btn-light btn-lg btn-glow">Mua hàng ngay</a>
    </div>
</section>
