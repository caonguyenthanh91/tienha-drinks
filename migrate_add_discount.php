<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

try {
    $pdo = db();

    // Check if discount_percent column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM customer_tiers LIKE 'discount_percent'");
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        echo "Adding discount_percent column to customer_tiers...\n";
        $pdo->exec("ALTER TABLE customer_tiers ADD COLUMN discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER min_spending");

        // Update existing data with correct discount percentages
        $pdo->exec("UPDATE customer_tiers SET discount_percent = 0 WHERE name = 'Đồng'");
        $pdo->exec("UPDATE customer_tiers SET discount_percent = 5 WHERE name = 'Bạc'");
        $pdo->exec("UPDATE customer_tiers SET discount_percent = 10 WHERE name = 'Vàng'");

        echo "✓ Migration completed successfully!\n";
    } else {
        echo "Column discount_percent already exists.\n";
    }
} catch (Throwable $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
