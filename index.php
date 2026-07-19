<?php
declare(strict_types=1);

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';

$allowedPages = [
	'home',
	'products',
	'blog',
	'about',
	'contact',
	'cart',
	'checkout',
	'account',
	'admin_login',
	'admin_dashboard',
	'admin_orders',
	'admin_products',
];

$page = $_GET['page'] ?? 'home';
if (!in_array($page, $allowedPages, true)) {
	$page = 'home';
}

$adminPages = ['admin_dashboard', 'admin_orders', 'admin_products'];
if (in_array($page, $adminPages, true) && !is_admin_authenticated()) {
	header('Location: ' . app_url('index.php?page=admin_login&redirect=' . urlencode($page)));
	exit;
}

$pageFile = __DIR__ . '/pages/' . $page . '.php';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/menu.php';

echo '<main class="main-content py-4">';
if (is_file($pageFile)) {
	require $pageFile;
} else {
	echo '<div class="container"><div class="alert alert-warning">Trang web đang được cập nhật.</div></div>';
}
echo '</main>';

require __DIR__ . '/includes/footer.php';

