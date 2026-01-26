<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $key = 'event_creation_info_text';
    $defaultValue = "<strong>Atenção:</strong><br>
    <ul>
        <li>O seu evento passará por aprovação.</li>
        <li>Ao término do evento, você deve devolver a chave do local e os itens solicitados dentro de no máximo <strong>24 horas</strong>.</li>
        <li>Faça as solicitações com antecedência para conferir os materiais junto à seção de eventos.</li>
    </ul>";

    // Insert or update
    $stmt = $pdo->prepare("INSERT INTO configuracoes (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = value");
    $stmt->execute([$key, $defaultValue]);
    
    echo "Configuração '$key' adicionada/verificada com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
