<?php
// cron_check_returns.php
// Run this script periodically (e.g., hourly or daily) via system cron

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/lib/Notification.php';
require_once __DIR__ . '/models/Event.php';
require_once __DIR__ . '/models/Loan.php';

global $pdo;
$notification = new Notification($pdo);
$loanModel = new Loan();

// 1. Find events that have ended recently (e.g., in the last hour/day) 
// and logic to avoid duplicate emails (maybe check if emails already sent? or just check active loans).
// Simple logic: Check all loans with status 'Emprestado' where event end_date < NOW().
// Group by User and Event to send consolidated emails.

echo "Checking for overdue returns...\n";

$stmt = $pdo->prepare("
    SELECT l.id, l.user_id, l.event_id, e.name as event_name, e.date, e.start_time, e.end_time, ai.identification as item_name, a.name as asset_name
    FROM loans l
    JOIN events e ON l.event_id = e.id
    JOIN asset_items ai ON l.item_id = ai.id
    JOIN assets a ON ai.asset_id = a.id
    WHERE l.status = 'Emprestado' 
    AND CONCAT(e.date, ' ', e.end_time) < NOW()
    AND CONCAT(e.date, ' ', e.end_time) > DATE_SUB(NOW(), INTERVAL 24 HOUR)
");

// Ideally we need a 'notification_sent' flag in loans or separate table, 
// for now, we rely on the interval to only send for events that ended in the last 24h.
// Better approach: One email per event end.

$stmt->execute();
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

$notifications = [];

foreach ($loans as $loan) {
    $key = $loan['user_id'] . '_' . $loan['event_id'];
    if (!isset($notifications[$key])) {
        $notifications[$key] = [
            'user_id' => $loan['user_id'],
            'event_name' => $loan['event_name'],
            'items' => []
        ];
    }
    $notifications[$key]['items'][] = $loan['asset_name'] . ' - ' . $loan['item_name'];
}

foreach ($notifications as $notif) {
    echo "Sending notification to User ID {$notif['user_id']} for Event '{$notif['event_name']}'...\n";
    if ($notification->sendPendingReturnNotification($notif['user_id'], $notif['event_name'], $notif['items'])) {
        echo "Sent.\n";
    } else {
        echo "Failed.\n";
    }
}

echo "Done.\n";
