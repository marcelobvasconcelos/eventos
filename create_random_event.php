<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Event.php';
require_once __DIR__ . '/models/Location.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/User.php';

echo "<h1>Gerador de Evento Aleatório</h1>";

try {
    $eventModel = new Event();
    $locationModel = new Location();
    $categoryModel = new Category();
    $userModel = new User();

    // Fetch random existing IDs
    $locations = $locationModel->getAllLocations();
    if (empty($locations)) die("Erro: Nenhuma localização cadastrada.");
    $randomLocation = $locations[array_rand($locations)];

    $categories = $categoryModel->getAllCategories();
    if (empty($categories)) die("Erro: Nenhuma categoria cadastrada.");
    $randomCategory = $categories[array_rand($categories)];

    // Fetch a user (optimistically picking ID 1 or random existing)
    $users = $userModel->getAllUsers();
    if (empty($users)) die("Erro: Nenhum usuário cadastrado.");
    $randomUser = $users[array_rand($users)];

    // Random Data Generation
    $titles = ['Workshop de TI', 'Palestra sobre IA', 'Encontro de Estudantes', 'Semana Acadêmica', 'Curso de PHP', 'Hackathon 2024', 'Reunião do Conselho', 'Apresentação de TCC'];
    $descriptions = [
        'Evento destinado a discutir as novas tecnologias.',
        'Um encontro para troca de conhecimentos e networking.',
        'Aprenda as melhores práticas de desenvolvimento.',
        'Venha conhecer as novidades da área.',
        'Debate sobre o futuro da educação e tecnologia.'
    ];

    $name = $titles[array_rand($titles)] . ' - ' . date('d/m');
    $description = $descriptions[array_rand($descriptions)];
    
    // Future date
    $days = rand(1, 30);
    $hours = rand(8, 18);
    $date = date('Y-m-d', strtotime("+$days days"));
    $startTime = sprintf("%02d:00", $hours);
    $endTime = sprintf("%02d:00", $hours + rand(1, 4));
    
    $startDateTime = "$date $startTime";
    $endDateTime = "$date $endTime";

    // Verify availability (simple loop until consistent)
    $attempts = 0;
    while (!$locationModel->isAvailable($randomLocation['id'], $startDateTime, $endDateTime) && $attempts < 10) {
        $days = rand(1, 60);
        $date = date('Y-m-d', strtotime("+$days days"));
        $startDateTime = "$date $startTime";
        $endDateTime = "$date $endTime";
        $attempts++;
    }

    if ($attempts >= 10) {
        die("Não foi possível encontrar um horário livre para o local {$randomLocation['name']} após 10 tentativas.");
    }

    $status = 'Aprovado';
    $isPublic = 1;
    $type = 'evento_publico';

    $eventId = $eventModel->createEvent(
        $name,
        $description,
        $startDateTime,
        $endDateTime,
        $randomLocation['id'],
        $randomCategory['id'],
        $randomUser['id'],
        $status,
        $type,
        $isPublic
    );

    if ($eventId) {
        echo "<p style='color:green'>Sucesso! Evento criado.</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> $eventId</li>";
        echo "<li><strong>Nome:</strong> $name</li>";
        echo "<li><strong>Data:</strong> $startDateTime até $endDateTime</li>";
        echo "<li><strong>Local:</strong> {$randomLocation['name']}</li>";
        echo "<li><strong>Categoria:</strong> {$randomCategory['name']}</li>";
        echo "<li><strong>Criado por:</strong> {$randomUser['name']}</li>";
        echo "</ul>";
        echo "<p><a href='/eventos/public/detail?id=$eventId'>Ver Evento</a></p>";
    } else {
        echo "<p style='color:red'>Erro ao criar evento.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Exceção: " . $e->getMessage() . "</p>";
}
?>
