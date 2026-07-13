<?php
/**
 * Simple image downloader (without resizing)
 * Downloads product images and saves locally
 * You can resize later with your preferred tool
 */

require_once __DIR__ . '/config/db.php';

$imagesDir = __DIR__ . '/assets/img/products';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
    echo "<p>✓ Created directory: <code>assets/img/products/</code></p>";
}

echo "<h2>🖼️ Downloading Product Images</h2>";
echo "<p><strong>📐 Standard Image Sizes (for reference):</strong></p>";
echo "<ul style='background: #f0fdf4; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #15803d;'>
    <li><strong>Product Thumbnail:</strong> 600x450px (4:3 ratio) - ~50-80KB JPEG</li>
    <li><strong>Category Banner:</strong> 800x400px (2:1 ratio) - ~80-120KB JPEG</li>
    <li><strong>Quality:</strong> 85% JPEG compression for web</li>
</ul>";
echo "<hr>";

$pdo = db();
$downloadCount = 0;
$skipCount = 0;

// Products
echo "<h3>📦 Products</h3>";
$products = $pdo->query("SELECT id, name, thumbnail FROM products WHERE thumbnail LIKE 'http%' ORDER BY id")->fetchAll();

if (empty($products)) {
    echo "<p style='color: green;'>✓ All products already have local images</p>";
} else {
    foreach ($products as $product) {
        $productId = $product['id'];
        $productName = $product['name'];
        $externalUrl = $product['thumbnail'];

        // Generate filename
        $urlParts = parse_url($externalUrl);
        $pathParts = explode('/', $urlParts['path']);
        $fileExt = pathinfo(end($pathParts), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'product_' . $productId . '.' . $fileExt;
        $localPath = $imagesDir . '/' . $filename;
        $localUrl = '/demo/tienha-drinks/assets/img/products/' . $filename;

        // Skip if already exists
        if (file_exists($localPath)) {
            echo "<div style='margin: 0.8rem 0; padding: 0.8rem; background: #fff; border-left: 3px solid #999;'>";
            echo "ID {$productId}: {$productName} <span style='color: orange;'>⊘ Already exists</span>";
            echo "</div>";
            $skipCount++;
            continue;
        }

        echo "<div style='margin: 0.8rem 0; padding: 0.8rem; background: #fff; border-left: 3px solid #15803d;'>";
        echo "<strong>ID {$productId}:</strong> {$productName}<br>";

        try {
            // Download
            $imageData = @file_get_contents($externalUrl, false, stream_context_create([
                'http' => ['timeout' => 15],
                'https' => ['timeout' => 15, 'verify_peer' => false]
            ]));

            if (!$imageData) {
                echo "<span style='color: red;'>✗ Failed to download</span>";
                echo "</div>";
                continue;
            }

            // Validate it's an image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);

            if (strpos($mime, 'image') === false) {
                echo "<span style='color: red;'>✗ Not an image (mime: {$mime})</span>";
                echo "</div>";
                continue;
            }

            // Save
            if (file_put_contents($localPath, $imageData) === false) {
                echo "<span style='color: red;'>✗ Failed to save</span>";
                echo "</div>";
                continue;
            }

            $fileSize = round(filesize($localPath) / 1024, 1);
            echo "✓ Downloaded ({$fileSize}KB)<br>";

            // Update database
            $stmt = $pdo->prepare("UPDATE products SET thumbnail = :thumb WHERE id = :id");
            $stmt->execute([':thumb' => $localUrl, ':id' => $productId]);
            echo "✓ Database updated<br>";
            echo "<code>{$localUrl}</code>";

            $downloadCount++;
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Error: {$e->getMessage()}</span>";
        }

        echo "</div>";
    }
}

// Categories
echo "<h3>📁 Categories</h3>";
$categories = $pdo->query("SELECT id, name, image FROM categories WHERE image LIKE 'http%' ORDER BY id")->fetchAll();

if (empty($categories)) {
    echo "<p style='color: green;'>✓ All categories already have local images</p>";
} else {
    foreach ($categories as $cat) {
        $catId = $cat['id'];
        $catName = $cat['name'];
        $externalUrl = $cat['image'];

        $urlParts = parse_url($externalUrl);
        $pathParts = explode('/', $urlParts['path']);
        $fileExt = pathinfo(end($pathParts), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'category_' . $catId . '.' . $fileExt;
        $localPath = $imagesDir . '/' . $filename;
        $localUrl = '/demo/tienha-drinks/assets/img/products/' . $filename;

        if (file_exists($localPath)) {
            echo "<div style='margin: 0.8rem 0; padding: 0.8rem; background: #fff; border-left: 3px solid #999;'>";
            echo "Category {$catId}: {$catName} <span style='color: orange;'>⊘ Already exists</span>";
            echo "</div>";
            $skipCount++;
            continue;
        }

        echo "<div style='margin: 0.8rem 0; padding: 0.8rem; background: #fff; border-left: 3px solid #15803d;'>";
        echo "<strong>Category {$catId}:</strong> {$catName}<br>";

        try {
            $imageData = @file_get_contents($externalUrl, false, stream_context_create([
                'http' => ['timeout' => 15],
                'https' => ['timeout' => 15, 'verify_peer' => false]
            ]));

            if (!$imageData) {
                echo "<span style='color: red;'>✗ Failed to download</span>";
                echo "</div>";
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);

            if (strpos($mime, 'image') === false) {
                echo "<span style='color: red;'>✗ Not an image</span>";
                echo "</div>";
                continue;
            }

            if (file_put_contents($localPath, $imageData) === false) {
                echo "<span style='color: red;'>✗ Failed to save</span>";
                echo "</div>";
                continue;
            }

            $fileSize = round(filesize($localPath) / 1024, 1);
            echo "✓ Downloaded ({$fileSize}KB)<br>";

            $stmt = $pdo->prepare("UPDATE categories SET image = :img WHERE id = :id");
            $stmt->execute([':img' => $localUrl, ':id' => $catId]);
            echo "✓ Database updated<br>";
            echo "<code>{$localUrl}</code>";

            $downloadCount++;
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Error: {$e->getMessage()}</span>";
        }

        echo "</div>";
    }
}

echo "<hr>";
echo "<h3 style='color: green;'>✓ Download Complete!</h3>";
echo "<p><strong>Stats:</strong> Downloaded {$downloadCount}, Skipped {$skipCount}</p>";
echo "<p>All images are now in <code>assets/img/products/</code></p>";

echo "<div style='background: #f0fdf4; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #15803d; margin: 1.5rem 0;'>";
echo "<h4>📐 Image Upload Standards (for your reference):</h4>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #e4fce9;'>";
echo "  <th style='border: 1px solid #bfe7cb; padding: 0.8rem; text-align: left;'>Type</th>";
echo "  <th style='border: 1px solid #bfe7cb; padding: 0.8rem; text-align: left;'>Dimensions</th>";
echo "  <th style='border: 1px solid #bfe7cb; padding: 0.8rem; text-align: left;'>Aspect Ratio</th>";
echo "  <th style='border: 1px solid #bfe7cb; padding: 0.8rem; text-align: left;'>File Size</th>";
echo "</tr>";
echo "<tr>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'><strong>Product</strong></td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'><code>600 × 450px</code></td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'>4:3</td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'>50-80 KB</td>";
echo "</tr>";
echo "<tr>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'><strong>Category</strong></td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'><code>800 × 400px</code></td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'>2:1</td>";
echo "  <td style='border: 1px solid #bfe7cb; padding: 0.8rem;'>80-120 KB</td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "<p style='margin-top: 2rem;'>";
echo "  <a href='index.php?page=home' class='btn btn-success' style='padding: 0.8rem 1.5rem; text-decoration: none; display: inline-block; border-radius: 8px; background: #15803d; color: white;'>← Back to Home</a>";
echo "</p>";
?>
