<?php
require_once 'config/database.php';

try {
    // 1. Create asset_categories table
    $sql1 = "CREATE TABLE IF NOT EXISTS asset_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql1);
    echo "Table 'asset_categories' checked/created.<br>";

    // 2. Add category_id to assets table
    // Check if column exists first to avoid error
    // fetchAll to clear buffer
    $stmt = $pdo->prepare("SHOW COLUMNS FROM assets LIKE 'category_id'");
    $stmt->execute();
    $exists = $stmt->fetch();
    $stmt->closeCursor(); // Important

    if (!$exists) {
        $sql2 = "ALTER TABLE assets ADD COLUMN category_id INT NULL AFTER description;";
        $pdo->exec($sql2);
        echo "Column 'category_id' added to 'assets' table.<br>";

        // Add Foreign Key
        $sql3 = "ALTER TABLE assets ADD CONSTRAINT fk_asset_category FOREIGN KEY (category_id) REFERENCES asset_categories(id) ON DELETE SET NULL;";
        $pdo->exec($sql3);
        echo "Foreign Key 'fk_asset_category' added.<br>";
    } else {
        echo "Column 'category_id' already exists in 'assets' table.<br>";
    }

    echo "Database migration successful.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
