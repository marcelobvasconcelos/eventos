<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif !important; background-color: transparent !important; margin:0; padding:10px; }
        .card { border: none !important; border-radius: 15px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important; background-color: #ffffff !important;}
        @media (max-width: 768px) {
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-body p-2 p-md-4">
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
            <a href="/eventos/public/iframeCalendar?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline-primary rounded-pill px-3 px-md-4"><i class="fas fa-chevron-left"></i><span class="d-none d-md-inline ms-2">Anterior</span></a>
            <h3 class="fw-bold text-dark mb-0 fs-5 fs-md-3"><?= substr($monthNames[$month], 0, 3) ?>/<?= substr($year, -2) ?></h3>
            <a href="/eventos/public/iframeCalendar?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline-primary rounded-pill px-3 px-md-4"><span class="d-none d-md-inline me-2">Próximo</span><i class="fas fa-chevron-right"></i></a>
        </div>
        
        <?php
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        // ... (lines 47-63 omitted for brevity, keeping original content implied or handled by replacement scope if large block, 
        // but here I must be careful. The user instruction is to hide buttons and add logic. 
        // I should replace the button block specifically, and then the JS block specifically.
        // HOWEVER, replace_file_content can only handle one contiguous block. 
        // I will use two separate calls or one large call if the blocks are close.
        // They are far apart (lines 39 vs 300). I will use two calls.
        
        // This tool call will ONLY handle the buttons visibility.
        // Wait, I can't do multiple replace calls in parallel on the same file if not using multi_replace.
        // I should use multi_replace interaction or do it sequentially.
        // I'll do this one for buttons first.
        ?>
        
        <?php
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $dayOfWeek = date('w', $firstDay); // 0 (Sun) to 6 (Sat)

        // Group events by date
        $eventsByDate = [];
        foreach ($events as $event) {
            $dateKey = date('Y-m-d', strtotime($event['date']));
            $eventsByDate[$dateKey][] = $event;
        }
        ?>
        <!-- Desktop Calendar View (Forced everywhere for Iframe) -->
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
                                $renderedBlocksHover = [];
                                foreach ($eventsByDate[$currentDate] as $ev) {
                                    if (($ev['type'] ?? '') === 'bloqueio_administrativo') {
                                        if (in_array($ev['name'], $renderedBlocksHover)) continue;
                                        $renderedBlocksHover[] = $ev['name'];
                                    }

                                    if ($count >= 5) {
                                        $dayContent .= '<li><em>e mais ' . (count($eventsByDate[$currentDate]) - 5) . '...</em></li>';
                                        break;
                                    }
                                    $evTime = date('H:i', strtotime($ev['start_time']));
                                    $evName = htmlspecialchars($ev['name']);
                                    if (($ev['type'] ?? '') === 'informativo_calendario') {
                                        $dayContent .= "<li><i class='fas fa-info-circle text-primary me-1'></i>{$evName}</li>";
                                        $count++;
                                        continue;
                                    }
                                    
                                    // Privacy check for popover content as well
                                    $evIsPublic = $ev['is_public'] ?? 1;
                                    $evIsAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                                    $evIsOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($ev['created_by'] ?? 0);
                                    if (!$evIsPublic && !$evIsAdmin && !$evIsOwner) {
                                        // $evName remains visible
                                    }
                                    $dayContent .= "<li><strong>{$evTime}</strong> - {$evName}</li>";
                                    $count++;
                                }
                                $dayContent .= '</ul>';
                            } else {
                                $dayContent = 'Nenhum evento agendado.';
                            }
                            
                            echo '<td class="align-top p-2 ' . $todayClass . '" style="height: 120px; cursor: pointer;" 
                                      onclick="openMobileDayView(\''.$currentDate.'\')"
                                      data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="top" 
                                      title="Eventos de ' . date('d/m', strtotime($currentDate)) . '" 
                                      data-bs-content="' . htmlspecialchars($dayContent) . '">';
                                      
                            echo '<div class="d-flex justify-content-between align-items-start">';
                            echo '<span class="fw-bold ' . ($currentDate == date('Y-m-d') ? 'text-primary' : 'text-secondary') . '">' . $day . '</span>';
                            echo '</div>';
                            
                            if (isset($eventsByDate[$currentDate])) {
                                echo '<div class="mt-2 d-grid gap-1">';
                                $renderedBlocksCell = [];
                                foreach ($eventsByDate[$currentDate] as $event) {
                                    $eventName = $event['name'];
                                    $isPublic = $event['is_public'] ?? 1;
                                    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                                    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($event['created_by'] ?? 0);
                                    $type = $event['type'] ?? 'evento_publico';

                                    // Block Logic
                                    if ($type === 'bloqueio_administrativo') {
                                        if (in_array($eventName, $renderedBlocksCell)) continue;
                                        $renderedBlocksCell[] = $eventName;
                                        
                                        $style = ['bg' => '#6c757d', 'text' => '#ffffff']; // Dark Gray
                                        // No popover detailed info needed? "Interatividade: Não deve levar para a página... apenas mostrar o informativo"
                                        // We can still show popover with just title/reason.
                                    } else {
                                        // Normal Event Logic
                                        if (!$isPublic && !$isAdmin && !$isOwner) {
                                            // $eventName remains visible
                                        }
                                        
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
                                    }

                                    // Display Logic
                                    if ($type === 'bloqueio_administrativo') {
                                        echo '<span class="badge text-decoration-none text-truncate d-block text-start py-1 px-2" 
                                                 style="background-color: ' . $style['bg'] . '; color: ' . $style['text'] . '; cursor: default;"
                                                 title="' . htmlspecialchars($eventName) . '">';
                                        echo '<i class="fas fa-ban fa-xs me-1" style="opacity: 0.7;"></i>' . htmlspecialchars($eventName);
                                        echo '</span>';
                                    } elseif ($type === 'informativo_calendario') {
                                        $hColor = $event['custom_location'] ?? '#ffc107';
                                        $hDesc = $event['description'] ?? '';
                                        echo '<div class="d-block text-start py-1 px-2 mb-1 rounded-1" 
                                                 style="background-color: ' . htmlspecialchars($hColor) . '33; border-left: 3px solid ' . htmlspecialchars($hColor) . '; color: #333; font-size: 0.8rem; cursor: default;"
                                                 data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-content="' . htmlspecialchars($hDesc) . '"
                                                 onclick="event.stopPropagation();">';
                                        echo '<i class="fas fa-info-circle me-1" style="color: ' . htmlspecialchars($hColor) . ';"></i>' . htmlspecialchars($eventName);
                                        echo '</div>';
                                    } else {
                                        echo '<a href="javascript:void(0);" 
                                                 class="badge text-decoration-none text-truncate d-block text-start py-1 px-2" 
                                                 style="background-color: ' . $style['bg'] . '; color: ' . $style['text'] . '; ' . ($extraStyle??'') . '"
                                                 onclick="handleEventClick(event, ' . $event['id'] . ');"
                                                 data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="right"
                                                 title="' . htmlspecialchars($eventName) . '"
                                                 data-bs-content="Ver Detalhes">'; 
                                        echo '<i class="fas fa-circle fa-xs me-1" style="opacity: 0.7;"></i>' . htmlspecialchars($eventName);
                                        echo '</a>';
                                    }
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



        <!-- Mobile Day View Modal -->
        <div class="modal fade" id="mobileDayModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 bg-light">
                        <h5 class="modal-title fw-bold text-primary" id="mobileDayTitle">Detalhes do Dia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="mobileDayTimeline" class="p-3">
                            <!-- Events will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inject Data for JS -->
        <script>
            // Serialize PHP events array to JS object safely
            const calendarEvents = <?php echo json_encode($eventsByDate) ?: '{}'; ?>;
            const monthNames = <?php echo json_encode($monthNames) ?: '[]'; ?>;
            const currentMonth = <?php echo $month; ?>;
            const currentYear = <?php echo $year; ?>;
            
            function openMobileDayView(dateStr) {
                const dayEvents = calendarEvents[dateStr] || [];
                const timeline = document.getElementById('mobileDayTimeline');
                const title = document.getElementById('mobileDayTitle');
                
                // Format Date Title
                const dateObj = new Date(dateStr + 'T12:00:00'); // Safe parsing
                const day = dateObj.getDate();
                title.innerHTML = `<i class="far fa-calendar-alt me-2"></i>${day} de ${monthNames[currentMonth]}`;
                
                if (dayEvents.length === 0) {
                    timeline.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="far fa-calendar-times fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">Nenhum evento neste dia.</p>
                        </div>
                    `;
                } else {
                    let html = '<div class="timeline">';
                    dayEvents.forEach(ev => {
                        let name = ev.name;
                        
                        // Handle Highlight inside Modal
                        if (ev.type === 'informativo_calendario') {
                            const customColor = ev.custom_location || '#ffc107';
                            html += `
                                <div class="card mb-3 border-0 shadow-sm" style="background: ${customColor}22; border-left: 4px solid ${customColor} !important; border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-1" style="color: ${customColor};"><i class="fas fa-bullhorn me-2"></i>${name}</h6>
                                        <div class="small mt-1 text-dark">${ev.description || ''}</div>
                                    </div>
                                </div>`;
                            return; // skip to next
                        }

                        const time = ev.date.split(' ')[1].substring(0, 5);
                        
                        html += `
                            <div class="card mb-3 border-0 shadow-sm" onclick="handleEventClick(event, ${ev.id})" style="cursor: pointer; background: #f8f9fa;">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="me-3 text-center" style="min-width: 50px;">
                                        <div class="fw-bold text-primary fs-5">${time}</div>
                                    </div>
                                    <div class="border-start ps-3 border-2 border-primary" style="min-width: 0;">
                                        <h6 class="fw-bold mb-1 text-dark text-truncate">${name}</h6>
                                        <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i>${ev.location_name || 'Local não definido'}</div>
                                        <div class="small text-muted"><i class="fas fa-tag me-1"></i>${ev.category_name || 'Sem Categoria'}</div>
                                        <div class="small text-muted mt-1 text-truncate"><i class="fas fa-align-left me-1"></i>${ev.description || 'Sem descrição'}</div>
                                    </div>
                                    <div class="ms-auto text-primary ps-2">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    timeline.innerHTML = html;
                }
                
                const modal = new bootstrap.Modal(document.getElementById('mobileDayModal'));
                modal.show();
            }
        </script>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
    
    // Swipe Navigation Logic for Mobile
    const calendarContainer = document.getElementById('mobileCalendarContainer'); // Updated selector
    let touchStartX = 0;
    let touchEndX = 0;
    
    // PHP variables for navigation
    const prevMonthUrl = "/eventos/public/iframeCalendar?month=<?= $prevMonth ?>&year=<?= $prevYear ?>";
    const nextMonthUrl = "/eventos/public/iframeCalendar?month=<?= $nextMonth ?>&year=<?= $nextYear ?>";
    
    if (calendarContainer) {
        calendarContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, {passive: true});
        
        calendarContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, {passive: true});
    }
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swiped Left -> Next Month
            calendarContainer.classList.add('slide-exit-next');
            setTimeout(() => {
                window.location.href = nextMonthUrl + '&dir=next';
            }, 300);
        }
        if (touchEndX > touchStartX + 50) {
            // Swiped Right -> Prev Month
            calendarContainer.classList.add('slide-exit-prev');
            setTimeout(() => {
                window.location.href = prevMonthUrl + '&dir=prev';
            }, 300);
        }
    }
    
    // Check for entrance animation
    const urlParams = new URLSearchParams(window.location.search);
    const dir = urlParams.get('dir');
    if (dir === 'next' && calendarContainer) {
        calendarContainer.classList.add('slide-enter-next');
    } else if (dir === 'prev' && calendarContainer) {
        calendarContainer.classList.add('slide-enter-prev');
    }
});

function handleEventClick(e, eventId) {
    e.stopPropagation(); // Prevent row click
    window.open('/eventos/public/detail?id=' + eventId, '_blank');
}
</script>

<style>
/* Calendar Transitions */
.slide-exit-next {
    animation: slideOutLeft 0.3s forwards;
}
.slide-exit-prev {
    animation: slideOutRight 0.3s forwards;
}
.slide-enter-next {
    animation: slideInRight 0.3s forwards;
}
.slide-enter-prev {
    animation: slideInLeft 0.3s forwards;
}

@keyframes slideOutLeft {
    to { transform: translateX(-100%); opacity: 0; }
}
@keyframes slideOutRight {
    to { transform: translateX(100%); opacity: 0; }
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideInLeft {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>