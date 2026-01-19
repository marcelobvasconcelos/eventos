<?php
$title = 'Agenda do Dia - ' . date('d/m/Y', strtotime($date));
ob_start();

$prevDay = date('Y-m-d', strtotime($date . ' -1 day'));
$nextDay = date('Y-m-d', strtotime($date . ' +1 day'));

// Helper validation for private events
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'] ?? 0;
?>

<div class="row">
    <div class="col-12">
        <!-- Navigation Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/eventos/public/day?date=<?php echo $prevDay; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-chevron-left"></i>
            </a>
            
            <div class="text-center">
                <h3 class="fw-bold text-primary mb-0"><?php echo date('d/m/Y', strtotime($date)); ?></h3>
                <?php
                $days = [
                    'Sunday' => 'Domingo', 'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira', 
                    'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira', 'Friday' => 'Sexta-feira', 
                    'Saturday' => 'Sábado'
                ];
                $dayName = date('l', strtotime($date));
                ?>
                <span class="text-muted text-uppercase small"><?php echo $days[$dayName]; ?></span>
            </div>
            
            <a href="/eventos/public/day?date=<?php echo $nextDay; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 position-relative" style="overflow: auto; max-height: 70vh;">
                
                <!-- Timeline Container -->
                <div class="timeline-container ps-2" style="min-width: 600px; position: relative; height: 960px;"> <!-- 16 hours * 60px/hour -->
                    
                    <!-- Grid Lines & Hours -->
                    <?php 
                    $startHour = 7;
                    $endHour = 22;
                    $hourHeight = 60; // px
                    
                    for ($h = $startHour; $h <= $endHour; $h++): 
                        $top = ($h - $startHour) * $hourHeight;
                        $timeLabel = sprintf('%02d:00', $h);
                    ?>
                        <div class="position-absolute w-100 border-top border-light-subtle" style="top: <?php echo $top; ?>px; z-index: 1;">
                            <span class="text-muted small position-absolute" style="top: -10px; left: 0; width: 50px; text-align: right;"><?php echo $timeLabel; ?></span>
                            <!-- Dotted half-hour line could go here -->
                        </div>
                    <?php endfor; ?>

                    <!-- Events Rendering -->
                    <div class="events-layer position-absolute" style="top: 0; left: 60px; right: 0; bottom: 0; z-index: 2;">
                        <?php 
                        // Pre-process events to handle overlaps
                        $processedEvents = [];
                        foreach ($events as $event) {
                            $eventStartTimestamp = strtotime($event['date']);
                            $eventHour = (int)date('H', $eventStartTimestamp);
                            
                            // Skip if out of view range
                            if ($eventHour < $startHour || $eventHour > $endHour) continue;
                            
                            $eventMinute = (int)date('i', $eventStartTimestamp);
                            $startOffsetMinutes = ($eventHour - $startHour) * 60 + $eventMinute;
                            
                            $durationMinutes = 60; // Default 1h
                            if (!empty($event['end_date'])) {
                                $endTimestamp = strtotime($event['end_date']);
                                $durationMinutes = ($endTimestamp - $eventStartTimestamp) / 60;
                            }
                            
                            $topPx = ($startOffsetMinutes / 60) * $hourHeight;
                            $heightPx = ($durationMinutes / 60) * $hourHeight;
                            if ($heightPx < 30) $heightPx = 30; // Min height

                            $processedEvents[] = [
                                'data' => $event,
                                'top' => $topPx,
                                'height' => $heightPx,
                                'bottom' => $topPx + $heightPx,
                                'start_ts' => $eventStartTimestamp,
                                'end_ts' => $eventStartTimestamp + ($durationMinutes * 60),
                                'col_index' => 0,
                                'total_cols' => 1
                            ];
                        }

                        // Column packing algorithm
                        $columns = []; // Array of end times for each column
                        foreach ($processedEvents as &$ev) {
                            $placed = false;
                            foreach ($columns as $index => $endTime) {
                                if ($ev['start_ts'] >= $endTime) {
                                    $ev['col_index'] = $index;
                                    $columns[$index] = $ev['end_ts'];
                                    $placed = true;
                                    break;
                                }
                            }
                            if (!$placed) {
                                $ev['col_index'] = count($columns);
                                $columns[] = $ev['end_ts'];
                            }
                        }
                        unset($ev);

                        // Calculate visual width based on max concurrency in time ranges
                        // Simple approach: Checking overlaps again to maximize width usage
                        foreach ($processedEvents as &$ev1) {
                            $maxCols = 0;
                            // Find all events that overlap with this one
                            foreach ($processedEvents as $ev2) {
                                if (!($ev1['end_ts'] <= $ev2['start_ts'] || $ev1['start_ts'] >= $ev2['end_ts'])) {
                                    if ($ev2['col_index'] > $maxCols) {
                                        $maxCols = $ev2['col_index'];
                                    }
                                }
                            }
                            $ev1['total_cols'] = $maxCols + 1;
                        }
                        unset($ev1);


                        foreach ($processedEvents as $item): 
                             $event = $item['data'];
                             // Visibility Logic
                             $isPublic = isset($event['is_public']) ? $event['is_public'] : 1;
                             $isCreator = ($event['created_by'] == $userId);
                             $canViewDetails = $isPublic || $isAdmin || $isCreator;

                             $eventName = $canViewDetails ? $event['name'] : 'Agendamento Privado';
                             $eventLocation = $canViewDetails ? ($event['location_name'] ?? 'Local não definido') : 'Local Reservado';
                             $eventResponsible = $canViewDetails ? ($event['creator_name'] ?? 'N/A') : 'N/A';
                             
                             if (!$canViewDetails && isset($event['location_name'])) {
                                 $eventLocation = $event['location_name'] . ' | Resp: ' . ($event['creator_name'] ?? '??');
                             }

                            // Calculate visual geometry
                            $widthPercent = 100 / $item['total_cols'];
                            $leftPercent = $item['col_index'] * $widthPercent;

                            // Color Logic based on Location
                            $colors = [
                                ['bg' => 'rgba(13, 110, 253, 0.15)', 'border' => '#0d6efd', 'text' => '#0d6efd'], // Blue
                                ['bg' => 'rgba(25, 135, 84, 0.15)', 'border' => '#198754', 'text' => '#198754'], // Green
                                ['bg' => 'rgba(220, 53, 69, 0.15)', 'border' => '#dc3545', 'text' => '#dc3545'], // Red
                                ['bg' => 'rgba(255, 193, 7, 0.15)', 'border' => '#ffc107', 'text' => '#856404'], // Yellow (darker text)
                                ['bg' => 'rgba(13, 202, 240, 0.15)', 'border' => '#0dcaf0', 'text' => '#0aa2c0'], // Cyan
                                ['bg' => 'rgba(111, 66, 193, 0.15)', 'border' => '#6f42c1', 'text' => '#6f42c1'], // Purple
                                ['bg' => 'rgba(253, 126, 20, 0.15)', 'border' => '#fd7e14', 'text' => '#fd7e14'], // Orange
                                ['bg' => 'rgba(32, 201, 151, 0.15)', 'border' => '#20c997', 'text' => '#146c43'], // Teal
                                ['bg' => 'rgba(214, 51, 132, 0.15)', 'border' => '#d63384', 'text' => '#d63384'], // Pink
                                ['bg' => 'rgba(102, 16, 242, 0.15)', 'border' => '#6610f2', 'text' => '#6610f2'], // Indigo
                            ];
                            
                            $locId = $event['location_id'] ?? 0;
                            $colorIndex = $locId % count($colors);
                            $style = $colors[$colorIndex];
                            
                            if (!$canViewDetails) {
                                // Override for private/hidden events
                                $style = ['bg' => 'rgba(108, 117, 125, 0.1)', 'border' => '#6c757d', 'text' => '#6c757d'];
                            }
                        ?>
                            <div class="event-block position-absolute rounded shadow-sm p-2" 
                                 style="top: <?php echo $item['top']; ?>px; height: <?php echo $item['height']; ?>px; 
                                        left: <?php echo $leftPercent; ?>%; width: <?php echo $widthPercent; ?>%;
                                        background-color: <?php echo $style['bg']; ?>; 
                                        border-left: 4px solid <?php echo $style['border']; ?>; 
                                        overflow: hidden; cursor: pointer; z-index: 10;"
                                 onclick="window.location.href='/eventos/public/detail?id=<?php echo $event['id']; ?>'">
                                
                                <div class="fw-bold small text-truncate" style="color: <?php echo $style['text']; ?>">
                                    <?php echo htmlspecialchars($eventName); ?>
                                </div>
                                <div class="small text-muted text-truncate">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('H:i', $item['start_ts']); ?> - 
                                    <?php echo date('H:i', $item['end_ts']); ?>
                                </div>
                                <div class="small text-muted text-truncate">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($eventLocation); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 text-center">
    <a href="/eventos/public/calendar" class="btn btn-secondary rounded-pill"><i class="fas fa-calendar-alt me-2"></i>Voltar para o Mês</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="/eventos/request/form?date=<?php echo $date; ?>" class="btn btn-primary rounded-pill ms-2"><i class="fas fa-plus me-2"></i>Novo Evento</a>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
