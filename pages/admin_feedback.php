<?php
declare(strict_types=1);

$pdo = db();
$message = '';
$messageType = 'success';

// Xử lý trả lời feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_feedback') {
    $feedbackId = (int)($_POST['feedback_id'] ?? 0);
    $adminReply = trim((string)($_POST['admin_reply'] ?? ''));

    if ($feedbackId <= 0 || $adminReply === '') {
        $message = 'Dữ liệu không hợp lệ.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('UPDATE customer_feedback SET admin_reply = :reply, admin_reply_at = NOW(), status = "replied" WHERE id = :id');
        $stmt->execute([
            ':reply' => $adminReply,
            ':id' => $feedbackId,
        ]);
        $message = 'Đã gửi trả lời phản hồi.';
    }
}

// Lấy danh sách feedback
$statusFilter = trim((string)($_GET['status'] ?? ''));
$validStatuses = ['pending', 'replied'];
if ($statusFilter !== '' && !in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}

$where = '1=1';
$params = [];

if ($statusFilter !== '') {
    $where .= ' AND cf.status = :status';
    $params[':status'] = $statusFilter;
}

$stmt = $pdo->prepare('SELECT 
    cf.id, 
    cf.order_id, 
    o.order_code, 
    o.customer_name, 
    o.customer_phone,
    cf.rating, 
    cf.feedback_text, 
    cf.admin_reply, 
    cf.admin_reply_at,
    cf.status,
    cf.created_at
FROM customer_feedback cf
INNER JOIN orders o ON o.id = cf.order_id
WHERE ' . $where . '
ORDER BY cf.status ASC, cf.created_at DESC
LIMIT 100');
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

$pendingCount = $pdo->query('SELECT COUNT(*) FROM customer_feedback WHERE status = "pending"')->fetchColumn();
$repliedCount = $pdo->query('SELECT COUNT(*) FROM customer_feedback WHERE status = "replied"')->fetchColumn();
?>

<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <span class="section-eyebrow">Management</span>
            <h2 class="mb-0">Phản hồi chất lượng từ khách hàng</h2>
        </div>
        <a class="btn btn-outline-success" href="<?= e(app_url('index.php?page=admin_orders')) ?>">Quay lại đơn hàng</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= e($messageType) ?> alert-dismissible fade show" role="alert">
            <?= e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <span>Chờ trả lời</span>
                <strong><?= (int)$pendingCount ?></strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <span>Đã trả lời</span>
                <strong><?= (int)$repliedCount ?></strong>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="admin_feedback">
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ trả lời</option>
                        <option value="replied" <?= $statusFilter === 'replied' ? 'selected' : '' ?>>Đã trả lời</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-success" type="submit">Lọc</button>
                    <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin_feedback')) ?>">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!$feedbacks): ?>
                <p class="text-muted mb-0">Không có phản hồi phù hợp bộ lọc.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="border rounded-3 p-3 mb-3" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        <span class="badge text-bg-primary"><?= e($feedback['order_code']) ?></span>
                                    </h6>
                                    <small class="text-muted">
                                        <?= e($feedback['customer_name']) ?> | 
                                        <?= e($feedback['customer_phone']) ?> | 
                                        <?= e(date('d/m/Y H:i', strtotime((string)$feedback['created_at']))) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div style="font-size: 1.5rem; letter-spacing: 0.3rem;">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <span><?= ($i < (int)$feedback['rating']) ? '★' : '☆' ?></span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="badge <?= $feedback['status'] === 'pending' ? 'text-bg-warning' : 'text-bg-success' ?>">
                                        <?= $feedback['status'] === 'pending' ? 'Chờ trả lời' : 'Đã trả lời' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <p class="mb-0" style="white-space: pre-wrap;"><?= e($feedback['feedback_text']) ?></p>
                            </div>

                            <?php if ($feedback['admin_reply']): ?>
                                <div class="alert alert-light border border-success mb-3">
                                    <h6 class="text-success mb-2">💬 Trả lời từ cửa hàng</h6>
                                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($feedback['admin_reply']) ?></p>
                                    <small class="text-muted d-block mt-2">
                                        <?= e(date('d/m/Y H:i', strtotime((string)$feedback['admin_reply_at']))) ?>
                                    </small>
                                </div>
                            <?php elseif ($feedback['status'] === 'pending'): ?>
                                <form method="post" action="<?= e(app_url('index.php?page=admin_feedback')) ?>" class="mb-0">
                                    <input type="hidden" name="action" value="reply_feedback">
                                    <input type="hidden" name="feedback_id" value="<?= (int)$feedback['id'] ?>">
                                    <div class="input-group mb-2">
                                        <textarea class="form-control" name="admin_reply" rows="2" placeholder="Nhập trả lời..." required></textarea>
                                        <button class="btn btn-success" type="submit">Gửi trả lời</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.space-y-3 > * + * {
    margin-top: 1rem;
}
</style>
