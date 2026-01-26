<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pending_items LIKE 'item_type'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Type: " . $col['Type'] . "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
