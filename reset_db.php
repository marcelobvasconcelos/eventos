<?php
require_once 'config/database.php';

try {
    // Disable foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    // List of tables to truncate
    $tables = [
        'notifications',
        'loans',
        'asset_items',
        'assets',
        'event_requests',
        'events',
        'categories',
        'locations',
        'users'
    ];

    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
        echo "Tabela '$table' limpa.<br>";
    }

    // Enable foreign key checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Re-create default Admin user
    // Password: admin (you should change this immediately)
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@eventos.com', ?, 'admin')");
    $stmt->execute([$password]);
    
    echo "<br><strong>Sucesso!</strong> Todas as tabelas foram limpas.";
    echo "<br>Um usuário padrão foi criado:";
    echo "<br>Email: <strong>admin@eventos.com</strong>";
    echo "<br>Senha: <strong>admin</strong>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
