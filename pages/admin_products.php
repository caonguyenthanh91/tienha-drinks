<?php
declare(strict_types=1);

$pdo = db();
$errors = [];
$success = '';

$categories = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editingProduct = null;
if ($editId > 0) {
    $stmtEdit = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmtEdit->execute([':id' => $editId]);
    $editingProduct = $stmtEdit->fetch() ?: null;
}

$form = [
    'action' => $editingProduct ? 'update' : 'create',
    'product_id' => $editingProduct ? (int)$editingProduct['id'] : 0,
    'category_id' => $editingProduct ? (int)$editingProduct['category_id'] : (int)($categories[0]['id'] ?? 0),
    'name' => $editingProduct['name'] ?? '',
    'price' => $editingProduct ? (string)$editingProduct['price'] : '',
    'sale_price' => $editingProduct && $editingProduct['sale_price'] !== null ? (string)$editingProduct['sale_price'] : '',
    'description' => $editingProduct['description'] ?? '',
    'current_thumbnail' => $editingProduct['thumbnail'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['action'] = $_POST['action'] ?? 'create';
    $form['product_id'] = (int)($_POST['product_id'] ?? 0);
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['name'] = trim((string)($_POST['name'] ?? ''));
    $form['price'] = trim((string)($_POST['price'] ?? ''));
    $form['sale_price'] = trim((string)($_POST['sale_price'] ?? ''));
    $form['description'] = trim((string)($_POST['description'] ?? ''));
    $form['current_thumbnail'] = trim((string)($_POST['current_thumbnail'] ?? ''));

    if ($form['name'] === '') {
        $errors[] = 'Vui lòng nhập tên sản phẩm.';
    }

    $categoryIds = array_map(static fn(array $c): int => (int)$c['id'], $categories);
    if (!in_array($form['category_id'], $categoryIds, true)) {
        $errors[] = 'Danh mục không hợp lệ.';
    }

    if ($form['price'] === '' || !is_numeric($form['price']) || (float)$form['price'] <= 0) {
        $errors[] = 'Giá bán cũ phải là số lớn hơn 0.';
    }

    if ($form['sale_price'] !== '' && (!is_numeric($form['sale_price']) || (float)$form['sale_price'] < 0)) {
        $errors[] = 'Giá bán mới phải là số hợp lệ hoặc để trống.';
    }

    if ($form['sale_price'] !== '' && is_numeric($form['price']) && (float)$form['sale_price'] > (float)$form['price']) {
        $errors[] = 'Giá bán mới nên nhỏ hơn hoặc bằng giá bán cũ.';
    }

    $thumbnailPath = $form['current_thumbnail'] !== '' ? $form['current_thumbnail'] : null;

    if (isset($_FILES['thumbnail']) && (int)$_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ((int)$_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload hình ảnh thất bại. Vui lòng thử lại.';
        } else {
            $tmpName = (string)$_FILES['thumbnail']['tmp_name'];
            $originalName = (string)$_FILES['thumbnail']['name'];
            $extension = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Định dạng ảnh không được hỗ trợ. Chỉ chấp nhận JPG, PNG, WEBP, GIF.';
            } else {
                $uploadDir = __DIR__ . '/../assets/img/products';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    $errors[] = 'Không thể tạo thư mục lưu ảnh.';
                } else {
                    $filename = 'product-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $destination = $uploadDir . '/' . $filename;

                    if (!move_uploaded_file($tmpName, $destination)) {
                        $errors[] = 'Không thể lưu ảnh vào máy chủ.';
                    } else {
                        $thumbnailPath = 'assets/img/products/' . $filename;
                    }
                }
            }
        }
    }

    if ($errors === []) {
        $price = (float)$form['price'];
        $salePrice = $form['sale_price'] !== '' ? (float)$form['sale_price'] : null;
        $description = $form['description'] !== '' ? $form['description'] : null;
        if ($description !== null) {
            $shortDescription = function_exists('mb_substr')
                ? mb_substr($description, 0, 255)
                : substr($description, 0, 255);
        } else {
            $shortDescription = null;
        }

        try {
            if ($form['action'] === 'update' && $form['product_id'] > 0) {
                $stmtExisting = $pdo->prepare('SELECT id, sku FROM products WHERE id = :id LIMIT 1');
                $stmtExisting->execute([':id' => $form['product_id']]);
                $existing = $stmtExisting->fetch();

                if (!$existing) {
                    $errors[] = 'Không tìm thấy sản phẩm để cập nhật.';
                } else {
                    $slug = generate_unique_product_slug($pdo, $form['name'], (int)$existing['id']);

                    $stmtUpdate = $pdo->prepare('UPDATE products
                        SET category_id = :category_id,
                            name = :name,
                            slug = :slug,
                            price = :price,
                            sale_price = :sale_price,
                            thumbnail = :thumbnail,
                            short_description = :short_description,
                            description = :description
                        WHERE id = :id');

                    $stmtUpdate->execute([
                        ':category_id' => $form['category_id'],
                        ':name' => $form['name'],
                        ':slug' => $slug,
                        ':price' => $price,
                        ':sale_price' => $salePrice,
                        ':thumbnail' => $thumbnailPath,
                        ':short_description' => $shortDescription,
                        ':description' => $description,
                        ':id' => $form['product_id'],
                    ]);

                    $success = 'Cập nhật sản phẩm thành công.';
                    $form['current_thumbnail'] = (string)($thumbnailPath ?? '');
                    $editId = $form['product_id'];
                }
            } else {
                $slug = generate_unique_product_slug($pdo, $form['name']);
                $sku = generate_unique_product_sku($pdo);

                $stmtInsert = $pdo->prepare('INSERT INTO products
                    (category_id, name, slug, sku, price, sale_price, stock, thumbnail, short_description, description, nutrition_info, is_featured, is_active)
                    VALUES
                    (:category_id, :name, :slug, :sku, :price, :sale_price, 0, :thumbnail, :short_description, :description, NULL, 0, 1)');

                $stmtInsert->execute([
                    ':category_id' => $form['category_id'],
                    ':name' => $form['name'],
                    ':slug' => $slug,
                    ':sku' => $sku,
                    ':price' => $price,
                    ':sale_price' => $salePrice,
                    ':thumbnail' => $thumbnailPath,
                    ':short_description' => $shortDescription,
                    ':description' => $description,
                ]);

                $newId = (int)$pdo->lastInsertId();
                $success = 'Tạo sản phẩm mới thành công.';
                header('Location: ' . app_url('index.php?page=admin_products&edit=' . $newId . '&created=1'));
                exit;
            }
        } catch (Throwable $exception) {
            $errors[] = 'Không thể lưu sản phẩm. Vui lòng kiểm tra dữ liệu và thử lại.';
        }
    }
}

if (isset($_GET['created']) && (int)$_GET['created'] === 1) {
    $success = 'Tạo sản phẩm mới thành công.';
}

$products = $pdo->query('SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.thumbnail, c.name AS category_name
    FROM products p
    INNER JOIN categories c ON c.id = p.category_id
    ORDER BY p.id DESC')->fetchAll();

function normalize_product_slug(string $text): string
{
    $text = trim(mb_strtolower($text));
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($converted !== false) {
        $text = $converted;
    }

    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'san-pham';
}

function generate_unique_product_slug(PDO $pdo, string $name, ?int $excludeId = null): string
{
    $base = normalize_product_slug($name);
    $slug = $base;
    $i = 1;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = :slug AND id <> :id');
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = :slug');
            $stmt->execute([':slug' => $slug]);
        }

        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }

        $slug = $base . '-' . $i;
        $i++;
    }
}

function generate_unique_product_sku(PDO $pdo): string
{
    do {
        $sku = 'PR' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku');
        $stmt->execute([':sku' => $sku]);
    } while ((int)$stmt->fetchColumn() > 0);

    return $sku;
}

function product_image_url(?string $thumbnail): string
{
    if ($thumbnail === null || trim($thumbnail) === '') {
        return app_url('assets/img/icon.png');
    }

    if (str_starts_with($thumbnail, 'http://') || str_starts_with($thumbnail, 'https://')) {
        return $thumbnail;
    }

    return app_url($thumbnail);
}
?>
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">Quản lý sản phẩm</h2>
        <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin')) ?>">Về Dashboard</a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><?= $form['action'] === 'update' ? 'Cập nhật sản phẩm' : 'Tạo sản phẩm mới' ?></h5>
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?= e((string)$form['action']) ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$form['product_id'] ?>">
                        <input type="hidden" name="current_thumbnail" value="<?= e((string)$form['current_thumbnail']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" name="name" required value="<?= e((string)$form['name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int)$category['id'] ?>" <?= (int)$form['category_id'] === (int)$category['id'] ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Giá bán cũ (VND)</label>
                                <input type="number" class="form-control" min="0" step="1000" name="price" required value="<?= e((string)$form['price']) ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Giá bán mới (VND)</label>
                                <input type="number" class="form-control" min="0" step="1000" name="sale_price" value="<?= e((string)$form['sale_price']) ?>" placeholder="Để trống nếu không giảm giá">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Hình ảnh sản phẩm</label>
                            <input type="file" class="form-control" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,.gif">
                            <small class="text-muted">Để trống nếu muốn giữ ảnh hiện tại.</small>
                        </div>

                        <?php if ((string)$form['current_thumbnail'] !== ''): ?>
                            <div class="mt-3">
                                <img src="<?= e(product_image_url((string)$form['current_thumbnail'])) ?>" alt="Ảnh hiện tại" class="img-fluid rounded border" style="max-height: 160px; object-fit: cover;">
                            </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Mô tả chi tiết sản phẩm..."><?= e((string)$form['description']) ?></textarea>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success"><?= $form['action'] === 'update' ? 'Lưu cập nhật' : 'Tạo sản phẩm' ?></button>
                            <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin_products')) ?>">Làm mới form</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Danh sách sản phẩm</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Tên</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Giá mới</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= e(product_image_url($product['thumbnail'])) ?>" alt="<?= e($product['name']) ?>" width="56" height="56" class="rounded border" style="object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= e($product['name']) ?></div>
                                            <small class="text-muted">SKU: <?= e($product['sku']) ?></small>
                                        </td>
                                        <td><?= e($product['category_name']) ?></td>
                                        <td><?= e(format_currency((float)$product['price'])) ?></td>
                                        <td>
                                            <?= $product['sale_price'] !== null ? e(format_currency((float)$product['sale_price'])) : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-success" href="<?= e(app_url('index.php?page=admin_products&edit=' . (int)$product['id'])) ?>">Sửa</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
