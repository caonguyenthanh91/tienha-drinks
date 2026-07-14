<?php
declare(strict_types=1);

$page = current_page();
$menus = [
	'home' => 'Trang chủ',
	'products' => 'Sản phẩm',
	'blog' => 'Blog',
	'about' => 'Về chúng tôi',
	'contact' => 'Liên hệ',
	'cart' => 'Giỏ hàng',
	'account' => 'Tài khoản',
	'admin' => 'Quản trị',
	'admin_products' => 'QL sản phẩm',
];
?>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
	<div class="container">
		<a class="navbar-brand brand-logo" href="<?= e(app_url('index.php?page=home')) ?>">
			<span class="brand-dot"></span>
			M&T Quán
		</a>
		
		<div class="collapse navbar-collapse" id="mainNav">
			<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
				<?php foreach ($menus as $key => $label): ?>
					<?php if ($key !== 'cart'): ?>
						<li class="nav-item">
							<a class="nav-link <?= $page === $key ? 'active' : '' ?>" href="<?= e(app_url('index.php?page=' . $key)) ?>"><?= e($label) ?></a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<a class="btn btn-success cart-pill" href="<?= e(app_url('index.php?page=cart')) ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 0.5rem;">
				<circle cx="9" cy="21" r="1"></circle>
				<circle cx="20" cy="21" r="1"></circle>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
			</svg>
			<span class="badge bg-light text-success" id="cartCountBadge"><?= cart_count() ?></span>
		</a>
		
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	</div>
</nav>

