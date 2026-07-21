<?php
declare(strict_types=1);

$pdo = db();
$message = '';
$messageType = 'success';
$contactReplyColumnsAvailable = false;

$contactMessageColumns = $pdo->query('SHOW COLUMNS FROM contact_messages')->fetchAll();
$contactMessageColumnNames = array_map(static fn (array $column): string => (string)$column['Field'], $contactMessageColumns);
$contactReplyColumnsAvailable = in_array('admin_reply', $contactMessageColumnNames, true)
    && in_array('admin_reply_at', $contactMessageColumnNames, true);

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
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_contact_message') {
    $contactMessageId = (int)($_POST['contact_message_id'] ?? 0);
    $adminReply = trim((string)($_POST['admin_reply'] ?? ''));

    if (!$contactReplyColumnsAvailable) {
        $message = 'Cần chạy migration để trả lời góp ý từ trang liên hệ.';
        $messageType = 'warning';
    } elseif ($contactMessageId <= 0 || $adminReply === '') {
        $message = 'Dữ liệu không hợp lệ.';
        $messageType = 'danger';
    } else {
        $stmt = $pdo->prepare('UPDATE contact_messages SET admin_reply = :reply, admin_reply_at = NOW(), status = "done" WHERE id = :id');
        $stmt->execute([
            ':reply' => $adminReply,
            ':id' => $contactMessageId,
        ]);
        $message = 'Đã gửi trả lời góp ý từ trang liên hệ.';
    }
}

// Lấy danh sách feedback
$statusFilter = trim((string)($_GET['status'] ?? ''));
$validStatuses = ['pending', 'replied'];
if ($statusFilter !== '' && !in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}

$contactStatusFilter = trim((string)($_GET['contact_status'] ?? ''));
$validContactStatuses = ['new', 'processing', 'done'];
if ($contactStatusFilter !== '' && !in_array($contactStatusFilter, $validContactStatuses, true)) {
    $contactStatusFilter = '';
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

$contactWhere = '1=1';
$contactParams = [];

if ($contactStatusFilter !== '') {
    $contactWhere .= ' AND status = :status';
    $contactParams[':status'] = $contactStatusFilter;
}

$contactStmt = $pdo->prepare('SELECT
    id,
    full_name,
    phone,
    email,
    message,
    ' . ($contactReplyColumnsAvailable ? 'admin_reply' : 'NULL AS admin_reply') . ',
    ' . ($contactReplyColumnsAvailable ? 'admin_reply_at' : 'NULL AS admin_reply_at') . ',
    status,
    created_at
FROM contact_messages
WHERE ' . $contactWhere . '
ORDER BY FIELD(status, "new", "processing", "done"), created_at DESC
LIMIT 100');
$contactStmt->execute($contactParams);
$contactMessages = $contactStmt->fetchAll();

$pendingCount = $pdo->query('SELECT COUNT(*) FROM customer_feedback WHERE status = "pending"')->fetchColumn();
$repliedCount = $pdo->query('SELECT COUNT(*) FROM customer_feedback WHERE status = "replied"')->fetchColumn();
$contactNewCount = $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE status = "new"')->fetchColumn();
$contactProcessingCount = $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE status = "processing"')->fetchColumn();
$contactDoneCount = $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE status = "done"')->fetchColumn();
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
                <div class="col-md-3">
                    <label class="form-label">Góp ý liên hệ</label>
                    <select class="form-select" name="contact_status">
                        <option value="">Tất cả</option>
                        <option value="new" <?= $contactStatusFilter === 'new' ? 'selected' : '' ?>>Mới nhận</option>
                        <option value="processing" <?= $contactStatusFilter === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="done" <?= $contactStatusFilter === 'done' ? 'selected' : '' ?>>Đã trả lời</option>
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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-5 mb-4">
        <div>
            <span class="section-eyebrow">Contact</span>
            <h3 class="mb-0">Góp ý từ trang liên hệ</h3>
        </div>
    </div>

    <?php if (!$contactReplyColumnsAvailable): ?>
        <div class="alert alert-warning">
            Cơ sở dữ liệu hiện chưa có cột phản hồi cho bảng contact_messages. Phần danh sách vẫn xem được, nhưng cần chạy migration để admin trả lời trực tiếp tại đây.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <span>Mới nhận</span>
                <strong><?= (int)$contactNewCount ?></strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <span>Đang xử lý</span>
                <strong><?= (int)$contactProcessingCount ?></strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <span>Đã trả lời</span>
                <strong><?= (int)$contactDoneCount ?></strong>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!$contactMessages): ?>
                <p class="text-muted mb-0">Không có góp ý liên hệ phù hợp bộ lọc.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($contactMessages as $contactMessage): ?>
                        <?php
                            $contactStatus = (string)$contactMessage['status'];
                            $contactBadgeClass = 'text-bg-warning';
                            $contactStatusLabel = 'Mới nhận';

                            if ($contactStatus === 'processing') {
                                $contactBadgeClass = 'text-bg-info';
                                $contactStatusLabel = 'Đang xử lý';
                            } elseif ($contactStatus === 'done') {
                                $contactBadgeClass = 'text-bg-success';
                                $contactStatusLabel = 'Đã trả lời';
                            }
                        ?>
                        <div class="border rounded-3 p-3 mb-3" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?= e($contactMessage['full_name']) ?></h6>
                                    <small class="text-muted d-block"><?= e($contactMessage['phone']) ?></small>
                                    <?php if (!empty($contactMessage['email'])): ?>
                                        <small class="text-muted d-block"><?= e($contactMessage['email']) ?></small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block"><?= e(date('d/m/Y H:i', strtotime((string)$contactMessage['created_at']))) ?></small>
                                </div>
                                <span class="badge <?= $contactBadgeClass ?>"><?= e($contactStatusLabel) ?></span>
                            </div>

                            <div class="mb-3">
                                <p class="mb-0" style="white-space: pre-wrap;"><?= e($contactMessage['message']) ?></p>
                            </div>

                            <?php if (!empty($contactMessage['admin_reply'])): ?>
                                <div class="alert alert-light border border-success mb-3">
                                    <h6 class="text-success mb-2">Trả lời từ cửa hàng</h6>
                                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($contactMessage['admin_reply']) ?></p>
                                    <?php if (!empty($contactMessage['admin_reply_at'])): ?>
                                        <small class="text-muted d-block mt-2">
                                            <?= e(date('d/m/Y H:i', strtotime((string)$contactMessage['admin_reply_at']))) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (!$contactReplyColumnsAvailable): ?>
                                <div class="alert alert-secondary mb-0">
                                    Chưa thể trả lời trực tiếp vì database chưa chạy migration bổ sung cột admin_reply và admin_reply_at.
                                </div>
                            <?php else: ?>
                                <form method="post" action="<?= e(app_url('index.php?page=admin_feedback')) ?>" class="mb-0">
                                    <input type="hidden" name="action" value="reply_contact_message">
                                    <input type="hidden" name="contact_message_id" value="<?= (int)$contactMessage['id'] ?>">
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
