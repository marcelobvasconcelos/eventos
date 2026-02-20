<?php
require_once 'config/database.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Alter events table
    echo "Altering events table...\n";
    $columns = $pdo->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('requires_registration', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN requires_registration TINYINT(1) DEFAULT 0");
        echo "- Added requires_registration\n";
    }
    if (!in_array('max_participants', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN max_participants INT DEFAULT NULL");
        echo "- Added max_participants\n";
    }
    if (!in_array('has_certificate', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN has_certificate TINYINT(1) DEFAULT 0");
        echo "- Added has_certificate\n";
    }

    // 2. Create registrations table
    echo "Creating registrations table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, email)
    )");
    echo "- Table registrations created/checked\n";

    // 3. Create attendances table
    echo "Creating attendances table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendances (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        document_number VARCHAR(50),
        privacy_accepted TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (event_id, email)
    )");
    echo "- Table attendances created/checked\n";

    echo "Database updated successfully!\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
?>
