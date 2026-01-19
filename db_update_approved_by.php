<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("ALTER TABLE events ADD COLUMN approved_by INT NULL");
    $pdo->exec("ALTER TABLE events ADD CONSTRAINT fk_event_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL");
    echo "Database schema updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
