<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM events LIKE 'schedule_file_path'");
    $stmt->execute();
    $result = $stmt->fetch();

    if (!$result) {
        // Add column
        $sql = "ALTER TABLE events ADD COLUMN schedule_file_path VARCHAR(255) DEFAULT NULL AFTER image_path";
        $pdo->exec($sql);
        echo "Column 'schedule_file_path' added successfully to 'events' table.\n";
    } else {
        echo "Column 'schedule_file_path' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
