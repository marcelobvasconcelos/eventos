<?php
$title = 'Event List';
ob_start();
?>
<!-- Banner Image Section -->
<div class="mb-4 text-center">
    <img src="/eventos/lib/banner.jpeg" alt="Banner UAST Realiza" class="img-fluid rounded-3 shadow-sm w-100" style="object-fit: cover;">
</div>

<!-- Welcome and Action Section -->
<div class="text-center mb-5">
    <?php if (isset($_SESSION['user_name'])): ?>
        <h5 class="text-primary mb-2 fw-light">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <i class="fas fa-smile-beam"></i></h5>
    <?php else: ?>
        <h5 class="text-primary mb-2 fw-light">Bem-vindo!</h5>
    <?php endif; ?>
    
    <a href="/eventos/public/calendar" class="btn btn-primary rounded-pill px-4 fw-bold mt-2"><i class="fas fa-calendar-alt me-2"></i>Ver Calendário</a>
</div>

<div class="card mb-5 border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header border-0 py-4 px-4" style="background-color: #001f3f; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <div class="d-flex align-items-center">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-bolt fa-lg" style="color: #001f3f !important;"></i>
            </div>
            <div>
                <h3 class="fw-bold text-white mb-0">Acontece agora</h3>
                <small class="text-white-50">Eventos em andamento neste exato momento</small>
            </div>
        </div>
    </div>
    <div class="card-body p-0 bg-white" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
        
        <?php if (empty($activeEvents)): ?>
            <div class="alert alert-light border border-light-subtle rounded-3 text-center py-3" role="alert">
                <i class="fas fa-mug-hot text-muted me-2"></i> Nenhum evento ocorrendo agora. Confira a programação abaixo!
            </div>
        <?php else: ?>
            <div id="activeEventsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    $first = true;
                    foreach ($activeEvents as $index => $active): 
                        $isPublic = $active['is_public'] ?? 1;
                        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($active['created_by'] ?? 0);

                        if (!$isPublic && !$isAdmin && !$isOwner) {
                             $active['name'] = "Agendamento Privado";
                             $active['location_name'] = $active['location_name'] . " | Resp: " . ($active['creator_name'] ?? 'N/A');
                        }
                    ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <div class="card border-0 shadow-lg mx-auto overflow-hidden" style="background: linear-gradient(to right, #00c6ff, #0072ff); max-width: 100%; border-radius: 20px; border: 3px solid rgba(255,255,255,0.7);">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-5 position-relative p-2 d-flex align-items-center justify-content-center" style="min-height: 250px; background-color: rgba(0, 198, 255, 0.1);">
                                    <img src="<?php echo htmlspecialchars($active['image_path'] ?? '/eventos/lib/banner.jpeg'); ?>" 
                                         class="img-fluid rounded-3 shadow-sm" 
                                         style="object-fit: contain; max-height: 240px; width: auto; max-width: 100%;" 
                                         alt="<?php echo htmlspecialchars($active['name']); ?>">
                                     <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge fw-bold rounded-pill shadow-sm animate-pulse px-3 py-2" style="background: #004e92; color: white;">
                                            <i class="fas fa-circle fa-xs me-1 text-info"></i> ACONTECENDO AGORA
                                        </span>
                                     </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="card-body p-4 text-white">
                                        <h5 class="card-title fw-bold mb-3 display-4 text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3); font-size: 2.5rem; line-height: 1.1;"><?php echo htmlspecialchars($active['name']); ?></h5>
                                        <div class="d-flex align-items-center mb-4 text-white-50 fs-5">
                                            <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                                            <span class="fw-medium text-white"><?php echo htmlspecialchars($active['location_name'] ?? 'Local a definir'); ?></span>
                                        </div>
                                        <div>
                                             <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($active['id']); ?>" class="btn btn-light text-danger fw-bold rounded-pill px-5 py-3 shadow-sm fs-5 transform-scale">
                                                Participar / Ver Detalhes <i class="fas fa-arrow-right ms-2"></i>
                                             </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $first = false; 
                    endforeach; 
                    ?>
                </div>
                
                <?php if (count($activeEvents) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#activeEventsCarousel" data-bs-slide="prev" style="width: 5%; justify-content: flex-start;">
                        <span class="carousel-control-prev-icon bg-secondary rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#activeEventsCarousel" data-bs-slide="next" style="width: 5%; justify-content: flex-end;">
                        <span class="carousel-control-next-icon bg-secondary rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Próximo</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
.animate-pulse {
    animation: pulse 2s infinite;
}
.carousel-item {
    transition: transform 1s ease-in-out; /* Slower transition for better visibility */
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="form-check form-switch custom-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="hidePastEvents" checked>
        <label class="form-check-label fw-semibold text-muted" for="hidePastEvents">Ocultar Eventos Passados</label>
    </div>
    
    <div class="btn-group" role="group" aria-label="View Mode">
        <button type="button" class="btn btn-outline-primary active" id="viewGrid" title="Visualização em Grade">
            <i class="fas fa-th-large"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" id="viewList" title="Visualização em Lista">
            <i class="fas fa-list"></i>
        </button>
    </div>
</div>

<div class="p-4 rounded-3 mb-5" style="background: url('/eventos/lib/banner2.jpeg') center center / cover no-repeat fixed; box-shadow: inset 0 0 200px rgba(255,255,255,0.9);">
    <h3 class="fw-bold text-primary mb-3 bg-white d-inline-block px-3 py-1 rounded shadow-sm">Eventos Futuros</h3>

    <?php if (empty($events)): ?>
        <div class="text-center py-5 bg-white bg-opacity-75 rounded shadow-sm">
            <i class="far fa-calendar-times fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Nenhum evento encontrado.</h3>
            <p>Tente ajustar seus filtros de busca.</p>
        </div>
    <?php else: ?>
        <div class="row" id="eventsContainer">
            <?php foreach ($events as $event): ?>
                <?php
                    $isPublic = $event['is_public'] ?? 1;
                    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($event['created_by'] ?? 0);

                    if (!$isPublic && !$isAdmin && !$isOwner) {
                            $event['name'] = "Agendamento Privado";
                            $event['location_name'] = ($event['location_name'] ?? 'Local a definir') . " | Resp: " . ($event['creator_name'] ?? 'N/A');
                    }

                    // Color Logic based on Location (Stronger Palette)
                    $colors = [
                        ['border' => '#0d6efd', 'soft' => '#e7f1ff'], // Blue
                        ['border' => '#198754', 'soft' => '#d1e7dd'], // Green
                        ['border' => '#dc3545', 'soft' => '#f8d7da'], // Red
                        ['border' => '#ffc107', 'soft' => '#fff3cd'], // Yellow (Warning)
                        ['border' => '#0dcaf0', 'soft' => '#cff4fc'], // Cyan
                        ['border' => '#6f42c1', 'soft' => '#e0cffc'], // Purple
                        ['border' => '#fd7e14', 'soft' => '#ffe5d0'], // Orange
                        ['border' => '#20c997', 'soft' => '#d2f4ea'], // Teal
                        ['border' => '#d63384', 'soft' => '#f2d0e0'], // Pink
                        ['border' => '#6610f2', 'soft' => '#d0c4f7'], // Indigo
                    ];
                    
                    $locId = $event['location_id'] ?? 0;
                    $colorIndex = $locId % count($colors);
                    $style = $colors[$colorIndex];
                    
                    if (!$isPublic && !$isAdmin && !$isOwner) {
                        $style = ['border' => '#6c757d', 'soft' => '#f8f9fa']; // Gray default
                    }
                ?>
                <div class="col-md-4 mb-4 event-item" data-date="<?php echo $event['date']; ?>">
                    <div class="card h-100 shadow-sm hover-card event-card" 
                        style="border-radius: 12px; background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.6); border-top: 5px solid <?php echo $style['border']; ?> !important; overflow: hidden;">
                        
                        <!-- Event Image -->
                        <div style="height: 160px; overflow: hidden; position: relative;">
                            <img src="<?php echo htmlspecialchars($event['image_path'] ?? '/eventos/lib/banner.jpeg'); ?>" 
                                 alt="<?php echo htmlspecialchars($event['name']); ?>" 
                                 class="w-100 h-100" 
                                 style="object-fit: cover;">
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge rounded-pill px-3 py-2" style="background-color: <?php echo $style['border']; ?>; color: white; font-weight: 600; font-size: 0.95rem;">
                                    <i class="far fa-clock me-1"></i> 
                                    <?php echo date('d/m/Y', strtotime($event['date'])); ?>
                                </span>
                                <?php if (!$isPublic): ?>
                                    <span class="badge bg-secondary"><i class="fas fa-lock fa-xs"></i></span>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title fw-bold mb-3" style="line-height: 1.4;">
                                <span style="background: rgba(255, 243, 205, 0.9); color: #495057; padding: 4px 8px; border-radius: 4px; box-shadow: 2px 2px 5px rgba(0,0,0,0.05);">
                                    <?php echo htmlspecialchars($event['name']); ?>
                                </span>
                            </h5>
                            
                            <div class="mb-4 p-2 rounded" style="background-color: <?php echo $style['soft']; ?>; color: #495057;">
                                <small class="fw-bold"><i class="fas fa-map-marker-alt me-2" style="color: <?php echo $style['border']; ?>;"></i>Local:</small>
                                <div class="ms-4 small"><?php echo htmlspecialchars($event['location_name'] ?? 'Local a definir'); ?></div>
                            </div>

                            <div class="mt-auto pt-3 border-top actions-footer" style="border-color: rgba(0,0,0,0.1) !important;">
                                <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-sm w-100 mb-2 rounded-pill fw-medium" 
                                style="background-color: white; color: <?php echo $style['border']; ?>; border: 1px solid <?php echo $style['border']; ?>;">Ver Detalhes</a>
                                
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <div class="d-flex gap-2">
                                        <a href="/eventos/admin/editEvent?id=<?php echo htmlspecialchars($event['id']); ?>&return_url=<?php echo urlencode('/eventos/'); ?>" class="btn btn-sm btn-light flex-grow-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button type="button" 
                                                class="btn btn-sm btn-light text-danger" 
                                                title="Excluir"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteEventModal"
                                                data-event-id="<?php echo htmlspecialchars($event['id']); ?>"
                                                data-event-name="<?php echo htmlspecialchars($event['name']); ?>"
                                                data-event-date="<?php echo date('d/m/Y', strtotime($event['date'])); ?>"
                                                data-event-location="<?php echo htmlspecialchars($event['location_name'] ?? 'Não definido'); ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
/* List View Styles */
.list-view .event-item {
    width: 100%;
    margin-bottom: 1rem !important;
}
.list-view .event-card {
    flex-direction: row;
    align-items: center;
}
.list-view .card-body {
    flex-direction: row !important;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}
.list-view .actions-footer {
    border-top: none !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
    min-width: 200px;
    margin-left: 20px;
}
</style>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteEventModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="text-danger mb-3">
                        <i class="fas fa-trash-alt fa-3x"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Tem certeza que deseja excluir este evento?</h5>
                    <p class="text-muted">A ação não poderá ser desfeita e removerá todos os dados associados.</p>
                </div>
                
                <div class="bg-light p-3 rounded border mb-3">
                    <h6 class="fw-bold mb-2 text-primary" id="modalEventName">Nome do Evento</h6>
                    <ul class="list-unstyled mb-0 small text-secondary">
                        <li class="mb-1"><i class="far fa-calendar me-2"></i>Data: <span id="modalEventDate" class="fw-medium text-dark"></span></li>
                        <li class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>Local: <span id="modalEventLocation" class="fw-medium text-dark"></span></li>
                    </ul>
                </div>
                
                <form id="deleteEventForm" method="POST" action="/eventos/admin/deleteEvent">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="id" id="modalEventId" value="">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger fw-bold shadow-sm">Sim, Excluir Evento</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Modal Logic
    var deleteModal = document.getElementById('deleteEventModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var eventId = button.getAttribute('data-event-id');
            var eventName = button.getAttribute('data-event-name');
            var eventDate = button.getAttribute('data-event-date');
            var eventLocation = button.getAttribute('data-event-location');
            
            var modalIdInput = deleteModal.querySelector('#modalEventId');
            var modalName = deleteModal.querySelector('#modalEventName');
            var modalDate = deleteModal.querySelector('#modalEventDate');
            var modalLocation = deleteModal.querySelector('#modalEventLocation');
            
            modalIdInput.value = eventId;
            modalName.textContent = eventName;
            modalDate.textContent = eventDate;
            modalLocation.textContent = eventLocation;
        });
    }

    // View Options Logic
    const hidePastCheckbox = document.getElementById('hidePastEvents');
    const viewGridBtn = document.getElementById('viewGrid');
    const viewListBtn = document.getElementById('viewList');
    const eventsContainer = document.getElementById('eventsContainer');
    const eventItems = document.querySelectorAll('.event-item');

    // Load Preferences
    const savedHidePast = localStorage.getItem('hidePastEvents') !== 'false'; // Default true
    const savedViewMode = localStorage.getItem('viewMode') || 'grid';

    // Apply Preferences
    hidePastCheckbox.checked = savedHidePast;
    togglePastEvents(savedHidePast);
    switchView(savedViewMode);

    // Event Listeners
    hidePastCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        localStorage.setItem('hidePastEvents', isChecked);
        togglePastEvents(isChecked);
    });

    viewGridBtn.addEventListener('click', () => {
        localStorage.setItem('viewMode', 'grid');
        switchView('grid');
    });

    viewListBtn.addEventListener('click', () => {
        localStorage.setItem('viewMode', 'list');
        switchView('list');
    });

    function togglePastEvents(hide) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        eventItems.forEach(item => {
            const dateStr = item.getAttribute('data-date');
            const eventDate = new Date(dateStr);
            
            if (hide && eventDate < today) {
                item.classList.add('d-none');
            } else {
                item.classList.remove('d-none');
            }
        });
        
        checkEmptyState();
    }

    function switchView(mode) {
        if (mode === 'list') {
            eventsContainer.classList.add('list-view');
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            eventItems.forEach(item => {
                item.classList.remove('col-md-4');
                item.classList.add('col-12');
            });
        } else {
            eventsContainer.classList.remove('list-view');
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
            eventItems.forEach(item => {
                item.classList.add('col-md-4');
                item.classList.remove('col-12');
            });
        }
    }
    
    function checkEmptyState() {
        // Optional: Show "No events" message if all are hidden
        // keeping it simple for now
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';