<?php

require_once __DIR__ . '/../config/database.php';

class Loan {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function requestLoan($asset_id, $user_id, $event_id, $loan_date, $return_date, $quantity = 1) {
        $this->pdo->beginTransaction();
        try {
            if (!$return_date) {
                // Return date is mandatory for range checks now. 
                // Fallback: Set to 1 hour after loan_date or end of day? 
                // Let's assume strict requirement or default 1 hour.
                $return_date = date('Y-m-d H:i:s', strtotime($loan_date . ' +1 hour'));
            }

            for ($i = 0; $i < $quantity; $i++) {
                // Find available item for this specific date range
                // Logic: Find an item_id belonging to asset_id that is NOT in the list of active loans for this range.
                // Overlap: (LoanStart < MyEnd) AND (LoanEnd > MyStart)
                $stmt = $this->pdo->prepare("
                    SELECT id FROM asset_items 
                    WHERE asset_id = ? 
                    AND id NOT IN (
                        SELECT item_id FROM loans 
                        WHERE status = 'Emprestado' 
                        AND (loan_date < ? AND COALESCE(return_date, loan_date) > ?)
                    )
                    LIMIT 1
                ");
                $stmt->execute([$asset_id, $return_date, $loan_date]);
                $item = $stmt->fetch();
                
                if (!$item) {
                    throw new Exception("Not enough available items for this asset in this time slot");
                }
                $item_id = $item['id'];

                // Insert loan
                $stmt = $this->pdo->prepare("INSERT INTO loans (item_id, user_id, event_id, loan_date, return_date, status) VALUES (?, ?, ?, ?, ?, 'Emprestado')");
                $stmt->execute([$item_id, $user_id, $event_id, $loan_date, $return_date]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function returnLoan($loan_id) {
        $this->pdo->beginTransaction();
        try {
            // Get item_id from loan
            $stmt = $this->pdo->prepare("SELECT item_id FROM loans WHERE id = ?");
            $stmt->execute([$loan_id]);
            $loan = $stmt->fetch();
            if (!$loan) {
                throw new Exception("Loan not found");
            }
            $item_id = $loan['item_id'];

            // Update loan
            $stmt = $this->pdo->prepare("UPDATE loans SET status = 'Devolvido', return_date = NOW() WHERE id = ?");
            $stmt->execute([$loan_id]);

            // Update item status
            $stmt = $this->pdo->prepare("UPDATE asset_items SET status = 'Disponível' WHERE id = ?");
            $stmt->execute([$item_id]);

            // Increment available_quantity
            $stmt = $this->pdo->prepare("UPDATE assets SET available_quantity = available_quantity + 1 WHERE id = (SELECT asset_id FROM asset_items WHERE id = ?)");
            $stmt->execute([$item_id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getLoansByUser($user_id) {
        $stmt = $this->pdo->prepare("SELECT l.*, a.name as asset_name, e.name as event_name FROM loans l JOIN asset_items ai ON l.item_id = ai.id JOIN assets a ON ai.asset_id = a.id JOIN events e ON l.event_id = e.id WHERE l.user_id = ? ORDER BY l.created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLoansByEvent($event_id) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, a.name as asset_name, ai.asset_id 
            FROM loans l 
            JOIN asset_items ai ON l.item_id = ai.id 
            JOIN assets a ON ai.asset_id = a.id 
            WHERE l.event_id = ? 
            ORDER BY a.name ASC
        ");
        $stmt->execute([$event_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelLoansForEvent($event_id) {
        // Find all active loans for this event
        $stmt = $this->pdo->prepare("SELECT id FROM loans WHERE event_id = ? AND status = 'Emprestado'");
        $stmt->execute([$event_id]);
        $loans = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($loans as $loan_id) {
            // Reusing returnLoan to ensure item status and count are updated correctly
            // We might want to set status to 'Cancelado' instead of 'Devolvido' for clarity logic, 
            // but returnLoan sets it to 'Devolvido'. 
            // If we want 'Cancelado', we should modify returnLoan or create a variant.
            // For resource availability, 'Devolvido' (Returned) is functionally correct (back in stock).
            // Let's create a specific logic here to mark as Cancelled but restore stock.
            $this->cancelLoan($loan_id);
        }
        return true;
    }

    private function cancelLoan($loan_id) {
        $this->pdo->beginTransaction();
        try {
            // Get item_id
            $stmt = $this->pdo->prepare("SELECT item_id FROM loans WHERE id = ?");
            $stmt->execute([$loan_id]);
            $loan = $stmt->fetch();
            if (!$loan) throw new Exception("Loan not found");
            $item_id = $loan['item_id'];

            // Update loan status to Cancelado
            $stmt = $this->pdo->prepare("UPDATE loans SET status = 'Cancelado', return_date = NOW() WHERE id = ?");
            $stmt->execute([$loan_id]);

            // Update item status
            $stmt = $this->pdo->prepare("UPDATE asset_items SET status = 'Disponível' WHERE id = ?");
            $stmt->execute([$item_id]);

            // Increment available_quantity
            $stmt = $this->pdo->prepare("UPDATE assets SET available_quantity = available_quantity + 1 WHERE id = (SELECT asset_id FROM asset_items WHERE id = ?)");
            $stmt->execute([$item_id]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }
    }

    public function updateEventLoans($event_id, $new_start, $new_end) {
        // Update all active loans for this event to match new event times
        // Generally, loan_date = start, return_date = end.
        // Fallback for end date check logic if null passed (controller handles defaulting, but here we expect valid dates)
        if (!$new_end) $new_end = date('Y-m-d H:i:s', strtotime($new_start . ' +1 hour'));
        
        $stmt = $this->pdo->prepare("UPDATE loans SET loan_date = ?, return_date = ? WHERE event_id = ? AND status = 'Emprestado'");
        return $stmt->execute([$new_start, $new_end, $event_id]);
    }
}