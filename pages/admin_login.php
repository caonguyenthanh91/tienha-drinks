<?php
declare(strict_types=1);

$redirect = (string)($_GET['redirect'] ?? ($_POST['redirect'] ?? 'admin_orders'));
$allowedRedirects = ['admin_dashboard', 'admin_orders', 'admin_products'];
if (!in_array($redirect, $allowedRedirects, true)) {
    $redirect = 'admin_dashboard';
}

$successMessage = '';
$errorMessage = '';

if (isset($_GET['logout']) && is_admin_authenticated()) {
    admin_logout();
    $successMessage = 'Đã đăng xuất khỏi khu vực quản trị.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (admin_login($username, $password)) {
        header('Location: ' . app_url('index.php?page=' . $redirect));
        exit;
    }

    $errorMessage = 'Sai user hoặc password, vui lòng thử lại.';
}

if (is_admin_authenticated()) {
    $adminUser = admin_session_user();
    ?>
    <div class="container" data-reveal>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="mb-3">Đăng nhập quản trị thành công</h3>
                        <p class="text-muted mb-3">
                            Xin chào <?= e((string)($adminUser['full_name'] ?? $adminUser['email'] ?? 'Admin')) ?>.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-success" href="<?= e(app_url('index.php?page=' . $redirect)) ?>">Vào trang quản trị</a>
                            <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin_login&logout=1')) ?>">Đăng xuất</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return;
}
?>

<div class="container" data-reveal>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="mb-2">Đăng nhập quản trị</h3>
                    <p class="text-muted mb-4">Chỉ tài khoản có quyền mới truy cập được trang Admin.</p>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?= e($successMessage) ?></div>
                    <?php endif; ?>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="alert alert-danger"><?= e($errorMessage) ?></div>
                    <?php endif; ?>

                    <form method="post" novalidate>
                        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                        <div class="mb-3">
                            <label for="adminUsername" class="form-label">User</label>
                            <input type="text" class="form-control" id="adminUsername" name="username" placeholder="admin@domain.com" required>
                        </div>
                        <div class="mb-4">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="password" placeholder="********" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
