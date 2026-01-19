<?php
$title = 'Event List';
ob_start();
?>
<div class="text-center mb-5">
    <?php if (isset($_SESSION['user_name'])): ?>
        <h5 class="text-primary mb-2">Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <i class="fas fa-smile-beam"></i></h5>
    <?php endif; ?>
    <h1 class="fw-bold text-primary">Próximos Eventos</h1>
    <p class="text-muted">Confira nossa programação e participe!</p>
    <a href="/eventos/public/calendar" class="btn btn-primary rounded-pill px-4"><i class="fas fa-calendar-alt me-2"></i>Ver Calendário</a>
</div>

<div class="card mb-5 border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(to right, #ffffff, #f8f9fa);">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-bolt fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold text-primary mb-0">Acontecendo Agora</h3>
                <small class="text-muted">Eventos em andamento neste exato momento</small>
            </div>
        </div>
        
        <?php if (empty($activeEvents)): ?>
            <div class="alert alert-light border border-light-subtle rounded-3 text-center py-3" role="alert">
                <i class="fas fa-mug-hot text-muted me-2"></i> Nenhum evento ocorrendo agora. Confira a programação abaixo!
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($activeEvents as $active): ?>
                    <div class="col-md-6 lg-4">
                        <div class="card h-100 border-0 shadow-sm" style="background-color: #fff; border-left: 5px solid #0d6efd !important;">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($active['name']); ?></h5>
                                <div class="d-flex align-items-center text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                    <span><?php echo htmlspecialchars($active['location_name'] ?? 'Local a definir'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill px-3">
                                        <i class="fas fa-circle fa-xs me-1 animate-pulse"></i> Ao Vivo
                                    </span>
                                    <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($active['id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill stretched-link">Ver Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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

<?php if (empty($events)): ?>
    <div class="text-center py-5">
        <i class="far fa-calendar-times fa-4x text-muted mb-3"></i>
        <h3 class="text-muted">Nenhum evento encontrado.</h3>
        <p>Tente ajustar seus filtros de busca.</p>
    </div>
<?php else: ?>
    <div class="row" id="eventsContainer">
        <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-4 event-item" data-date="<?php echo $event['date']; ?>">
                <div class="card h-100 border-0 shadow-sm hover-card event-card" style="border-radius: 12px; transition: transform 0.2s;">
                    <div class="card-body d-flex flex-column p-4">
                        <div class="mb-3">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2">
                                <i class="far fa-clock me-1"></i> 
                                <?php echo date('d/m/Y', strtotime($event['date'])); ?>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($event['name']); ?></h5>
                        
                        <div class="mb-4 text-muted small">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                            <?php echo htmlspecialchars($event['location_name'] ?? 'Local a definir'); ?>
                        </div>

                        <div class="mt-auto pt-3 border-top actions-footer">
                            <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-outline-primary w-100 mb-2 rounded-pill">Ver Detalhes</a>
                            
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <div class="d-flex gap-2">
                                    <a href="/eventos/admin/editEvent?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-sm btn-light flex-grow-1" title="Editar"><i class="fas fa-edit"></i></a>
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