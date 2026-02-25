<?php

require_once __DIR__ . '/Mailer.php';

class Notification {

    private $pdo;
    private $mailer;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->mailer = new Mailer();
    }

    public function sendConfirmation($userId, $eventId) {
        $stmt = $this->pdo->prepare("SELECT u.email, u.name as user_name, e.name as event_name, e.date, e.location_id FROM users u JOIN events e ON e.id = ? WHERE u.id = ?");
        $stmt->execute([$eventId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            // Get location name
            $locName = 'A definir';
            if ($data['location_id']) {
                 $stmtLoc = $this->pdo->prepare("SELECT name FROM locations WHERE id = ?");
                 $stmtLoc->execute([$data['location_id']]);
                 $loc = $stmtLoc->fetch();
                 if ($loc) $locName = $loc['name'];
            }

            $subject = 'Confirmação de Solicitação de Evento - ' . $data['event_name'];
            $body = "<h2>Solicitação Recebida</h2>";
            $body .= "<p>Olá, {$data['user_name']}.</p>";
            $body .= "<p>Sua solicitação para o evento '<strong>{$data['event_name']}</strong>' foi recebida com sucesso e está pendente de aprovação.</p>";
            $body .= "<ul>";
            $body .= "<li><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($data['date'])) . "</li>";
            $body .= "<li><strong>Local:</strong> {$locName}</li>";
            $body .= "</ul>";
            $body .= "<p>Você será notificado assim que um administrador analisar seu pedido.</p>";
            
            $this->mailer->send($data['email'], $subject, $body);
        }
    }

    public function sendAdminAlert($eventId) {
        // Fetch event info
        $stmt = $this->pdo->prepare("SELECT e.*, u.name as requester_name FROM events e JOIN users u ON e.created_by = u.id WHERE e.id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            // Fetch all admins
            $stmtAdmins = $this->pdo->prepare("SELECT email FROM users WHERE role = 'admin'");
            $stmtAdmins->execute();
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

            $subject = 'Nova Solicitação de Evento Pendente';
            $body = "<h2>Nova Solicitação Pendente</h2>";
            $body .= "<p>O usuário <strong>{$event['requester_name']}</strong> solicitou um novo evento.</p>";
            $body .= "<ul>";
            $body .= "<li><strong>Evento:</strong> {$event['name']}</li>";
            $body .= "<li><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($event['date'])) . "</li>";
            $body .= "</ul>";
            $body .= "<p>Acesse o painel administrativo para aprovar ou rejeitar.</p>";
            $body .= "<p><a href='http://" . $_SERVER['HTTP_HOST'] . "/eventos/admin/events'>Acessar Painel</a></p>";

            foreach ($admins as $email) {
                $this->mailer->send($email, $subject, $body);
            }
        }
    }

    public function sendApproval($userId, $eventId, $approved) {
        $stmt = $this->pdo->prepare("SELECT u.email, u.name as user_name, e.name, e.date, e.location_id FROM users u JOIN events e ON e.id = ? WHERE u.id = ?");
        $stmt->execute([$eventId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
             // Get location name
            $locName = 'A definir';
            if ($data['location_id']) {
                 $stmtLoc = $this->pdo->prepare("SELECT name FROM locations WHERE id = ?");
                 $stmtLoc->execute([$data['location_id']]);
                 $loc = $stmtLoc->fetch();
                 if ($loc) $locName = $loc['name'];
            }
            
            $status = $approved ? 'Aprovada' : 'Rejeitada';
            $color = $approved ? 'green' : 'red';
            
            $subject = "Solicitação de Evento $status - {$data['name']}";
            $body = "<h2 style='color: $color;'>Sua solicitação foi $status</h2>";
            $body .= "<p>Olá, {$data['user_name']}.</p>";
            $body .= "<p>O status do evento '<strong>{$data['name']}</strong>' foi atualizado.</p>";
            
            if ($approved) {
                 $body .= "<p><strong>Detalhes Confirmados:</strong></p>";
                 $body .= "<ul>";
                 $body .= "<li><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($data['date'])) . "</li>";
                 $body .= "<li><strong>Local:</strong> {$locName}</li>";
                 $body .= "</ul>";
                 $body .= "<p>Verifique se houve alterações de horário ou local pelo administrador.</p>";
            } else {
                 $body .= "<p>Infelizmente sua solicitação não pôde ser atendida neste momento.</p>";
            }

            $this->mailer->send($data['email'], $subject, $body);
        }
    }

    public function sendReminder($userId, $assetId, $dueDate) {
        $stmt = $this->pdo->prepare("SELECT u.email, u.name as user_name, a.name as asset_name FROM users u JOIN assets a ON a.id = ? WHERE u.id = ?");
        $stmt->execute([$assetId, $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $subject = 'Lembrete de Devolução';
            $body = "<p>Olá, {$data['user_name']}.</p>";
            $body .= "<p>Lembramos que o item '<strong>{$data['asset_name']}</strong>' deve ser devolvido até <strong>" . date('d/m/Y H:i', strtotime($dueDate)) . "</strong>.</p>";
            
            $this->mailer->send($data['email'], $subject, $body);
        }
    }
    
    // Send email to admin when user confirms return (frontend action needed)
    public function sendReturnConfirmationToAdmin($userId, $itemsReturned, $deliveredTo) {
        $stmt = $this->pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $stmtAdmins = $this->pdo->prepare("SELECT email FROM users WHERE role = 'admin'");
            $stmtAdmins->execute();
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

            $subject = 'Devolução de Equipamentos Informada';
            $body = "<h2>Devolução Informada</h2>";
            $body .= "<p>O usuário <strong>{$user['name']}</strong> informou a devolução dos seguintes itens:</p>";
            $body .= "<ul>";
            foreach ($itemsReturned as $item) {
                $body .= "<li>$item</li>";
            }
            $body .= "</ul>";
            $body .= "<p><strong>Entregue a:</strong> $deliveredTo</p>";

            foreach ($admins as $email) {
                $this->mailer->send($email, $subject, $body);
            }
        }
    }

    public function checkOverdueLoans() {
        // Logic kept for compat, but maybe move cron logic here?
    }

    public function sendPendingReturnNotification($userId, $eventName, $pendingItems) {
        $stmt = $this->pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $subject = 'Finalização de Evento - Devolução Pendente';
            $body = "<p>Olá, {$user['name']}.</p>";
            $body .= "<p>O evento '<strong>{$eventName}</strong>' chegou ao fim.</p>";
            $body .= "<p>Por favor, realize a devolução dos seguintes itens:</p>";
            $body .= "<ul>";
            foreach ($pendingItems as $item) {
                 $body .= "<li>{$item}</li>";
            }
            $body .= "</ul>";
            
            return $this->mailer->send($user['email'], $subject, $body);
        }
        return false;
    }

    public function sendAdminRegistrationAlert($userId) {
        // Fetch new user info
        $stmt = $this->pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Fetch all admins
            $stmtAdmins = $this->pdo->prepare("SELECT email FROM users WHERE role = 'admin'");
            $stmtAdmins->execute();
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

            $subject = 'Novo cadastro pendente na UAST-UFRPE';
            $body = "<h2>Alerta de Novo Cadastro</h2>";
            $body .= "<p>Um novo usuário solicitou acesso ao sistema e aguarda aprovação administrativa.</p>";
            $body .= "<ul>";
            $body .= "<li><strong>Nome:</strong> {$user['name']}</li>";
            $body .= "<li><strong>E-mail:</strong> {$user['email']}</li>";
            $body .= "</ul>";
            $body .= "<p><a href='http://" . $_SERVER['HTTP_HOST'] . "/eventos/admin/users'>Acessar Controle de Usuários</a></p>";

            foreach ($admins as $email) {
                $this->mailer->send($email, $subject, $body);
            }
        }
    }
}
