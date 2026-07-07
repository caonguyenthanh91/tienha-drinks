<?php
declare(strict_types=1);

$posts = get_blog_posts(12);
?>
<div class="container">
    <div class="section-heading mb-4">
        <h2>Blog va meo vat</h2>
        <p class="text-muted mb-0">Cap nhat bai viet ve do uong, uu dai va cach chon nguyen lieu tot cho suc khoe.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-6 col-xl-4">
                <article class="card border-0 shadow-sm h-100">
                    <img src="<?= e($post['thumbnail']) ?>" class="card-img-top" alt="<?= e($post['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5><?= e($post['title']) ?></h5>
                        <p class="small text-muted"><?= e($post['excerpt']) ?></p>
                        <div class="mt-auto d-flex justify-content-between text-muted small">
                            <span><?= e($post['author_name']) ?></span>
                            <span><?= e(date('d/m/Y', strtotime((string)$post['published_at']))) ?></span>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
