<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$file = __DIR__ . '/models/Event.php';

if (!file_exists($file)) {
    die("models/Event.php não encontrado.");
}

$contents = file_get_contents($file);

// Find createEvent method
$pos = strpos($contents, 'function createEvent');
if ($pos === false) {
    echo "Método createEvent não encontrado no arquivo.<br>";
} else {
    // Show context around createEvent
    echo "<h1>Conteúdo de models/Event.php (createEvent)</h1>";
    echo "<pre>";
    // Extract roughly 20 lines starting from createEvent
    echo htmlspecialchars(substr($contents, $pos, 1500)); 
    echo "</pre>";
}
echo "<hr>";
echo "Se você ver 'end_time' no INSERT logo abaixo de createEvent, o arquivo está errado/antigo.<br>";
echo "Se você ver 'end_date', o arquivo está correto.";
?>
