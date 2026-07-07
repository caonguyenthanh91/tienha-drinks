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
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="mainNav">
			<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
				<?php foreach ($menus as $key => $label): ?>
					<li class="nav-item">
						<a class="nav-link <?= $page === $key ? 'active' : '' ?>" href="<?= e(app_url('index.php?page=' . $key)) ?>"><?= e($label) ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn-success ms-lg-3 cart-pill" href="<?= e(app_url('index.php?page=cart')) ?>">
				Giỏ hàng <span class="badge bg-light text-success ms-1" id="cartCountBadge"><?= cart_count() ?></span>
			</a>
		</div>
	</div>
</nav>

