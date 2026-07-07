<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = [];
}

if (!isset($_SESSION['user'])) {
	$_SESSION['user'] = null;
}

