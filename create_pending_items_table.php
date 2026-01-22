<?php
require_once __DIR__ . '/config/database.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS pending_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT NOT NULL,
        user_id INT NOT NULL,
        item_type ENUM('key', 'asset') NOT NULL,
        description VARCHAR(255) NOT NULL,
        status ENUM('pending', 'user_informed', 'completed', 'contested') NOT NULL DEFAULT 'pending',
        observation TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
    echo "Table 'pending_items' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
