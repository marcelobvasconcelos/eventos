<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM assets LIKE 'requires_patrimony'");
    $stmt->execute();
    $result = $stmt->fetch();

    if (!$result) {
        // Add column
        $sql = "ALTER TABLE assets ADD COLUMN requires_patrimony TINYINT(1) DEFAULT 0 AFTER category_id";
        $pdo->exec($sql);
        echo "Column 'requires_patrimony' added successfully to 'assets' table.\n";
    } else {
        echo "Column 'requires_patrimony' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
