<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

echo "<h1>Diagnóstico de Imagens de Locais</h1>";

try {
    // 1. Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'location_images'");
    if ($stmt->rowCount() == 0) {
        die("<p style='color:red'>ERRO: A tabela <strong>location_images</strong> NÃO existe no banco de dados.</p>");
    } else {
        echo "<p style='color:green'>SUCESSO: Tabela <strong>location_images</strong> encontrada.</p>";
    }

    // 2. Count total images
    $stmt = $pdo->query("SELECT COUNT(*) FROM location_images");
    $count = $stmt->fetchColumn();
    echo "<p>Total de imagens registradas no banco: <strong>$count</strong></p>";

    if ($count == 0) {
        echo "<p style='color:orange'>AVISO: Não há imagens registradas. O carrossel não aparecerá sem imagens.</p>";
    } else {
        // 3. Check specific locations
        $stmt = $pdo->query("SELECT li.*, l.name as location_name FROM location_images li JOIN locations l ON li.location_id = l.id LIMIT 5");
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1' cellpadding='5'><tr><th>Locais</th><th>Caminho da Imagem (BD)</th><th>Status do Arquivo</th></tr>";
        
        foreach ($images as $img) {
            $fullPath = __DIR__ . '/..' . $img['image_path']; // Adjust based on how image_path is stored (usually starts with /eventos/...)
            // Remove /eventos prefix for file check if needed, or check relative to document root.
            // stored path: /eventos/public/uploads/...
            // real path: c:\xampp\htdocs\eventos\public\uploads...
            
            // Fix path for file_exists check
            $relativePath = str_replace('/eventos/', '', $img['image_path']);
            $realPath = __DIR__ . '/' . $relativePath;

            $exists = file_exists($realPath) ? "<span style='color:green'>Encontrado</span>" : "<span style='color:red'>ARQUIVO FALTANDO</span>";
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($img['location_name']) . "</td>";
            echo "<td>" . htmlspecialchars($img['image_path']) . "</td>";
            echo "<td>$exists <br><small>Checado em: $realPath</small></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Erro de Banco de Dados: " . $e->getMessage() . "</p>";
}
?>
