<?php
require_once 'config/database.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('Pendente', 'Ativo', 'Inativo') NOT NULL DEFAULT 'Pendente'");
    echo "Coluna 'status' adicionada com sucesso à tabela 'users'.";
    
    // Set existing users to 'Ativo' to avoid locking out current users (like the Admin created in reset)
    $pdo->exec("UPDATE users SET status = 'Ativo' WHERE role = 'admin'");
    echo "<br>Administradores definidos como 'Ativo'.";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
