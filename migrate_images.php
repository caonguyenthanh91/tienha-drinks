<?php
/**
 * Download product images from external URLs and save locally
 * Resize images to 600x450px (optimal for product display)
 */

require_once __DIR__ . '/config/db.php';

$imagesDir = __DIR__ . '/assets/img/products';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

// Target dimensions: 600x450 (4:3 aspect ratio - good for product cards)
$targetWidth = 600;
$targetHeight = 450;
$quality = 85; // JPEG quality

echo "<h2>🖼️ Downloading & Optimizing Product Images</h2>";
echo "<p>Target size: {$targetWidth}x{$targetHeight}px | Quality: {$quality}%</p>";

// Get all products with external image URLs
$products = db()->query("SELECT id, name, thumbnail FROM products WHERE thumbnail LIKE 'http%' ORDER BY id")->fetchAll();

if (empty($products)) {
    echo "<p>✓ No external URLs found - all images already local!</p>";
    exit;
}

$pdo = db();
$updateStmt = $pdo->prepare("UPDATE products SET thumbnail = :thumb WHERE id = :id");

foreach ($products as $product) {
    $productId = $product['id'];
    $productName = $product['name'];
    $externalUrl = $product['thumbnail'];

    // Generate local filename
    $filename = 'product_' . $productId . '_' . time() . '.jpg';
    $localPath = $imagesDir . '/' . $filename;
    $localUrl = '/demo/tienha-drinks/assets/img/products/' . $filename;

    echo "<div style='margin: 1rem 0; padding: 1rem; border: 1px solid #ddd; border-radius: 8px;'>";
    echo "<strong>ID {$productId}:</strong> {$productName}<br>";
    echo "From: {$externalUrl}<br>";

    try {
        // Download image
        echo "↓ Downloading... ";
        $imageData = @file_get_contents($externalUrl, false, stream_context_create([
            'http' => ['timeout' => 10],
            'https' => ['timeout' => 10]
        ]));

        if (!$imageData) {
            echo "<span style='color: red'>✗ Failed to download</span><br>";
            continue;
        }
        echo "✓ ";

        // Create image from data
        $image = imagecreatefromstring($imageData);
        if (!$image) {
            echo "<span style='color: red'>✗ Invalid image format</span><br>";
            continue;
        }
        echo "✓ ";

        // Get original dimensions
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        echo "({$origWidth}x{$origHeight}) → ";

        // Calculate resize maintaining aspect ratio
        $ratio = min($targetWidth / $origWidth, $targetHeight / $origHeight);
        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);

        // Create new image with padding
        $newImage = imagecreatetruecolor($targetWidth, $targetHeight);
        $bgColor = imagecolorallocate($newImage, 255, 255, 255); // White background
        imagefill($newImage, 0, 0, $bgColor);

        // Calculate position to center image
        $x = (int)(($targetWidth - $newWidth) / 2);
        $y = (int)(($targetHeight - $newHeight) / 2);

        // Resize and copy
        imagecopyresampled($newImage, $image, $x, $y, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as JPEG
        imagejpeg($newImage, $localPath, $quality);
        imagedestroy($image);
        imagedestroy($newImage);

        // Get file size
        $fileSize = round(filesize($localPath) / 1024, 1);
        echo "<span style='color: green'>{$targetWidth}x{$targetHeight} ({$fileSize}KB)</span><br>";

        // Update database
        $updateStmt->execute([':thumb' => $localUrl, ':id' => $productId]);
        echo "Database updated: <code>{$localUrl}</code><br>";

    } catch (Exception $e) {
        echo "<span style='color: red'>✗ Error: {$e->getMessage()}</span><br>";
    }

    echo "</div>";
}

// Get category images too
echo "<h3>📁 Category Images</h3>";
$categories = db()->query("SELECT id, name, image FROM categories WHERE image LIKE 'http%' ORDER BY id")->fetchAll();

foreach ($categories as $cat) {
    $catId = $cat['id'];
    $catName = $cat['name'];
    $externalUrl = $cat['image'];

    $filename = 'category_' . $catId . '_' . time() . '.jpg';
    $localPath = $imagesDir . '/' . $filename;
    $localUrl = '/demo/tienha-drinks/assets/img/products/' . $filename;

    echo "<div style='margin: 1rem 0; padding: 1rem; border: 1px solid #ddd; border-radius: 8px;'>";
    echo "<strong>Category {$catId}:</strong> {$catName}<br>";
    echo "From: {$externalUrl}<br>";

    try {
        echo "↓ Downloading... ";
        $imageData = @file_get_contents($externalUrl, false, stream_context_create([
            'http' => ['timeout' => 10],
            'https' => ['timeout' => 10]
        ]));

        if (!$imageData) {
            echo "<span style='color: red'>✗ Failed</span><br>";
            continue;
        }
        echo "✓ ";

        $image = imagecreatefromstring($imageData);
        if (!$image) {
            echo "<span style='color: red'>✗ Invalid format</span><br>";
            continue;
        }
        echo "✓ ";

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // Categories: 800x400px (wider)
        $catWidth = 800;
        $catHeight = 400;
        $ratio = min($catWidth / $origWidth, $catHeight / $origHeight);
        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);

        $newImage = imagecreatetruecolor($catWidth, $catHeight);
        $bgColor = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $bgColor);

        $x = (int)(($catWidth - $newWidth) / 2);
        $y = (int)(($catHeight - $newHeight) / 2);

        imagecopyresampled($newImage, $image, $x, $y, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagejpeg($newImage, $localPath, $quality);
        imagedestroy($image);
        imagedestroy($newImage);

        $fileSize = round(filesize($localPath) / 1024, 1);
        echo "<span style='color: green'>{$catWidth}x{$catHeight} ({$fileSize}KB)</span><br>";

        $updateCatStmt = $pdo->prepare("UPDATE categories SET image = :img WHERE id = :id");
        $updateCatStmt->execute([':img' => $localUrl, ':id' => $catId]);
        echo "Database updated: <code>{$localUrl}</code><br>";

    } catch (Exception $e) {
        echo "<span style='color: red'>✗ Error: {$e->getMessage()}</span><br>";
    }

    echo "</div>";
}

echo "<h3 style='color: green;'>✓ Migration Complete!</h3>";
echo "<p>All images are now stored locally in <code>assets/img/products/</code></p>";
echo "<p><strong>📐 Image Size Standards:</strong></p>";
echo "<ul>
    <li><strong>Product Thumbnails:</strong> 600x450px (4:3 aspect ratio) ← Use this for products</li>
    <li><strong>Category Headers:</strong> 800x400px (2:1 aspect ratio)</li>
    <li><strong>Quality:</strong> 85% JPEG (balance size & quality)</li>
</ul>";
echo "<p><a href='index.php?page=home' class='btn btn-success'>← Back to home</a></p>";
?>
