<?php
declare(strict_types=1);

$posts = get_blog_posts(12);
?>
<div class="container">
    <div class="section-heading text-center mb-5" data-reveal>
        <span class="section-eyebrow">Kiến thức & mẹo vặt</span>
        <h2>Blog M&T Quán</h2>
        <p class="text-muted mb-0">Cập nhật bài viết về đồ uống lành mạnh, ưu đãi hấp dẫn và cách chọn nguyên liệu tốt cho sức khỏe.</p>
    </div>

    <?php if (!$posts): ?>
        <div class="alert alert-info text-center my-4">
            Chưa có bài viết nào. Hãy quay lại sau!
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $index => $post): ?>
                <div class="col-md-6 col-xl-4" data-reveal style="--delay: <?= ($index % 3) * 0.08 ?>s">
                    <article class="card border-0 h-100">
                        <div class="blog-thumb">
                            <img src="<?= e($post['thumbnail']) ?>" class="card-img-top" alt="<?= e($post['title']) ?>">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <span class="text-success fw-600 small"><?= e(date('d/m/Y', strtotime((string)$post['published_at']))) ?></span>
                            <h5 class="mt-1 mb-2"><?= e($post['title']) ?></h5>
                            <p class="small text-muted flex-grow-1"><?= e($post['excerpt']) ?></p>
                            <div class="d-flex justify-content-between align-items-center text-muted small mt-auto pt-2">
                                <span>✍️ <?= e($post['author_name']) ?></span>
                                <a href="<?= e(app_url('index.php?page=blog')) ?>" class="text-success fw-600">Đọc thêm →</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
