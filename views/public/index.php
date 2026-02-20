<?php
$title = 'Event List';
ob_start();
?>
<?php 
require_once __DIR__ . '/../../models/Config.php';
$configModel = new Config();
$globalConfigs = $configModel->getAll();

// Helper to get image path or default
function getImagePath($path, $default) {
    return !empty($path) ? '/eventos/' . $path : $default;
}

$bannerImage = getImagePath($globalConfigs['home_banner_image'] ?? '', '/eventos/lib/banner.jpeg');
$cardDefaultImage = getImagePath($globalConfigs['event_card_default_image'] ?? '', '/eventos/lib/banner.jpeg');
?>
<!-- Banner Image Section -->
<div class="mb-4 text-center">
    <img src="<?php echo htmlspecialchars($bannerImage); ?>?t=<?php echo time(); ?>" alt="Banner UAST Realiza" class="img-fluid rounded-3 shadow-sm w-100" style="object-fit: cover;">
</div>

<!-- Welcome and Action Section -->
<div class="text-center mb-5">
    <?php if (isset($_SESSION['user_name'])): ?>
        <h5 class="text-white mb-2 fw-light">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <i class="fas fa-smile-beam"></i></h5>
    <?php else: ?>
        <h5 class="text-white mb-2 fw-light">Bem-vindo!</h5>
    <?php endif; ?>
    
    <a href="/eventos/public/calendar" class="btn btn-primary rounded-pill px-4 fw-bold mt-2"><i class="fas fa-calendar-alt me-2"></i>Ver Calendário</a>
</div>

<div class="card mb-5 border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header border-0 py-4 px-4" style="background-color: #d1bc96 !important; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background-color: #ffffff;">
                <i class="fas fa-bolt fa-lg" style="color: #001f3f;"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #001f3f;">Acontece Agora</h3>
                <small style="color: #001f3f;">Eventos em andamento neste exato momento</small>
            </div>
        </div>
    </div>
    <div class="card-body p-0" style="background-color: rgba(255, 255, 255, 0.1) !important; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
        
        <?php if (empty($activeEvents)): ?>
            <div class="alert alert-light border border-light-subtle rounded-3 text-center py-3" role="alert">
                <i class="fas fa-mug-hot me-2" style="color: #001f3f;"></i> Nenhum evento ocorrendo agora. Confira a programação abaixo!
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
                             // $active['name'] remains visible
                             $active['location_name'] = $active['location_name'] . " | Resp: " . ($active['creator_name'] ?? 'N/A');
                        }
                    ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <div class="card border-0 shadow-lg mx-auto overflow-hidden" style="background: linear-gradient(to right, #fdfbf7, #e6d5b8); max-width: 100%; border-radius: 20px; border: 3px solid rgba(0, 31, 63, 0.1);">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-5 position-relative p-2 d-flex align-items-center justify-content-center" style="min-height: 250px; background-color: rgba(0, 0, 0, 0.02);">
                                    <img src="<?php echo htmlspecialchars(!empty($active['image_path']) ? $active['image_path'] : $cardDefaultImage); ?>" 
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
                                    <div class="card-body p-4 text-dark text-center">
                                        <h5 class="card-title fw-bold mb-3 display-4" style="color: #001f3f; text-shadow: 1px 1px 2px rgba(255,255,255,0.5); font-size: 2.5rem; line-height: 1.1;"><?php echo htmlspecialchars($active['name']); ?></h5>
                                        <div class="d-flex align-items-center justify-content-center mb-4 fs-5" style="color: #4a4a4a;">
                                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                            <span class="fw-medium"><?php echo htmlspecialchars($active['location_name'] ?? 'Local a definir'); ?></span>
                                        </div>
                                        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                                             <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($active['id']); ?>" class="btn btn-primary fw-bold rounded-pill px-5 py-3 shadow-sm fs-5 transform-scale w-100 w-md-auto" style="background-color: #001f3f; border: none;">
                                                Participar / Ver Detalhes <i class="fas fa-arrow-right ms-2"></i>
                                             </a>
                                             <?php if (!empty($active['external_link'])): ?>
                                                <a href="<?php echo htmlspecialchars($active['external_link']); ?>" target="_blank" class="btn btn-warning fw-bold rounded-pill px-4 py-3 shadow-sm fs-5 transform-scale text-dark d-inline-flex align-items-center justify-content-center w-100 w-md-auto">
                                                    <i class="fas fa-link me-2"></i>
                                                    <?php echo htmlspecialchars(!empty($active['link_title']) ? $active['link_title'] : 'Acessar Link'); ?> 
                                                </a>
                                             <?php endif; ?>
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
        <label class="form-check-label fw-semibold text-white" for="hidePastEvents">Ocultar Eventos Passados</label>
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

<div id="upcomingEventsWrapper" class="p-4 rounded-3 mb-5" style="background: url('/eventos/lib/banner2.jpg') center center / cover no-repeat fixed; box-shadow: inset 0 0 200px rgba(255,255,255,0.9);">
    <h3 class="fw-bold text-primary mb-3 bg-white d-inline-block px-3 py-1 rounded shadow-sm">Eventos Futuros</h3>

    <?php if (empty($events)): ?>
        <div class="text-center py-5 bg-white bg-opacity-75 rounded shadow-sm">
            <i class="far fa-calendar-times fa-4x mb-3" style="color: #001f3f;"></i>
            <h3 style="color: #001f3f;">Nenhum evento encontrado.</h3>
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
                            // $event['name'] remains visible
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
                <div id="event-card-<?php echo $event['id']; ?>" class="col-md-4 mb-4 event-item" data-date="<?php echo $event['date']; ?>">
                    <div class="card h-100 shadow-sm hover-card event-card" 
                        style="border-radius: 12px; background-color: rgba(255, 255, 255, 0.1) !important; border: 1px solid rgba(255, 255, 255, 0.2); border-top: none !important; backdrop-filter: blur(5px); overflow: hidden;">
                        
                        <!-- Title Header Bar -->
                        <div class="card-title-bar py-2 px-3 text-center d-flex align-items-center justify-content-center position-relative" style="background-color: <?php echo $style['border']; ?>; min-height: 60px;">
                            <h5 class="card-title fw-bold mb-0 text-white m-0" style="line-height: 1.2; font-size: 1.1rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                <?php echo htmlspecialchars($event['name']); ?>
                            </h5>
                            <?php if (!$isPublic): ?>
                                <span class="position-absolute end-0 me-3 text-white-50" title="Evento Privado"><i class="fas fa-lock text-white"></i></span>
                            <?php endif; ?>
                        </div>

                        <!-- Event Image -->
                        <div class="card-img-wrapper position-relative">
                            <img src="<?php echo htmlspecialchars(!empty($event['image_path']) ? $event['image_path'] : $cardDefaultImage); ?>" 
                                 alt="<?php echo htmlspecialchars($event['name']); ?>" 
                                 class="w-100 h-100 event-card-img" 
                                 style="object-fit: cover;">
                        </div>

                        <div class="card-body d-flex flex-column p-4 text-center">
                             <!-- Date and Location -->
                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-3">
                                <span class="badge rounded-pill px-3 py-2 bg-light text-dark border shadow-sm" style="font-weight: 600; font-size: 0.85rem;">
                                    <i class="far fa-clock me-1 text-primary"></i> 
                                    <?php echo date('d/m/Y', strtotime($event['date'])); ?>
                                </span>
                                
                                <div class="px-3 py-1 rounded d-inline-flex align-items-center border bg-light shadow-sm" style="color: #495057; font-size: 0.85rem;">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                    <span class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($event['location_name'] ?? 'Local a definir'); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($event['external_link'])): ?>
                                <div class="mb-3 px-2 text-center">
                                    <a href="<?php echo htmlspecialchars($event['external_link']); ?>" target="_blank" class="btn btn-sm btn-info bg-opacity-10 text-primary border border-info border-opacity-25 fw-bold rounded-pill px-4 shadow-sm hover-scale d-inline-flex align-items-center justify-content-center" style="white-space: normal; max-width: 100%;">
                                        <i class="fas fa-link me-2 flex-shrink-0"></i>
                                        <?php echo htmlspecialchars(!empty($event['link_title']) ? $event['link_title'] : 'Acessar Link'); ?> 
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-3 border-top actions-footer" style="border-color: rgba(255,255,255,0.1) !important;">
                                <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-sm w-100 mb-2 rounded-pill fw-medium shadow-sm hover-scale" 
                                style="background-color: rgba(0, 31, 63, 0.75); color: white; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(2px);">Ver Detalhes</a>
                                
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
        
        <!-- Load More Button -->
        <div class="text-center mt-4 d-none" id="loadMoreContainer">
            <button id="loadMoreBtn" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                Ver mais eventos <i class="fas fa-chevron-down ms-2"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
@media (max-width: 768px) {
    /* ... existing mobile styles ... */
    #upcomingEventsWrapper { padding: 0.5rem !important; border-radius: 0 !important; }
    #eventsContainer { 
        display: flex; 
        flex-wrap: nowrap !important; 
        overflow-x: auto; 
        scroll-snap-type: x mandatory; 
        padding-bottom: 2rem !important; /* Manted shadows and buttons visible */
        -webkit-overflow-scrolling: touch; 
        scrollbar-width: none; 
        margin-right: -1rem; 
        margin-left: -1rem; 
        padding-left: 1rem; 
        padding-right: 1rem; 
    }
    #eventsContainer::-webkit-scrollbar { display: none; }
    .event-item { 
        flex: 0 0 85% !important; 
        width: 85% !important; 
        max-width: 85% !important; 
        scroll-snap-align: center; 
        margin-right: 1rem; 
        margin-bottom: 0 !important; 
        padding-left: 0;
        padding-right: 0;
    }
    .event-item:last-child { margin-right: 2rem; }
    .event-card { height: 100%; width: 100%; border-radius: 20px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important; }
    .card-img-wrapper { height: 200px !important; background-color: #f8f9fa; /* Light gray background for contain mode */ }
    .event-card-img { object-fit: contain !important; /* Ensure full image is visible */ }
    .btn-group[aria-label="View Mode"] { display: none !important; }
    #loadMoreContainer { margin-top: 1rem !important; }
}

/* Default Card Image Height (Grid View / Desktop) */
.card-img-wrapper {
    height: 160px;
    overflow: hidden;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

/* List View Styles - CSS Grid Implementation */
.list-view .event-item {
    width: 100%;
    margin-bottom: 1rem !important;
}
.list-view .event-card {
    display: grid;
    grid-template-columns: 500px 1fr; /* Much wider image area */
    grid-template-rows: auto 1fr; /* Title bar auto height, body takes rest */
    grid-template-areas: 
        "img title"
        "img body";;
    border: none !important; /* Cleaner look for list items */
    background: white !important;
    min-height: 150px; /* Further reduced min-height for compact look */
}

.list-view .card-img-wrapper {
    grid-area: img;
    height: 100% !important; /* Full height in list view */
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
    border-top-right-radius: 0; /* Remove right radius in list view */
    background-color: transparent;
    display: flex; /* alignment */
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.list-view .card-img-wrapper img {
    object-fit: contain !important; /* Resize to fit, DO NOT crop */
    height: 100%;
    width: 100%;
    object-position: center;
}

.list-view .card-title-bar {
    grid-area: title;
    border-top-right-radius: 12px; /* Restore radius here */
    border-top-left-radius: 0; /* Attach to image */
    justify-content: center !important; /* Center text */
    width: 100%;
}

.list-view .card-body {
    grid-area: body;
    justify-content: center;
    border-bottom-right-radius: 12px;
}

.list-view .actions-footer {
    border-top: none !important;
    margin-top: 1rem !important;
    padding-top: 0 !important;
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

    // View & Pagination Logic
    const hidePastCheckbox = document.getElementById('hidePastEvents');
    const viewGridBtn = document.getElementById('viewGrid');
    const viewListBtn = document.getElementById('viewList');
    const eventsContainer = document.getElementById('eventsContainer');
    const eventItems = document.querySelectorAll('.event-item');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const loadMoreBtn = document.getElementById('loadMoreBtn');

    // Constants
    const LIMIT_GRID = 6;
    const LIMIT_LIST = 6;

    // State
    let currentView = localStorage.getItem('viewMode') || 'grid';
    let hidePast = localStorage.getItem('hidePastEvents') !== 'false';
    let isExpanded = false;

    // Initialize
    applyViewMode(currentView);
    hidePastCheckbox.checked = hidePast;
    updateVisibility();

    // Event Listeners
    hidePastCheckbox.addEventListener('change', function() {
        hidePast = this.checked;
        localStorage.setItem('hidePastEvents', hidePast);
        updateVisibility();
    });

    viewGridBtn.addEventListener('click', () => {
        currentView = 'grid';
        localStorage.setItem('viewMode', 'grid');
        applyViewMode('grid');
        updateVisibility();
    });

    viewListBtn.addEventListener('click', () => {
        currentView = 'list';
        localStorage.setItem('viewMode', 'list');
        applyViewMode('list');
        updateVisibility();
    });

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            isExpanded = true;
            updateVisibility();
        });
    }

    function applyViewMode(mode) {
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

    function updateVisibility() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const isMobile = window.innerWidth < 768;
        // On mobile, show all events (limit = Infinity) to allow reel scrolling
        const limit = (isExpanded || isMobile) ? Infinity : (currentView === 'grid' ? LIMIT_GRID : LIMIT_LIST);
        
        let visibleCount = 0;
        let totalCandidate = 0;

        eventItems.forEach(item => {
            const dateStr = item.getAttribute('data-date');
            const eventDate = new Date(dateStr);
            const isPast = eventDate < today;
            
            // 1. Date Filter
            if (hidePast && isPast) {
                item.classList.add('d-none');
                return; // Skip counting
            }
            
            totalCandidate++;

            // 2. Pagination Limit
            if (visibleCount < limit) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });
        
        // Show/Hide Load More Button
        if (loadMoreContainer) {
            // Hide button on mobile or if all items are shown
            if (totalCandidate > limit && !isMobile) {
                loadMoreContainer.classList.remove('d-none');
            } else {
                loadMoreContainer.classList.add('d-none');
            }
        }
        
        checkEmptyState();
    }
    

    function checkEmptyState() {
        // Implementation for empty state handling if needed
    }

    // Highlight Event Logic (Mobile Redirect)
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight_event_id');
    
    if (highlightId) {
        const targetCard = document.getElementById('event-card-' + highlightId);
        if (targetCard) {
            // Remove d-none if it was hidden by pagination/filters
            targetCard.classList.remove('d-none');
            
            // Scroll into view
            setTimeout(() => {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
                
                // Add highlight animation
                targetCard.querySelector('.event-card').classList.add('highlight-pulse');
                
                // Remove param from URL without reload
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: newUrl}, '', newUrl);
            }, 500); // Small delay to ensure layout is ready
        }
    }
});
</script>

<style>
@keyframes highlightPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); }
    50% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}
.highlight-pulse {
    animation: highlightPulse 1.5s ease-out;
    border: 2px solid #0d6efd !important; /* Blue border to indicate selection */
    z-index: 10;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';