<?php
declare(strict_types=1);

$categories = get_categories();
?>
<aside class="sidebar card border-0" data-reveal>
	<div class="card-body">
		<h5 class="sidebar-title mb-3">📁 Danh mục</h5>
		<ul class="list-unstyled mb-4">
			<li class="mb-2">
				<a href="<?= e(app_url('index.php?page=products')) ?>" class="sidebar-link fw-500">
					🔥 Tất cả sản phẩm
				</a>
			</li>
			<?php foreach ($categories as $cat): ?>
				<li class="mb-2">
					<a href="<?= e(app_url('index.php?page=products&category=' . urlencode($cat['slug']))) ?>" class="sidebar-link">
						<?= e($cat['name']) ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="support-box">
			<strong class="d-block mb-2">☎️ Hotline đặt nhanh</strong>
			<div class="fs-5 fw-bold text-success">03 6816 6816</div>
			<small class="text-muted d-block mt-1">Mở cửa 07:00 - 22:00 mỗi ngày</small>
		</div>
	</div>
</aside>
