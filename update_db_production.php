<?php
require_once 'config/database.php';

echo "Iniciando atualização do banco de dados...\n";

try {
    // 1. Tabela de Configurações
    echo "Verificando tabela 'configuracoes'...\n";
    $sql = "CREATE TABLE IF NOT EXISTS configuracoes (
        `key` VARCHAR(255) PRIMARY KEY,
        `value` TEXT
    )";
    $pdo->exec($sql);
    echo "Tabela 'configuracoes' verificada/criada.\n";

    // 2. Colunas na tabela 'events'
    echo "Verificando colunas na tabela 'events'...\n";
    $columns = $pdo->query("DESCRIBE events")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('requires_registration', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN requires_registration TINYINT(1) DEFAULT 0");
        echo "Coluna 'requires_registration' adicionada.\n";
    }
    
    if (!in_array('max_participants', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN max_participants INT DEFAULT NULL");
        echo "Coluna 'max_participants' adicionada.\n";
    }

    if (!in_array('has_certificate', $columns)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN has_certificate TINYINT(1) DEFAULT 0");
        echo "Coluna 'has_certificate' adicionada.\n";
    }
    
    if (!in_array('custom_location', $columns)) {
         // Check if it exists (it might from previous updates)
         $pdo->exec("ALTER TABLE events ADD COLUMN custom_location VARCHAR(255) NULL DEFAULT NULL AFTER location_id");
         echo "Coluna 'custom_location' adicionada.\n";
    }

    // 3. Tabela de Inscrições (Registrations)
    echo "Verificando tabela 'registrations'...\n";
    $sql = "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabela 'registrations' verificada/criada.\n";

    // 4. Tabela de Presença (Attendances)
    echo "Verificando tabela 'attendances'...\n";
    $sql = "CREATE TABLE IF NOT EXISTS attendances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        document_number VARCHAR(50),
        privacy_policy_accepted TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Tabela 'attendances' verificada/criada.\n";

    echo "Atualização concluída com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro ao atualizar banco de dados: " . $e->getMessage() . "\n";
}
