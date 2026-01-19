<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Location.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

if (!$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing start_date or end_date parameters']);
    exit;
}

try {
    $locationModel = new Location();
    $locations = $locationModel->getLocationsWithAvailability($startDate, $endDate);
    
    // Return structured data for simpler JS handling
    $response = array_map(function($loc) {
        return [
            'id' => $loc['id'],
            'name' => $loc['name'],
            'is_occupied' => !empty($loc['is_occupied'])
        ];
    }, $locations);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}
