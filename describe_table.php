<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("DESCRIBE pending_items");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo $col['Field'] . " | " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
