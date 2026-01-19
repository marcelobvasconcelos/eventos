<?php
require_once 'config/database.php';

try {
    // Clear existing data
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE notifications");
    $pdo->exec("TRUNCATE TABLE event_requests");
    $pdo->exec("TRUNCATE TABLE loans");
    $pdo->exec("TRUNCATE TABLE asset_items");
    $pdo->exec("TRUNCATE TABLE assets");
    $pdo->exec("TRUNCATE TABLE events");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("TRUNCATE TABLE locations");
    $pdo->exec("TRUNCATE TABLE users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Insert sample users
    $pdo->exec("INSERT INTO users (name, email, password, role, created_at) VALUES
        ('Admin User', 'admin@example.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin', NOW()),
        ('John Doe', 'john@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'user', NOW()),
        ('Jane Smith', 'jane@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'user', NOW()),
        ('Bob Johnson', 'bob@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'user', NOW())");

    // Insert sample categories
    $pdo->exec("INSERT INTO categories (name, description, created_at) VALUES
        ('Electronics', 'Electronic equipment and devices', NOW()),
        ('Audio', 'Audio equipment and accessories', NOW()),
        ('Furniture', 'Furniture and seating', NOW())");

    // Insert sample locations
    $pdo->exec("INSERT INTO locations (name, address, capacity, created_at) VALUES
        ('Convention Center', '123 Main St, City Center', 1000, NOW()),
        ('City Park', '456 Park Ave, Downtown', 5000, NOW()),
        ('Tech Hub', '789 Tech Blvd, Innovation District', 200, NOW()),
        ('Storage Room A', 'Storage Building A, Warehouse District', NULL, NOW()),
        ('Storage Room B', 'Storage Building B, Warehouse District', NULL, NOW()),
        ('Warehouse', 'Main Warehouse, Industrial Zone', NULL, NOW()),
        ('IT Room', 'IT Building, Tech Campus', NULL, NOW())");

    // Insert sample events
    $pdo->exec("INSERT INTO events (name, description, date, end_date, location, max_participants, created_by, created_at) VALUES
        ('Tech Conference 2026', 'Annual technology conference', '2026-06-15 09:00:00', '2026-06-17 18:00:00', 'Convention Center', 500, 1, NOW()),
        ('Music Festival', 'Summer music festival', '2026-07-20 18:00:00', '2026-07-22 23:00:00', 'City Park', 2000, 2, NOW()),
        ('Workshop on AI', 'Hands-on AI workshop', '2026-08-10 10:00:00', '2026-08-10 16:00:00', 'Tech Hub', 50, 3, NOW())");

    // Insert sample assets
    $pdo->exec("INSERT INTO assets (name, description, category, quantity, available_quantity, location, created_by, created_at) VALUES
        ('Projector', 'High-definition projector', 'Electronics', 5, 5, 'Storage Room A', 1, NOW()),
        ('Microphones', 'Wireless microphones', 'Audio', 10, 10, 'Storage Room B', 1, NOW()),
        ('Chairs', 'Folding chairs', 'Furniture', 100, 100, 'Warehouse', 1, NOW()),
        ('Laptops', 'Dell laptops for events', 'Electronics', 20, 20, 'IT Room', 1, NOW())");

    // Insert sample loans
    $pdo->exec("INSERT INTO loans (asset_id, user_id, quantity, loan_date, return_date, status, created_at) VALUES
        (1, 2, 1, '2026-01-15', '2026-01-20', 'active', NOW()),
        (2, 3, 2, '2026-01-20', '2026-01-25', 'active', NOW()),
        (3, 4, 10, '2026-01-10', '2026-01-12', 'returned', NOW())");

    // Insert sample event requests
    $pdo->exec("INSERT INTO event_requests (event_id, user_id, status, request_date, notes, created_at) VALUES
        (1, 2, 'approved', '2024-05-01', 'Interested in tech talks', NOW()),
        (2, 3, 'pending', '2024-06-01', 'Bringing friends', NOW()),
        (3, 4, 'approved', '2024-07-15', 'Professional development', NOW())");

    // Insert sample notifications
    $pdo->exec("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES
        (2, 'Your event request for Tech Conference 2024 has been approved.', 'event_request', 0, NOW()),
        (3, 'Your loan for Microphones has been approved.', 'loan', 0, NOW()),
        (4, 'Reminder: Return chairs by 2024-08-11.', 'reminder', 0, NOW())");

    echo "Sample data inserted successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>