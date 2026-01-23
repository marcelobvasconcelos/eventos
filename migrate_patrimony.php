<?php
require_once 'config/database.php';

try {
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM assets LIKE 'requires_patrimony'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $sql = "ALTER TABLE assets ADD COLUMN requires_patrimony TINYINT(1) DEFAULT 0 AFTER category_id;";
        $pdo->exec($sql);
        echo "Column 'requires_patrimony' added to 'assets' table.<br>";
    } else {
        echo "Column 'requires_patrimony' already exists.<br>";
    }
    echo "Migration successful.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
