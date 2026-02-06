<?php

require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/PendingItem.php';
require_once __DIR__ . '/Notification.php';

class PendingManager {

    private $pdo;
    private $eventModel;
    private $loanModel;
    private $pendingItemModel;
    private $notification;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->eventModel = new Event();
        $this->loanModel = new Loan();
        $this->pendingItemModel = new PendingItem();
        $this->notification = new Notification($pdo);
    }

    public function checkAndGenerate() {
        // Find events that ended in the last hour/day and haven't been processed yet.
        // Or simply find finished events (status could be updated or just time passed)
        // Since we don't have a 'processed' flag in events table, we check existence of pending items
        // or we rely on a status change. Ideally we should add a 'post_event_processed' flag to events table.
        // But for this task, I'll rely on time + non-existence of pending items.
        
        // Let's get "Active" events from Event Model actually returns ongoing.
        // We need "Just Finished" events.
        
        // Events that finished in the past, but we haven't generated pending items for them.
        // We check "pendencias" table for this event_id. If count is 0, we might need to generate.
        // Be careful: if an event had NO items to return, we might keep checking it.
        // So checking if 'date' < NOW is a start.
        
        // Query: Events where End Date < NOW and Status = 'Aprovado' (or 'Concluido' if manually set)
        
        $sql = "
            SELECT * FROM events 
            WHERE status IN ('Aprovado', 'Concluido') 
            AND (end_date < NOW() OR (end_date IS NULL AND date < DATE_SUB(NOW(), INTERVAL 4 HOUR)))
        ";
        // Retrieve waiting for optimization limit
        // Optimization: In a real system, we'd add a flag 'pending_items_generated' to table.
        // For now, I'll fetch and check one by one (lazy load, might be slow if many old events). 
        // Optimization: Limit to last 7 days to avoid processing ancient history every time.
        $sql .= " AND date > DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $finishedEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($finishedEvents as $event) {
            $eventId = $event['id'];
            
            // Check if we already processed this event (simply check if any pending item exists for it)
            // What if it has no pending items? We will keep checking it. 
            // Ideally we insert a "dummy" or have a flag. To avoid re-checking, let's assume if it's > 24h old we stop?
            // Actually, let's check if we generated Key pending item.
            
            if ($this->pendingItemModel->existsForEvent($eventId, 'key')) {
                continue; // Already processed
            }
            // Double check loan items too? No, if key exists, we assume we processed.

            $userId = $event['created_by'];

            if (empty($userId)) {
                continue; 
            }
            $itemsGenerated = [];

            // 1. Generate Key Return Pending Item
            // Every event needs to return keys (assumption based on prompt "Devolução da Chave do Local")
            $this->pendingItemModel->create($eventId, $userId, 'key', 'Devolução da Chave do Local (' . ($event['location_name'] ?? 'Local') . ')');
            $itemsGenerated[] = 'Chave do Local';

            // 2. Generate Asset Return Pending Items
            $loans = $this->loanModel->getLoansByEvent($eventId);
            foreach ($loans as $loan) {
                if ($loan['status'] == 'Emprestado') {
                     // Create pending item for this asset
                     $description = 'Devolução de ' . $loan['asset_name'];
                     // Use category_name as item_type, default to 'Equipamento' if null or empty
                     $itemType = (!empty($loan['category_name'])) ? $loan['category_name'] : 'Equipamento';
                     
                     $this->pendingItemModel->create($eventId, $userId, $itemType, $description);
                     $itemsGenerated[] = $loan['asset_name'];
                }
            }
            
            // Send Notification
            if (!empty($itemsGenerated)) {
                // Email notification disabled as per user request
                // $this->notification->sendPendingReturnNotification($userId, $event['name'], $itemsGenerated);
            }
            
            // Optionally update event status to 'Concluido' if it was 'Aprovado'
            if ($event['status'] == 'Aprovado') {
                 // $this->eventModel->updateStatus($eventId, 'Concluido'); 
                 // Maybe keep it Aprovado until everything is returned?
                 // Prompt says "Status sugeridos: Pendente, Informado pelo Usuário, Concluído" for PENDENCIES.
                 // For Event, 'Concluido' seems appropriate if time passed.
            }
        }
    }
}
