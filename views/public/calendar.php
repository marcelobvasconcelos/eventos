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
                            
                            // Calculate day summary for popover
                            $dayContent = '';
                            $hasEvents = isset($eventsByDate[$currentDate]);
                            if ($hasEvents) {
                                $dayContent .= '<ul class="list-unstyled mb-0 small">';
                                $count = 0;
                                foreach ($eventsByDate[$currentDate] as $ev) {
                                    if ($count >= 5) {
                                        $dayContent .= '<li><em>e mais ' . (count($eventsByDate[$currentDate]) - 5) . '...</em></li>';
                                        break;
                                    }
                                    $evTime = date('H:i', strtotime($ev['date']));
                                    $evName = htmlspecialchars($ev['name']);
                                    // Privacy check for popover content as well
                                    $evIsPublic = $ev['is_public'] ?? 1;
                                    $evIsAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                                    $evIsOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($ev['created_by'] ?? 0);
                                    if (!$evIsPublic && !$evIsAdmin && !$evIsOwner) {
                                        $evName = "Agendamento Privado";
                                    }
                                    $dayContent .= "<li><strong>{$evTime}</strong> - {$evName}</li>";
                                    $count++;
                                }
                                $dayContent .= '</ul>';
                            } else {
                                $dayContent = 'Nenhum evento agendado.';
                            }
                            
                            echo '<td class="align-top p-2 ' . $todayClass . '" style="height: 120px; cursor: pointer;" 
                                      onclick="window.location.href=\'/eventos/public/day?date=' . $currentDate . '\'"
                                      data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="top" 
                                      title="Eventos de ' . date('d/m', strtotime($currentDate)) . '" 
                                      data-bs-content="' . htmlspecialchars($dayContent) . '">';
                                      
                            echo '<div class="d-flex justify-content-between align-items-start">';
                            echo '<span class="fw-bold ' . ($currentDate == date('Y-m-d') ? 'text-primary' : 'text-secondary') . '">' . $day . '</span>';
                            // Add button with tooltip, clicking it should NOT trigger the row click
                            echo '<a href="/eventos/public/create?date=' . $currentDate . 'T09:00" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" title="Adicionar evento" onclick="event.stopPropagation();"><i class="fas fa-plus-circle" style="font-size: 1.5em;"></i></a>';
                            echo '</div>';
                            
                            if (isset($eventsByDate[$currentDate])) {
                                echo '<div class="mt-2 d-grid gap-1">';
                                foreach ($eventsByDate[$currentDate] as $event) {
                                    $eventName = $event['name'];
                                    $eventDesc = $event['description'] ?? '';
                                    $isPublic = $event['is_public'] ?? 1;
                                    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                                    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($event['created_by'] ?? 0);

                                    if (!$isPublic && !$isAdmin && !$isOwner) {
                                        $eventName = "Agendamento Privado";
                                        $eventDesc = "Detalhes restritos.";
                                    }
                                    
                                    // Popover content for event
                                    $startTs = strtotime($event['date']);
                                    $endTs = $event['end_date'] ? strtotime($event['end_date']) : $startTs + 3600;
                                    
                                    if (date('Y-m-d', $startTs) !== date('Y-m-d', $endTs)) {
                                        $endTime = date('d/m H:i', $endTs);
                                    } else {
                                        $endTime = date('H:i', $endTs);
                                    }
                                    
                                    $imgPop = !empty($event['image_path']) ? $event['image_path'] : '/eventos/lib/banner.jpeg';
                                    
                                    $eventPopover = "<div class='mb-2'>";
                                    $eventPopover .= "<img src='" . htmlspecialchars($imgPop) . "' class='img-fluid rounded shadow-sm' style='width: 100%; height: 100px; object-fit: cover;'>";
                                    $eventPopover .= "</div>";
                                    $eventPopover .= "<strong>Hora:</strong> " . date('H:i', $startTs) . " - " . $endTime . "<br>";
                                    $eventPopover .= "<strong>Local:</strong> " . htmlspecialchars($event['location_name'] ?? 'N/A') . "<br>";
                                    $eventPopover .= "<small>" . htmlspecialchars(substr($eventDesc, 0, 100)) . (strlen($eventDesc)>100?'...':'') . "</small>";

                                    // Color Logic based on Location (Same palette as day_timeline)
                                    $colors = [
                                        ['bg' => '#0d6efd', 'text' => '#ffffff'], // Blue
                                        ['bg' => '#198754', 'text' => '#ffffff'], // Green
                                        ['bg' => '#dc3545', 'text' => '#ffffff'], // Red
                                        ['bg' => '#ffc107', 'text' => '#000000'], // Yellow
                                        ['bg' => '#0dcaf0', 'text' => '#000000'], // Cyan
                                        ['bg' => '#6f42c1', 'text' => '#ffffff'], // Purple
                                        ['bg' => '#fd7e14', 'text' => '#ffffff'], // Orange
                                        ['bg' => '#20c997', 'text' => '#ffffff'], // Teal
                                        ['bg' => '#d63384', 'text' => '#ffffff'], // Pink
                                        ['bg' => '#6610f2', 'text' => '#ffffff'], // Indigo
                                    ];
                                    
                                    $locId = $event['location_id'] ?? 0;
                                    $colorIndex = $locId % count($colors);
                                    $style = $colors[$colorIndex];
                                    
                                    // Private events override
                                    if (!$isPublic && !$isAdmin && !$isOwner) {
                                        $style = ['bg' => '#6c757d', 'text' => '#ffffff'];
                                    }
                                    
                                    $extraStyle = '';
                                    if (($event['status']??'') === 'Cancelado') {
                                        $style = ['bg' => '#dc3545', 'text' => '#ffffff'];
                                        $extraStyle = 'text-decoration: line-through; opacity: 0.8;';
                                    }

                                    // Removed title attribute to avoid double tooltip, using data-bs-title or just content
                                    echo '<a href="/eventos/public/detail?id=' . htmlspecialchars($event['id']) . '" 
                                             class="badge text-decoration-none text-truncate d-block text-start py-1 px-2" 
                                             style="background-color: ' . $style['bg'] . '; color: ' . $style['text'] . '; ' . $extraStyle . '"
                                             onclick="event.stopPropagation();"
                                             data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="right"
                                             title="' . htmlspecialchars($eventName) . '"
                                             data-bs-content="' . htmlspecialchars($eventPopover) . '">';
                                    echo '<i class="fas fa-circle fa-xs me-1" style="opacity: 0.7;"></i>' . htmlspecialchars($eventName);
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';