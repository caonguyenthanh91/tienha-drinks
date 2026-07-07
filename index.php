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
	'admin',
	'admin_products',
];

$page = $_GET['page'] ?? 'home';
if (!in_array($page, $allowedPages, true)) {
	$page = 'home';
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

