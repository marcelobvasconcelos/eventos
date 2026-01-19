<?php
require_once 'config/database.php';

// Connect to database
$db = $pdo;

echo "Populating database...\n";

$db->exec("SET FOREIGN_KEY_CHECKS = 0;");

$db->exec("TRUNCATE TABLE locations");
$db->exec("TRUNCATE TABLE categories");
$db->exec("TRUNCATE TABLE users");
$db->exec("TRUNCATE TABLE events");
$db->exec("TRUNCATE TABLE assets");
$db->exec("TRUNCATE TABLE asset_items");
$db->exec("TRUNCATE TABLE loans");
$db->exec("TRUNCATE TABLE event_requests");
$db->exec("TRUNCATE TABLE notifications");

// Insert sample locations
$locations = [
    ['name' => 'Main Auditorium', 'description' => 'Large auditorium for conferences', 'capacity' => 200],
    ['name' => 'Outdoor Stage', 'description' => 'Open-air stage for events', 'capacity' => 500],
    ['name' => 'Meeting Room A', 'description' => 'Small meeting room', 'capacity' => 20],
];

foreach ($locations as $location) {
    $stmt = $db->prepare("INSERT IGNORE INTO locations (name, description, capacity) VALUES (?, ?, ?)");
    $stmt->execute([$location['name'], $location['description'], $location['capacity']]);
}

echo "Locations inserted.\n";

$db->exec("TRUNCATE TABLE categories");

// Insert sample categories
$categories = [
    ['name' => 'Technology', 'description' => 'Tech-related events'],
    ['name' => 'Music', 'description' => 'Music and entertainment events'],
    ['name' => 'Education', 'description' => 'Educational workshops and seminars'],
];

foreach ($categories as $category) {
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$category['name'], $category['description']]);
}

echo "Categories inserted.\n";

// Insert sample users
$users = [
    ['name' => 'Admin User', 'email' => 'admin@example.com', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'role' => 'admin'],
    ['name' => 'John Doe', 'email' => 'john@example.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'user'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'user'],
    ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'user'],
];

foreach ($users as $user) {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['name'], $user['email'], $user['password'], $user['role']]);
}

echo "Users inserted.\n";

// Insert sample events
$events = [
    ['name' => 'Tech Conference 2024', 'description' => 'Annual technology conference', 'date' => '2024-06-15 09:00:00', 'end_date' => '2024-06-15 17:00:00', 'location_id' => 1, 'category_id' => 1, 'status' => 'Aprovado', 'created_by' => 1],
    ['name' => 'Music Festival', 'description' => 'Summer music festival', 'date' => '2024-07-20 18:00:00', 'end_date' => '2024-07-21 02:00:00', 'location_id' => 2, 'category_id' => 2, 'status' => 'Aprovado', 'created_by' => 2],
    ['name' => 'Workshop on AI', 'description' => 'Hands-on AI workshop', 'date' => '2024-08-10 10:00:00', 'end_date' => '2024-08-10 16:00:00', 'location_id' => 1, 'category_id' => 1, 'status' => 'Pendente', 'created_by' => 1],
];

foreach ($events as $event) {
    $stmt = $db->prepare("INSERT INTO events (name, description, date, end_date, location_id, category_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$event['name'], $event['description'], $event['date'], $event['end_date'], $event['location_id'], $event['category_id'], $event['status'], $event['created_by']]);
}

echo "Events inserted.\n";

// Insert sample assets
$assets = [
    ['name' => 'Projector', 'description' => 'High-definition projector', 'quantity' => 5, 'available_quantity' => 5],
    ['name' => 'Microphone', 'description' => 'Wireless microphone', 'quantity' => 10, 'available_quantity' => 10],
    ['name' => 'Chairs', 'description' => 'Foldable chairs', 'quantity' => 100, 'available_quantity' => 100],
];

foreach ($assets as $asset) {
    $stmt = $db->prepare("INSERT INTO assets (name, description, quantity, available_quantity) VALUES (?, ?, ?, ?)");
    $stmt->execute([$asset['name'], $asset['description'], $asset['quantity'], $asset['available_quantity']]);
}

echo "Assets inserted.\n";

// Insert sample asset items
$assetItems = [
    ['asset_id' => 1, 'identification' => 'PROJ-001', 'status' => 'Disponível'],
    ['asset_id' => 1, 'identification' => 'PROJ-002', 'status' => 'Disponível'],
    ['asset_id' => 2, 'identification' => 'MIC-001', 'status' => 'Disponível'],
    ['asset_id' => 2, 'identification' => 'MIC-002', 'status' => 'Disponível'],
    ['asset_id' => 3, 'identification' => 'CHAIR-001', 'status' => 'Disponível'],
];

foreach ($assetItems as $item) {
    $stmt = $db->prepare("INSERT INTO asset_items (asset_id, identification, status) VALUES (?, ?, ?)");
    $stmt->execute([$item['asset_id'], $item['identification'], $item['status']]);
}

echo "Asset items inserted.\n";

// Insert sample loans
$loans = [
    ['item_id' => 1, 'user_id' => 2, 'event_id' => 1, 'loan_date' => '2024-06-14 10:00:00', 'return_date' => null, 'status' => 'Emprestado'],
    ['item_id' => 3, 'user_id' => 3, 'event_id' => 2, 'loan_date' => '2024-07-19 15:00:00', 'return_date' => null, 'status' => 'Emprestado'],
];

foreach ($loans as $loan) {
    $stmt = $db->prepare("INSERT INTO loans (item_id, user_id, event_id, loan_date, return_date, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$loan['item_id'], $loan['user_id'], $loan['event_id'], $loan['loan_date'], $loan['return_date'], $loan['status']]);
}

echo "Loans inserted.\n";

// Insert sample event requests
$requests = [
    ['user_id' => 2, 'event_id' => 1, 'status' => 'Aprovado', 'approved_by' => 1],
    ['user_id' => 3, 'event_id' => 2, 'status' => 'Pendente', 'approved_by' => null],
];

foreach ($requests as $request) {
    $stmt = $db->prepare("INSERT INTO event_requests (user_id, event_id, status, approved_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$request['user_id'], $request['event_id'], $request['status'], $request['approved_by']]);
}

echo "Event requests inserted.\n";

// Insert sample notifications
$notifications = [
    ['user_id' => 2, 'message' => 'Your request for Tech Conference 2024 has been approved.'],
    ['user_id' => 3, 'message' => 'Your request for Music Festival is pending approval.'],
];

foreach ($notifications as $notification) {
    $stmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->execute([$notification['user_id'], $notification['message']]);
}

echo "Notifications inserted.\n";

echo "Database populated successfully!\n";