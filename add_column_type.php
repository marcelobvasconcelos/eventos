<?php
require_once __DIR__ . '/config/database.php';

try {
    $sql = "ALTER TABLE events ADD COLUMN type VARCHAR(50) DEFAULT 'evento_publico' AFTER status";
    $pdo->exec($sql);
    echo "Column 'type' added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'type' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
