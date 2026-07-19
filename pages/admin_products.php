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
    'sku' => $editingProduct['sku'] ?? '',
    'price' => $editingProduct ? (string)$editingProduct['price'] : '',
    'sale_price' => $editingProduct && $editingProduct['sale_price'] !== null ? (string)$editingProduct['sale_price'] : '',
    'stock' => $editingProduct ? (string)$editingProduct['stock'] : '0',
    'short_description' => $editingProduct['short_description'] ?? '',
    'description' => $editingProduct['description'] ?? '',
    'nutrition_info' => $editingProduct['nutrition_info'] ?? '',
    'is_featured' => $editingProduct ? (int)$editingProduct['is_featured'] : 0,
    'is_active' => $editingProduct ? (int)$editingProduct['is_active'] : 1,
    'current_thumbnail' => $editingProduct['thumbnail'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['action'] = $_POST['action'] ?? 'create';

    if ($form['action'] === 'toggle_active') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $nextStatus = (int)($_POST['next_status'] ?? 0) === 1 ? 1 : 0;

        if ($productId > 0) {
            $stmtToggle = $pdo->prepare('UPDATE products SET is_active = :is_active WHERE id = :id');
            $stmtToggle->execute([
                ':is_active' => $nextStatus,
                ':id' => $productId,
            ]);
            $success = $nextStatus === 1 ? 'Đã kích hoạt sản phẩm.' : 'Đã tạm ngưng sản phẩm.';
        } else {
            $errors[] = 'Không tìm thấy sản phẩm để đổi trạng thái.';
        }
    }

    if ($form['action'] !== 'toggle_active') {
    $form['product_id'] = (int)($_POST['product_id'] ?? 0);
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['name'] = trim((string)($_POST['name'] ?? ''));
    $form['sku'] = trim((string)($_POST['sku'] ?? ''));
    $form['price'] = trim((string)($_POST['price'] ?? ''));
    $form['sale_price'] = trim((string)($_POST['sale_price'] ?? ''));
    $form['stock'] = trim((string)($_POST['stock'] ?? '0'));
    $form['short_description'] = trim((string)($_POST['short_description'] ?? ''));
    $form['description'] = trim((string)($_POST['description'] ?? ''));
    $form['nutrition_info'] = trim((string)($_POST['nutrition_info'] ?? ''));
    $form['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;
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

    if ($form['stock'] === '' || !is_numeric($form['stock']) || (int)$form['stock'] < 0) {
        $errors[] = 'Tồn kho phải là số nguyên không âm.';
    }

    if ($form['sku'] !== '' && !preg_match('/^[A-Za-z0-9_-]{2,40}$/', $form['sku'])) {
        $errors[] = 'SKU chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới (2-40 ký tự).';
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
        $stock = (int)$form['stock'];
        $shortDescription = $form['short_description'] !== '' ? $form['short_description'] : null;
        $description = $form['description'] !== '' ? $form['description'] : null;
        $nutritionInfo = $form['nutrition_info'] !== '' ? $form['nutrition_info'] : null;

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
                            sku = :sku,
                            slug = :slug,
                            price = :price,
                            sale_price = :sale_price,
                            stock = :stock,
                            thumbnail = :thumbnail,
                            short_description = :short_description,
                            description = :description,
                            nutrition_info = :nutrition_info,
                            is_featured = :is_featured,
                            is_active = :is_active
                        WHERE id = :id');

                    $sku = $form['sku'] !== '' ? strtoupper($form['sku']) : (string)$existing['sku'];

                    $stmtSku = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku AND id <> :id');
                    $stmtSku->execute([':sku' => $sku, ':id' => $form['product_id']]);
                    if ((int)$stmtSku->fetchColumn() > 0) {
                        $errors[] = 'SKU đã tồn tại, vui lòng dùng mã khác.';
                    }

                    if ($errors !== []) {
                        throw new RuntimeException('validation_failed');
                    }

                    $stmtUpdate->execute([
                        ':category_id' => $form['category_id'],
                        ':name' => $form['name'],
                        ':sku' => $sku,
                        ':slug' => $slug,
                        ':price' => $price,
                        ':sale_price' => $salePrice,
                        ':stock' => $stock,
                        ':thumbnail' => $thumbnailPath,
                        ':short_description' => $shortDescription,
                        ':description' => $description,
                        ':nutrition_info' => $nutritionInfo,
                        ':is_featured' => $form['is_featured'],
                        ':is_active' => $form['is_active'],
                        ':id' => $form['product_id'],
                    ]);

                    $success = 'Cập nhật sản phẩm thành công.';
                    $form['current_thumbnail'] = (string)($thumbnailPath ?? '');
                    $editId = $form['product_id'];
                }
            } else {
                $slug = generate_unique_product_slug($pdo, $form['name']);
                $sku = $form['sku'] !== '' ? strtoupper($form['sku']) : generate_unique_product_sku($pdo);

                $stmtSku = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku');
                $stmtSku->execute([':sku' => $sku]);
                if ((int)$stmtSku->fetchColumn() > 0) {
                    $errors[] = 'SKU đã tồn tại, vui lòng dùng mã khác.';
                    throw new RuntimeException('validation_failed');
                }

                $stmtInsert = $pdo->prepare('INSERT INTO products
                    (category_id, name, slug, sku, price, sale_price, stock, thumbnail, short_description, description, nutrition_info, is_featured, is_active)
                    VALUES
                    (:category_id, :name, :slug, :sku, :price, :sale_price, :stock, :thumbnail, :short_description, :description, :nutrition_info, :is_featured, :is_active)');

                $stmtInsert->execute([
                    ':category_id' => $form['category_id'],
                    ':name' => $form['name'],
                    ':slug' => $slug,
                    ':sku' => $sku,
                    ':price' => $price,
                    ':sale_price' => $salePrice,
                    ':stock' => $stock,
                    ':thumbnail' => $thumbnailPath,
                    ':short_description' => $shortDescription,
                    ':description' => $description,
                    ':nutrition_info' => $nutritionInfo,
                    ':is_featured' => $form['is_featured'],
                    ':is_active' => $form['is_active'],
                ]);

                $newId = (int)$pdo->lastInsertId();
                $success = 'Tạo sản phẩm mới thành công.';
                header('Location: ' . app_url('index.php?page=admin_products&edit=' . $newId . '&created=1'));
                exit;
            }
        } catch (Throwable $exception) {
            if ($exception->getMessage() === 'validation_failed') {
                // Validation messages already populated above.
            } else {
            $errors[] = 'Không thể lưu sản phẩm. Vui lòng kiểm tra dữ liệu và thử lại.';
            }
        }
    }
    }
}

if (isset($_GET['created']) && (int)$_GET['created'] === 1) {
    $success = 'Tạo sản phẩm mới thành công.';
}

$products = $pdo->query('SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.stock, p.thumbnail, p.is_featured, p.is_active, c.name AS category_name
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

    $thumbnail = trim($thumbnail);

    if (str_starts_with($thumbnail, 'http://') || str_starts_with($thumbnail, 'https://')) {
        return $thumbnail;
    }

    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    $base = $base === '.' ? '' : $base;
    if (str_starts_with($thumbnail, $base . '/')) {
        return $thumbnail;
    }

    return app_url(ltrim($thumbnail, '/'));
}
?>
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">Quản lý sản phẩm</h2>
        <a class="btn btn-outline-secondary" href="<?= e(app_url('index.php?page=admin_orders')) ?>">Về xử lý đơn</a>
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
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" value="<?= e((string)$form['sku']) ?>" placeholder="Để trống để tự tạo mã">
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
                            <label class="form-label">Tồn kho</label>
                            <input type="number" class="form-control" min="0" step="1" name="stock" value="<?= e((string)$form['stock']) ?>">
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
                            <label class="form-label">Mô tả ngắn</label>
                            <input type="text" class="form-control" name="short_description" maxlength="255" value="<?= e((string)$form['short_description']) ?>" placeholder="Mô tả ngắn hiển thị trên card sản phẩm">
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Mô tả chi tiết sản phẩm..."><?= e((string)$form['description']) ?></textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Thông tin dinh dưỡng</label>
                            <textarea class="form-control" rows="2" name="nutrition_info" placeholder="Ví dụ: Calories: 120 | Đường: 20g"><?= e((string)$form['nutrition_info']) ?></textarea>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?= (int)$form['is_featured'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isFeatured">Sản phẩm nổi bật</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= (int)$form['is_active'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Đang kinh doanh</label>
                            </div>
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
                                    <th>Trạng thái</th>
                                    <th>Giá</th>
                                    <th>Giá mới</th>
                                    <th>Tồn</th>
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
                                        <td>
                                            <?php if ((int)$product['is_active'] === 1): ?>
                                                <span class="badge text-bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Deactive</span>
                                            <?php endif; ?>
                                            <?php if ((int)$product['is_featured'] === 1): ?>
                                                <span class="badge text-bg-warning text-dark">Nổi bật</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e(format_currency((float)$product['price'])) ?></td>
                                        <td>
                                            <?= $product['sale_price'] !== null ? e(format_currency((float)$product['sale_price'])) : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td><?= (int)$product['stock'] ?></td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a class="btn btn-sm btn-outline-success" href="<?= e(app_url('index.php?page=admin_products&edit=' . (int)$product['id'])) ?>">Sửa</a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                                    <input type="hidden" name="next_status" value="<?= (int)$product['is_active'] === 1 ? '0' : '1' ?>">
                                                    <button type="submit" class="btn btn-sm <?= (int)$product['is_active'] === 1 ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                                                        <?= (int)$product['is_active'] === 1 ? 'Deactive' : 'Active' ?>
                                                    </button>
                                                </form>
                                            </div>
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
