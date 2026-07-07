<?php
declare(strict_types=1);

$categories = get_categories();
?>
<aside class="sidebar card border-0 shadow-sm">
	<div class="card-body">
		<h5 class="sidebar-title">Danh muc</h5>
		<ul class="list-unstyled mb-4">
			<?php foreach ($categories as $category): ?>
				<li class="mb-2">
					<a href="<?= e(app_url('index.php?page=products&category=' . urlencode($category['slug']))) ?>" class="sidebar-link"><?= e($category['name']) ?></a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="support-box">
			<strong>Hotline dat nhanh</strong>
			<div class="fs-5 fw-bold text-success">0908 903 164</div>
			<small class="text-muted">Mo cua 07:00 - 22:00 moi ngay</small>
		</div>
	</div>
</aside>

