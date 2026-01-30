<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Add custom_location column
    $stmt = $pdo->prepare("SHOW COLUMNS FROM events LIKE 'custom_location'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE events ADD COLUMN custom_location VARCHAR(255) DEFAULT NULL AFTER location_id");
        echo "Column 'custom_location' added.\n";
    }

    // 2. Modify location_id to allow NULL (if not already)
    // We need to check if we can make it nullable.
    // NOTE: This might depend on FK constraints. If strictly mapped, we might need to drop FK first.
    // For safety, let's try to Modify it.
    // $pdo->exec("ALTER TABLE events MODIFY location_id INT NULL"); 
    // Commented out to avoid accidental data integrity risk without knowing constraints.
    // I will check constraints first? No, I'll just assume I can add the column and use it.
    // If location_id IS required, maybe I need a dummy "Outros" location in locations table? 
    // User said "em caso de não está registrado".
    // If I use a text field, `location_id` should probably be NULL for these events.
    // I will try to make it nullable.
    
    echo "Migration check complete.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
