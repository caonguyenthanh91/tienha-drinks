<?php
declare(strict_types=1);

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name !== '' && $phone !== '' && $message !== '') {
        $stmt = db()->prepare('INSERT INTO contact_messages (full_name, phone, email, message, status, created_at) VALUES (:full_name, :phone, :email, :message, :status, NOW())');
        $stmt->execute([
            ':full_name' => $name,
            ':phone' => $phone,
            ':email' => $email !== '' ? $email : null,
            ':message' => $message,
            ':status' => 'new',
        ]);
        $sent = true;
    }
}
?>
<div class="container">
    <div class="text-center mb-5" data-reveal>
        <span class="section-eyebrow">Kết nối với chúng tôi</span>
        <h2 class="mb-2">Liên hệ M&T Quán</h2>
        <p class="text-muted">Có câu hỏi hoặc muốn góp ý? Hãy liên hệ với chúng tôi ngay.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-5" data-reveal>
            <div class="card border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">📍 Thông tin liên hệ</h5>
                    <div class="mb-3">
                        <strong>Địa chỉ</strong>
                        <p class="text-muted mb-0">Khu 15, Bình An, Đồng Nai</p>
                    </div>
                    <div class="mb-3">
                        <strong>Hotline</strong>
                        <p class="text-success fw-bold mb-0">03.6816.6816</p>
                    </div>
                    <div class="mb-3">
                        <strong>Email</strong>
                        <p class="text-muted mb-0">hatien2403@gmail.com</p>
                    </div>
                    <div>
                        <strong>Giờ mở cửa</strong>
                        <p class="text-muted mb-0">08:00 - 16:00 (Thứ 2 - 6)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7" data-reveal style="--delay: 0.1s">
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="mb-4">✉️ Gửi yêu cầu/ Góp ý</h5>
                    <?php if ($sent): ?>
                        <div class="alert alert-success mb-3">✓ Cảm ơn bạn! Chúng tôi sẽ phản hồi sớm nhất.</div>
                    <?php endif; ?>
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Họ tên</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Nội dung yêu cầu/ Góp ý</label>
                            <textarea name="message" rows="4" class="form-control" required placeholder="Ví dụ: Đặt nước cho sự kiện..."></textarea>
                        </div>
                        <div class="col-12 d-grid d-md-flex justify-content-md-end gap-2">
                            <button class="btn btn-success btn-lg" type="submit">Gửi yêu cầu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
