<?php
require 'config/database.php';

try {
    echo "Checking for image_path column...\n";
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'image_path'");
    $column = $stmt->fetch();

    if ($column) {
        echo "Column 'image_path' already exists.\n";
    } else {
        echo "Column not found. Adding 'image_path'...\n";
        $pdo->exec("ALTER TABLE events ADD COLUMN image_path VARCHAR(255) DEFAULT NULL");
        echo "Column 'image_path' added successfully.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
