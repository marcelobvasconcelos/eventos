<?php
require_once __DIR__ . '/config/database.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM pending_items LIKE 'user_note'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $sql = "ALTER TABLE pending_items ADD COLUMN user_note TEXT AFTER observation";
        $pdo->exec($sql);
        echo "Column 'user_note' added successfully.";
    } else {
        echo "Column 'user_note' already exists.";
    }
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
