<?php
declare(strict_types=1);

$tiers = get_customer_tiers();
$phoneInput = trim((string)($_GET['phone'] ?? ''));
$normalizedPhone = preg_replace('/\D+/', '', $phoneInput) ?? '';
$searched = array_key_exists('phone', $_GET);
$errorMessage = '';
$customer = null;
$orders = [];
$fallbackProfile = null;

if ($searched) {
    if ($normalizedPhone === '') {
        $errorMessage = 'Vui lòng nhập số điện thoại hợp lệ (chỉ gồm chữ số).';
    } else {
        $customer = get_customer_by_phone($normalizedPhone);

        if ($customer) {
            $stmtOrders = db()->prepare('SELECT id, order_code, status, final_total, created_at, customer_name, customer_email, shipping_address, note FROM orders WHERE customer_id = :customer_id OR customer_phone = :phone ORDER BY id DESC LIMIT 10');
            $stmtOrders->execute([
                ':customer_id' => (int)$customer['id'],
                ':phone' => $normalizedPhone,
            ]);
        } else {
            $stmtOrders = db()->prepare('SELECT id, order_code, status, final_total, created_at, customer_name, customer_email, shipping_address, note FROM orders WHERE customer_phone = :phone ORDER BY id DESC LIMIT 10');
            $stmtOrders->execute([':phone' => $normalizedPhone]);
        }

        $orders = $stmtOrders->fetchAll();

        if (!$customer && $orders) {
            $latestOrder = $orders[0];
            $fallbackProfile = [
                'full_name' => (string)($latestOrder['customer_name'] ?? ''),
                'email' => (string)($latestOrder['customer_email'] ?? ''),
                'phone' => $normalizedPhone,
                'default_address' => (string)($latestOrder['shipping_address'] ?? ''),
                'tier_name' => 'Khách mới',
                'points' => 0,
            ];
        }

        if (!$customer && !$orders) {
            $errorMessage = 'Không tìm thấy đơn hàng với số điện thoại này.';
        }
    }
}

$currentOrder = null;
foreach ($orders as $order) {
    if (!in_array((string)$order['status'], ['completed', 'cancelled'], true)) {
        $currentOrder = $order;
        break;
    }
}

if ($currentOrder === null && $orders !== []) {
    $currentOrder = $orders[0];
}
?>
<div class="container" data-reveal>
    <div class="mb-4">
        <span class="section-eyebrow">Khu vực cá nhân</span>
        <h2>Tra cứu tài khoản theo số điện thoại</h2>
        <p class="text-muted mb-0">Nhập đúng số điện thoại đã đặt hàng</p>
    </div>

    <div class="card border-0 mb-4" data-reveal>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end" id="accountLookupForm" novalidate>
                <input type="hidden" name="page" value="account">
                <div class="col-md-8">
                    <label for="lookupPhone" class="form-label fw-600">Số điện thoại</label>
                    <input
                        type="text"
                        class="form-control"
                        id="lookupPhone"
                        name="phone"
                        value="<?= e($phoneInput) ?>"
                        placeholder="Ví dụ: 0912345678"
                        inputmode="numeric"
                        autocomplete="tel"
                        required
                    >
                    <!-- <small class="text-muted">Chỉ giữ lại chữ số khi tìm kiếm.</small> -->
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-success">Tra cứu</button>
                </div>
            </form>
            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-warning mt-3 mb-0"><?= e($errorMessage) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$searched): ?>
        <div class="alert alert-info">Vui lòng nhập số điện thoại để xem thông tin tài khoản, đơn hàng và chương trình thành viên.</div>
    <?php elseif ($customer || $orders): ?>

    <?php $profile = $customer ?: $fallbackProfile; ?>

    <div class="row g-4">
        <!-- Thông tin tài khoản -->
        <div class="col-lg-4" data-reveal>
            <div class="card border-0 h-100">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin khách hàng</h5>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Họ tên</small>
                        <p class="fw-600 mb-0"><?= e((string)($profile['full_name'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Email</small>
                        <p class="mb-0"><?= e((string)($profile['email'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Số điện thoại</small>
                        <p class="mb-0"><?= e((string)($profile['phone'] ?? $normalizedPhone)) ?></p>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Địa chỉ mặc định</small>
                        <p class="mb-0"><?= e((string)($profile['default_address'] ?? 'Chưa cập nhật')) ?></p>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Hạng hiện tại</small>
                        <p class="mb-0">
                            <span class="badge text-bg-success fw-bold"><?= e((string)($profile['tier_name'] ?? 'Khách mới')) ?></span>
                            <span class="text-success fw-600">● <?= (int)($profile['points'] ?? 0) ?> điểm</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lịch sử đơn hàng -->
        <div class="col-lg-8" data-reveal style="--delay: 0.1s">
            <div class="card border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Trạng thái đơn hàng</h5>
                    <?php if ($currentOrder): ?>
                        <div class="alert alert-light border mb-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Đơn hàng gần nhất</small>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none order-detail-trigger" data-order-id="<?= (int)$currentOrder['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal" style="font-size: inherit;">
                                        <strong><?= e($currentOrder['order_code']) ?></strong>
                                    </button>
                                </div>
                                <span class="badge <?= match($currentOrder['status']) {
                                    'pending' => 'text-bg-warning',
                                    'confirmed' => 'text-bg-info',
                                    'shipping' => 'text-bg-primary',
                                    'completed' => 'text-bg-success',
                                    'cancelled' => 'text-bg-danger',
                                    default => 'text-bg-secondary'
                                } ?>">
                                    <?= match($currentOrder['status']) {
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'shipping' => 'Đang giao',
                                        'completed' => 'Đã giao',
                                        'cancelled' => 'Đã hủy',
                                        default => e((string)$currentOrder['status'])
                                    } ?>
                                </span>
                            </div>
                            <?php if (trim((string)($currentOrder['note'] ?? '')) !== ''): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Phản hồi từ cửa hàng</small>
                                    <small class="d-block" style="white-space: pre-line;"><?= e((string)$currentOrder['note']) ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($orders): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th>Ngày tạo</th>
                                    <th>Phản hồi cửa hàng</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none order-detail-trigger" data-order-id="<?= (int)$order['id'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailModal">
                                                <strong><?= e($order['order_code']) ?></strong>
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge <?= match($order['status']) {
                                                'pending' => 'text-bg-warning',
                                                'confirmed' => 'text-bg-info',
                                                'shipping' => 'text-bg-primary',
                                                'completed' => 'text-bg-success',
                                                'cancelled' => 'text-bg-danger',
                                                default => 'text-bg-secondary'
                                            } ?>">
                                                <?= match($order['status']) {
                                                    'pending' => 'Chờ xác nhận',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'shipping' => 'Đang giao',
                                                    'completed' => 'Đã giao',
                                                    'cancelled' => 'Đã hủy',
                                                    default => e($order['status'])
                                                } ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success"><?= e(format_currency((float)$order['final_total'])) ?></td>
                                        <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                                        <td>
                                            <?php if (trim((string)($order['note'] ?? '')) !== ''): ?>
                                                <small class="d-block" style="white-space: pre-line;"><?= e((string)$order['note']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Chưa có phản hồi</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($order['status'] === 'completed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary feedback-trigger" data-order-id="<?= (int)$order['id'] ?>" data-order-code="<?= e($order['order_code']) ?>" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                                                    ⭐⭐⭐⭐⭐<br>Đánh giá
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Khách hàng chưa có đơn hàng nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Các hạng thành viên -->
            <div class="card border-0">
                <div class="card-body">
                    <h5 class="mb-3">Chương trình thành viên</h5>
                    <div class="row g-3">
                        <?php foreach ($tiers as $index => $tier): ?>
                            <div class="col-md-4" data-reveal style="--delay: <?= 0.15 + $index * 0.08 ?>s">
                                <div class="tier-card h-100">
                                    <span class="tier-badge">Hạng <?= chr(65 + $index) ?></span>
                                    <h6 class="mt-2"><?= e($tier['name']) ?></h6>
                                    <p class="text-success fw-600 small">Từ <?= e(format_currency((float)$tier['min_spending'])) ?></p>
                                    <p class="small text-muted"><?= e($tier['benefits']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal phản hồi chất lượng -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Phản hồi chất lượng</h5>
                    <small class="text-muted d-block">Mã đơn: <span id="feedbackOrderCode"></span></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="feedbackLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>

                <!-- Form gửi feedback mới -->
                <form id="feedbackForm" style="display: none;">
                    <input type="hidden" id="feedbackOrderId" name="order_id" value="">
                    <input type="hidden" id="feedbackCustomerId" name="customer_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Đánh giá chất lượng</label>
                        <div class="rating-stars" id="ratingStars" style="font-size: 2rem; letter-spacing: 0.5rem; cursor: pointer;">
                            <span class="star" data-rating="1">☆</span>
                            <span class="star" data-rating="2">☆</span>
                            <span class="star" data-rating="3">☆</span>
                            <span class="star" data-rating="4">☆</span>
                            <span class="star" data-rating="5">☆</span>
                        </div>
                        <input type="hidden" id="feedbackRating" name="rating" value="0">
                        <small class="text-muted d-block mt-2">
                            <span id="ratingText">Vui lòng chọn đánh giá</span>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="feedbackText" class="form-label">Ý kiến của bạn</label>
                        <textarea class="form-control" id="feedbackText" name="feedback_text" rows="4" placeholder="Hãy chia sẻ trải nghiệm của bạn..." required></textarea>
                    </div>

                    <div id="feedbackMessage" class="alert" style="display: none;"></div>
                </form>

                <!-- Hiển thị feedback đã gửi + trả lời -->
                <div id="feedbackDisplay" style="display: none;">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Đánh giá của bạn</h6>
                        <div style="font-size: 1.8rem; letter-spacing: 0.3rem; margin-bottom: 1rem;">
                            <span id="displayRating"></span>
                        </div>
                        <p id="displayFeedbackText" style="white-space: pre-wrap;" class="mb-0"></p>
                    </div>

                    <div id="replySection" style="display: none;">
                        <div class="alert alert-light border border-success">
                            <h6 class="text-success mb-2">💬 Trả lời từ cửa hàng</h6>
                            <p id="displayReply" style="white-space: pre-wrap;" class="mb-0"></p>
                            <small class="text-muted d-block mt-2">
                                Ngày: <span id="displayReplyDate"></span>
                            </small>
                        </div>
                    </div>

                    <div id="pendingMessage" class="alert alert-info" style="display: none;">
                        Phản hồi đã được gửi. Cửa hàng sẽ trả lời trong thời gian sớm nhất.
                    </div>
                </div>

                <!-- Error state -->
                <div id="feedbackError" class="alert alert-danger" style="display: none;">
                    Không thể tải thông tin. Vui lòng thử lại.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="submitFeedbackBtn" style="display: none;">Gửi phản hồi</button>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
                <div id="orderDetailContent" style="display: none;">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Mã đơn</h6>
                        <p class="mb-0 fw-bold" id="orderCode"></p>
                    </div>
                    <h6 class="text-muted mb-3">Danh sách sản phẩm</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên hàng</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsTable">
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-light border">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Tổng cộng:</span>
                            <span class="fw-bold text-success fs-5" id="orderTotal"></span>
                        </div>
                    </div>
                </div>
                <div id="orderDetailError" class="alert alert-danger" style="display: none;">
                    Không thể tải thông tin đơn hàng. Vui lòng thử lại.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('accountLookupForm');
    const input = document.getElementById('lookupPhone');
    if (form && input) {
        const normalize = () => {
            input.value = input.value.replace(/\D+/g, '');
        };
        input.addEventListener('input', normalize);
        form.addEventListener('submit', normalize);
    }

    // Xử lý modal chi tiết đơn hàng
    const orderDetailModal = document.getElementById('orderDetailModal');
    if (orderDetailModal) {
        orderDetailModal.addEventListener('show.bs.modal', async (e) => {
            const trigger = e.relatedTarget;
            const orderId = trigger?.dataset?.orderId;

            if (!orderId) return;

            const loadingEl = document.getElementById('orderDetailLoading');
            const contentEl = document.getElementById('orderDetailContent');
            const errorEl = document.getElementById('orderDetailError');

            loadingEl.style.display = 'block';
            contentEl.style.display = 'none';
            errorEl.style.display = 'none';

            try {
                const response = await fetch(`<?= app_url('includes/api_order_details.php') ?>?order_id=${orderId}`);
                if (!response.ok) throw new Error('Network error');
                
                const data = await response.json();
                if (!data.success) throw new Error('Invalid data');

                // Điền dữ liệu
                document.getElementById('orderCode').textContent = data.order_code;
                document.getElementById('orderTotal').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.final_total);

                const itemsTable = document.getElementById('orderItemsTable');
                itemsTable.innerHTML = '';
                
                data.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.unit_price)}</td>
                        <td class="text-end fw-semibold">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.line_total)}</td>
                    `;
                    itemsTable.appendChild(row);
                });

                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
            } catch (err) {
                console.error('Error loading order details:', err);
                loadingEl.style.display = 'none';
                errorEl.style.display = 'block';
            }
        });
    }

    // Xử lý modal phản hồi chất lượng
    const feedbackModal = document.getElementById('feedbackModal');
    const ratingStars = document.querySelectorAll('.rating-stars .star');
    const feedbackRatingInput = document.getElementById('feedbackRating');
    const ratingText = document.getElementById('ratingText');
    let currentRating = 0;

    ratingStars.forEach(star => {
        star.addEventListener('click', () => {
            currentRating = parseInt(star.dataset.rating);
            feedbackRatingInput.value = currentRating;
            ratingStars.forEach((s, index) => {
                s.textContent = (index + 1) <= currentRating ? '★' : '☆';
            });
            ratingText.textContent = currentRating + ' sao';
        });
        star.addEventListener('mouseenter', () => {
            const rating = parseInt(star.dataset.rating);
            ratingStars.forEach((s, index) => {
                s.textContent = (index + 1) <= rating ? '★' : '☆';
            });
        });
    });

    document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
        ratingStars.forEach((s, index) => {
            s.textContent = (index + 1) <= currentRating ? '★' : '☆';
        });
    });

    if (feedbackModal) {
        feedbackModal.addEventListener('show.bs.modal', async (e) => {
            const trigger = e.relatedTarget;
            const orderId = trigger?.dataset?.orderId;
            const orderCode = trigger?.dataset?.orderCode;
            
            document.getElementById('feedbackOrderId').value = orderId || '';
            document.getElementById('feedbackOrderCode').textContent = orderCode || '';
            document.getElementById('feedbackCustomerId').value = '<?= $customer ? (int)$customer['id'] : 0 ?>';
            
            // Hiển thị loading
            document.getElementById('feedbackLoading').style.display = 'block';
            document.getElementById('feedbackForm').style.display = 'none';
            document.getElementById('feedbackDisplay').style.display = 'none';
            document.getElementById('feedbackError').style.display = 'none';
            document.getElementById('submitFeedbackBtn').style.display = 'none';

            try {
                const response = await fetch(`<?= app_url('includes/api_customer_feedback.php') ?>?action=get_feedback&order_id=${orderId}`);
                if (!response.ok) throw new Error('Network error');
                
                const data = await response.json();
                if (!data.success) throw new Error('Invalid data');

                document.getElementById('feedbackLoading').style.display = 'none';

                if (data.feedback) {
                    // Hiển thị feedback đã gửi
                    const fb = data.feedback;
                    let ratingStars = '';
                    for (let i = 0; i < 5; i++) {
                        ratingStars += (i < fb.rating) ? '★' : '☆';
                    }
                    document.getElementById('displayRating').textContent = ratingStars;
                    document.getElementById('displayFeedbackText').textContent = fb.feedback_text;
                    
                    if (fb.admin_reply) {
                        document.getElementById('displayReply').textContent = fb.admin_reply;
                        document.getElementById('displayReplyDate').textContent = new Date(fb.admin_reply_at).toLocaleString('vi-VN');
                        document.getElementById('replySection').style.display = 'block';
                        document.getElementById('pendingMessage').style.display = 'none';
                    } else {
                        document.getElementById('replySection').style.display = 'none';
                        document.getElementById('pendingMessage').style.display = 'block';
                    }
                    
                    document.getElementById('feedbackDisplay').style.display = 'block';
                } else {
                    // Hiển thị form gửi feedback
                    document.getElementById('feedbackForm').reset();
                    currentRating = 0;
                    feedbackRatingInput.value = 0;
                    ratingStars.forEach(s => s.textContent = '☆');
                    ratingText.textContent = 'Vui lòng chọn đánh giá';
                    document.getElementById('feedbackMessage').style.display = 'none';
                    
                    document.getElementById('feedbackForm').style.display = 'block';
                    document.getElementById('submitFeedbackBtn').style.display = 'block';
                }
            } catch (err) {
                console.error('Error loading feedback:', err);
                document.getElementById('feedbackLoading').style.display = 'none';
                document.getElementById('feedbackError').style.display = 'block';
            }
        });
    }

    document.getElementById('submitFeedbackBtn').addEventListener('click', async () => {
        const orderId = document.getElementById('feedbackOrderId').value;
        const customerId = document.getElementById('feedbackCustomerId').value;
        const rating = document.getElementById('feedbackRating').value;
        const feedbackText = document.getElementById('feedbackText').value.trim();

        if (!orderId || rating === '0' || feedbackText === '') {
            const msgEl = document.getElementById('feedbackMessage');
            msgEl.className = 'alert alert-warning';
            msgEl.textContent = 'Vui lòng điền đầy đủ thông tin';
            msgEl.style.display = 'block';
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'submit_feedback');
            formData.append('order_id', orderId);
            formData.append('customer_id', customerId);
            formData.append('rating', rating);
            formData.append('feedback_text', feedbackText);

            const response = await fetch('<?= app_url('includes/api_customer_feedback.php') ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const msgEl = document.getElementById('feedbackMessage');
                msgEl.className = 'alert alert-success';
                msgEl.textContent = 'Cảm ơn bạn! Phản hồi đã được gửi.';
                msgEl.style.display = 'block';
                
                setTimeout(() => {
                    bootstrap.Modal.getInstance(feedbackModal).hide();
                }, 1500);
            } else {
                throw new Error(data.error || 'Error');
            }
        } catch (err) {
            console.error('Error submitting feedback:', err);
            const msgEl = document.getElementById('feedbackMessage');
            msgEl.className = 'alert alert-danger';
            msgEl.textContent = 'Lỗi khi gửi phản hồi. Vui lòng thử lại.';
            msgEl.style.display = 'block';
        }
    });
});
</script>

