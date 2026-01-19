<?php
$title = 'Calendário de Eventos';
ob_start();
?>
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-0 py-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0 fw-bold text-primary"><i class="fas fa-calendar-alt me-2"></i>Calendário de Eventos</h2>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/eventos/public/create" class="btn btn-primary rounded-pill"><i class="fas fa-plus me-2"></i>Criar Evento</a>
                <?php endif; ?>
                <a href="/eventos/" class="btn btn-outline-secondary rounded-pill ms-2"><i class="fas fa-list me-2"></i>Lista</a>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <?php
        // Navigation links
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline-primary rounded-pill px-4"><i class="fas fa-chevron-left me-2"></i>Anterior</a>
            <h3 class="fw-bold text-dark mb-0"><?= $monthNames[$month] ?> <?= $year ?></h3>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline-primary rounded-pill px-4">Próximo<i class="fas fa-chevron-right ms-2"></i></a>
        </div>
        
        <?php
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $dayOfWeek = date('w', $firstDay); // 0 (Sun) to 6 (Sat)

        // Group events by date, considering multi-day events
        $eventsByDate = [];
        foreach ($events as $event) {
            $startDate = date('Y-m-d', strtotime($event['date']));
            $endDate = $event['end_date'] ? date('Y-m-d', strtotime($event['end_date'])) : $startDate;
            $current = strtotime($startDate);
            $end = strtotime($endDate);
            while ($current <= $end) {
                $dateKey = date('Y-m-d', $current);
                $eventsByDate[$dateKey][] = $event;
                $current = strtotime('+1 day', $current);
            }
        }
        ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <thead class="table-light">
                    <tr class="text-center text-uppercase small text-muted">
                        <th style="width: 14.28%">Dom</th>
                        <th style="width: 14.28%">Seg</th>
                        <th style="width: 14.28%">Ter</th>
                        <th style="width: 14.28%">Qua</th>
                        <th style="width: 14.28%">Qui</th>
                        <th style="width: 14.28%">Sex</th>
                        <th style="width: 14.28%">Sáb</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        // Empty cells before first day
                        for ($i = 0; $i < $dayOfWeek; $i++) {
                            echo '<td class="bg-light"></td>';
                        }
                        
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $todayClass = ($currentDate == date('Y-m-d')) ? 'bg-primary-subtle border-primary' : '';
                            
                            echo '<td class="align-top p-2 ' . $todayClass . '" style="height: 120px;">';
                            echo '<div class="d-flex justify-content-between align-items-start">';
                            echo '<span class="fw-bold ' . ($currentDate == date('Y-m-d') ? 'text-primary' : 'text-secondary') . '">' . $day . '</span>';
                            // Add button with tooltip
                            echo '<a href="/eventos/public/create?date=' . $currentDate . 'T09:00" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" title="Adicionar evento"><i class="fas fa-plus-circle"></i></a>';
                            echo '</div>';
                            
                            if (isset($eventsByDate[$currentDate])) {
                                echo '<div class="mt-2 d-grid gap-1">';
                                foreach ($eventsByDate[$currentDate] as $event) {
                                    echo '<a href="/eventos/public/detail?id=' . htmlspecialchars($event['id']) . '" class="badge bg-primary text-white text-decoration-none text-truncate d-block text-start py-1 px-2" title="' . htmlspecialchars($event['name']) . '">';
                                    echo '<i class="fas fa-circle fa-xs me-1 text-white-50"></i>' . htmlspecialchars($event['name']);
                                    echo '</a>';
                                }
                                echo '</div>';
                            }
                            echo '</td>';
                            
                            // Break row
                            if (($day + $dayOfWeek) % 7 == 0 && $day != $daysInMonth) {
                                echo '</tr><tr>';
                            }
                        }
                        
                        // Correct padding logic
                        $remainingCells = (7 - ($daysInMonth + $dayOfWeek) % 7) % 7;
                        for($k=0; $k < $remainingCells; $k++){
                             echo '<td class="bg-light"></td>';
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';