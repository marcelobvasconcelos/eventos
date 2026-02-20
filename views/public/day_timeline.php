<?php
$title = 'Agenda do Dia - ' . date('d/m/Y', strtotime($date));
ob_start();

$prevDay = date('Y-m-d', strtotime($date . ' -1 day'));
$nextDay = date('Y-m-d', strtotime($date . ' +1 day'));

// Helper validation for private events
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'] ?? 0;
$currentDate = $date; // Mapping for consistency with new logic
?>

<div class="row">
    <div class="col-12">
        <!-- Navigation Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/eventos/public/day?date=<?php echo $prevDay; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-chevron-left"></i>
            </a>
            
            <div class="text-center">
                <h3 class="fw-bold text-white mb-0" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.5);"><?php echo date('d/m/Y', strtotime($date)); ?></h3>
                <?php
                $days = [
                    'Sunday' => 'Domingo', 'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira', 
                    'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira', 'Friday' => 'Sexta-feira', 
                    'Saturday' => 'Sábado'
                ];
                $dayName = date('l', strtotime($date));
                ?>
                <span class="text-white text-uppercase fw-medium small" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.5);"><?php echo $days[$dayName]; ?></span>
            </div>
            
            <a href="/eventos/public/day?date=<?php echo $nextDay; ?>" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body px-0 pt-3 pb-0 position-relative" style="overflow-x: auto;">
                
                <?php 
                $startHour = 7;
                $endHour = 22;
                $hourHeight = 50; // px
                $totalHeight = ($endHour - $startHour + 1) * $hourHeight;
                ?>
                <!-- Timeline Container -->
                <div class="timeline-container ps-2" style="min-width: 600px; position: relative; height: <?php echo $totalHeight; ?>px;">
                    
                    <!-- Grid Lines & Hours -->
                    <?php 
                    
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
                        // Pre-process events to handle overlaps and multi-day rendering
                        $processedEvents = [];
                        
                        // Define Grid Boundaries for the current view Date
                        $gridStartTs = strtotime($currentDate . sprintf(' %02d:00:00', $startHour));
                        // Grid ends at endHour + 1 (e.g. 22:00 start -> 23:00 end of block? Or just cover until 23:59?)
                        // Timeline shows 07:00 to 22:00. The last block is 22:00-23:00? 
                        // The loop goes $h <= $endHour. If $endHour is 22, it prints 22:00 line.
                        // Ideally we support up to 24:00 if needed, but let's stick to visual grid.
                        // Let's assume visibility cutoff is roughly 23:59 for now to allow late events to show at bottom.
                        $gridEndTs = strtotime($currentDate . ' 23:59:59');

                        foreach ($events as $event) {
                            $eventStartTimestamp = strtotime($event['date']);
                            
                            // Determine End Timestamp
                            if (!empty($event['end_date'])) {
                                $eventEndTimestamp = strtotime($event['end_date']);
                            } else {
                                $eventEndTimestamp = $eventStartTimestamp + 3600; // Default 1h
                            }

                            // Skip if event is completely outside the current day's visible grid
                            // (Ends before grid start OR Starts after grid end)
                            // We use lenient grid end (24h) to ensure we capture late night events even if grid lines stop at 22h,
                            // allowing them to overflow container if needed or just hang at bottom.
                            if ($eventEndTimestamp <= $gridStartTs || $eventStartTimestamp > $gridEndTs) {
                                continue;
                            }

                            // Clamp start/end to Grid/Day boundaries for visual rendering
                            $visualStartTs = max($eventStartTimestamp, $gridStartTs);
                            $visualEndTs = min($eventEndTimestamp, $gridEndTs);
                            
                            // Calculate dimensions relative to Grid Start (7:00)
                            $startOffsetSeconds = $visualStartTs - $gridStartTs;
                            $durationSeconds = $visualEndTs - $visualStartTs;
                            
                            $topPx = ($startOffsetSeconds / 3600) * $hourHeight;
                            $heightPx = ($durationSeconds / 3600) * $hourHeight;
                            
                            if ($heightPx < 30) $heightPx = 30; // Min height

                            // Store original timestamps for logic/sorting, but use clamped for display
                            $processedEvents[] = [
                                'data' => $event,
                                'top' => $topPx,
                                'height' => $heightPx,
                                'bottom' => $topPx + $heightPx,
                                'start_ts' => $visualStartTs,
                                'end_ts' => $visualEndTs,
                                'original_start_ts' => $eventStartTimestamp,
                                'original_end_ts' => $eventEndTimestamp,
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

                             $eventName = $event['name']; // Always visible as per request
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
                            
                            $extraStyle = '';
                            $statusBadge = '';
                            if (($event['status'] ?? '') === 'Cancelado') {
                                $style = ['bg' => 'rgba(220, 53, 69, 0.1)', 'border' => '#dc3545', 'text' => '#dc3545'];
                                $extraStyle = 'text-decoration: line-through; opacity: 0.8;';
                                $statusBadge = ' <span class="badge bg-danger" style="font-size: 0.6em; text-decoration: none;">CANCELADO</span>';
                            }

                            // Popover Content
                            $eventDesc = $event['description'] ?? '';
                            if (!$canViewDetails) $eventDesc = "Detalhes restritos.";
                            
                            $pStart = $item['original_start_ts'];
                            $pEnd = $item['original_end_ts'];
                            // Check if dates differ from view date
                            $viewDateStr = date('Ymd', strtotime($currentDate));
                            $pStartStr = (date('Ymd', $pStart) != $viewDateStr) ? date('d/m H:i', $pStart) : date('H:i', $pStart);
                            $pEndStr = (date('Ymd', $pEnd) != $viewDateStr) ? date('d/m H:i', $pEnd) : date('H:i', $pEnd);

                            $popoverContent = "<strong>Horário:</strong> " . $pStartStr . " até " . $pEndStr . "<br>";
                            $popoverContent .= "<strong>Local:</strong> " . htmlspecialchars($eventLocation) . "<br>";
                            $popoverContent .= "<small>" . htmlspecialchars(substr($eventDesc, 0, 100)) . (strlen($eventDesc)>100?'...':'') . "</small>";
                        ?>
                            <div class="event-block position-absolute rounded shadow-sm p-2" 
                                 style="top: <?php echo $item['top']; ?>px; height: <?php echo $item['height']; ?>px; 
                                        left: <?php echo $leftPercent; ?>%; width: <?php echo $widthPercent; ?>%;
                                        background-color: <?php echo $style['bg']; ?>; 
                                        border-left: 4px solid <?php echo $style['border']; ?>; 
                                        overflow: hidden; cursor: pointer; z-index: 10; <?php echo $extraStyle; ?>"
                                 onclick="window.location.href='/eventos/public/detail?id=<?php echo $event['id']; ?>'"
                                 data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-placement="top"
                                 title="<?php echo htmlspecialchars($eventName . (($event['status']??'')==='Cancelado'?' (CANCELADO)':'')); ?>"
                                 data-bs-content="<?php echo htmlspecialchars($popoverContent); ?>">
                                
                                <div class="fw-bold small text-truncate" style="color: <?php echo $style['text']; ?>">
                                    <?php echo htmlspecialchars($eventName); ?><?php echo $statusBadge; ?>
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
?>
