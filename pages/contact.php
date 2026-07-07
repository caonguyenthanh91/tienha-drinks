<?php
declare(strict_types=1);

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name !== '' && $phone !== '' && $message !== '') {
        $stmt = db()->prepare('INSERT INTO contact_messages (full_name, phone, email, message, created_at) VALUES (:full_name, :phone, :email, :message, NOW())');
        $stmt->execute([
            ':full_name' => $name,
            ':phone' => $phone,
            ':email' => $email !== '' ? $email : null,
            ':message' => $message,
        ]);
        $sent = true;
    }
}
?>
<div class="container">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h4>Thông tin liên hệ</h4>
                    <p class="mb-2">Địa chỉ: Khu 15, Bình An, Đồng Nai</p>
                    <p class="mb-2">Hotline: 03 6816 6816</p>
                    <p class="mb-2">Email: hello@tienhadrinks.vn</p>
                    <p class="mb-0">Giờ mở cửa: 07:00 - 22:00</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4>Gửi yêu cầu</h4>
                    <?php if ($sent): ?>
                        <div class="alert alert-success">Cảm ơn bạn, chúng tôi sẽ phản hồi sớm nhất.</div>
                    <?php endif; ?>
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nội dung</label>
                            <textarea name="message" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-12 d-grid d-md-flex justify-content-md-end">
                            <button class="btn btn-success">Gửi thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
