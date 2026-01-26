<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $configs = [
        'footer_logo_1' => 'lib/ufrpe.jpeg',
        'footer_logo_2' => 'lib/eventos.jpeg',
        'footer_col1_title' => 'Seção de Eventos',
        'footer_col1_subtitle' => 'UAST / UFRPE',
        'footer_address' => '<strong>Auditório Atikum</strong><br>Av. Gregório Ferraz Nogueira, S/N<br>Bairro José Tomé de Souza Ramos<br>CEP 56909-535 - Serra Talhada/PE',
        'footer_email' => 'eventos.uast@ufrpe.br',
        'footer_phone' => '(87) 3929-3274',
        // Existing social keys are: footer_social_instagram, footer_social_youtube
        // Existing bottom text: footer_text (will use for bottom socket)
    ];

    $stmt = $pdo->prepare("INSERT INTO configuracoes (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = value");
    
    foreach ($configs as $key => $default) {
        $stmt->execute([$key, $default]);
        echo "Configuração '$key' verificada/adicionada.\n";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
