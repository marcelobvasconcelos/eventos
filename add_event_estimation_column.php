<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM events LIKE 'public_estimation'");
    $stmt->execute();
    $result = $stmt->fetch();

    if (!$result) {
        // Add column
        $sql = "ALTER TABLE events ADD COLUMN public_estimation INT DEFAULT 0 AFTER description";
        $pdo->exec($sql);
        echo "Column 'public_estimation' added successfully to 'events' table.\n";
    } else {
        echo "Column 'public_estimation' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
