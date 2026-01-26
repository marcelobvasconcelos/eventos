<?php
require_once __DIR__ . '/config/database.php';

try {
    // $pdo is created in config/database.php
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking 'events' table structure...\n";

    // check if columns exist
    $columns = [];
    $stmt = $pdo->query("DESCRIBE events");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }

    if (!in_array('external_link', $columns)) {
        echo "Adding 'external_link' column...\n";
        $pdo->exec("ALTER TABLE events ADD COLUMN external_link VARCHAR(255) DEFAULT NULL AFTER image_path");
    } else {
        echo "'external_link' already exists.\n";
    }

    if (!in_array('link_title', $columns)) {
        echo "Adding 'link_title' column...\n";
        $pdo->exec("ALTER TABLE events ADD COLUMN link_title VARCHAR(100) DEFAULT NULL AFTER external_link");
    } else {
        echo "'link_title' already exists.\n";
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
