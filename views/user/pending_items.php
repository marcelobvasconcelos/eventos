<?php
$title = 'Minhas Pendências';
ob_start();
?>
<div class="card shadow-sm border-0 rounded-lg mb-4">
    <div class="card-body py-4">
        <h2 class="text-primary fw-bold mb-1"><i class="fas fa-clipboard-list me-2"></i>Minhas Pendências de Devolução</h2>
        <p class="text-muted mb-0">Gerencie a devolução de chaves e equipamentos dos seus eventos finalizados.</p>
    </div>
</div>

<?php
    $activeItems = [];
    $historyItems = [];
    foreach ($pendingItems as $item) {
        if ($item['status'] == 'completed') {
            $historyItems[] = $item;
        } else {
            $activeItems[] = $item;
        }
    }
?>

<!-- Active Items Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Pendências Ativas</h4>
    <?php if (!empty($activeItems)): ?>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="selectAllPending" style="cursor: pointer;">
            <label class="form-check-label user-select-none fw-bold text-secondary" for="selectAllPending" style="cursor: pointer;">Selecionar Tudo</label>
        </div>
    <?php endif; ?>
</div>

<?php if (empty($activeItems)): ?>
    <div class="alert alert-success shadow-sm rounded-3">
        <i class="fas fa-check-circle me-2"></i> Você não possui pendências de devolução no momento. Parabéns!
    </div>
<?php else: ?>
    <?php 
        // Group items by Event Name
        $groupedItems = [];
        foreach ($activeItems as $item) {
            $eventName = $item['event_name'];
            if (!isset($groupedItems[$eventName])) {
                $groupedItems[$eventName] = [];
            }
            $groupedItems[$eventName][] = $item;
        }
    ?>

    <div class="accordion" id="pendingAccordion">
        <?php $evtIndex = 0; ?>
        <?php foreach ($groupedItems as $eventName => $items): ?>
            <?php 
                $evtIndex++;
                $collapseId = "collapseEvent" . $evtIndex;
                $headingId = "headingEvent" . $evtIndex;
                $eventCheckboxId = "eventCheckbox" . $evtIndex;
                
                // Check if any item in this group is critical (contested) to maybe color the header?
                $hasContested = false;
                foreach($items as $i) { if($i['status'] == 'contested') $hasContested = true; }

                // Separate Keys and Categories
                $keys = [];
                $categories = [];
                
                foreach ($items as $item) {
                    if ($item['item_type'] == 'key' || $item['item_type'] == 'Chave') {
                        $keys[] = $item;
                    } else {
                        // Use item_type as Category Name
                        $catName = $item['item_type'];
                         if (empty($catName) || $catName == 'asset') {
                            $catName = 'Equipamentos';
                         }
                        if (!isset($categories[$catName])) {
                            $categories[$catName] = [];
                        }
                        $categories[$catName][] = $item;
                    }
                }
            ?>
            <div class="accordion-item shadow-sm border-0 mb-3 rounded overflow-hidden">
                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                    <div class="accordion-button <?php echo $evtIndex > 1 ? 'collapsed' : ''; ?> bg-white p-3 d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $evtIndex === 1 ? 'true' : 'false'; ?>" aria-controls="<?php echo $collapseId; ?>">
                        <div class="form-check me-3" onclick="event.stopPropagation();">
                            <input class="form-check-input event-checkbox" type="checkbox" id="<?php echo $eventCheckboxId; ?>" data-target-group="<?php echo $collapseId; ?>" style="transform: scale(1.2); cursor: pointer;">
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($eventName); ?></span>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i> Data: <?php echo date('d/m/Y', strtotime($items[0]['event_date'])); ?> 
                                <span class="mx-2">|</span> 
                                <span class="badge bg-secondary rounded-pill"><?php echo count($items); ?> Item(ns)</span>
                            </small>
                        </div>
                         <?php if ($hasContested): ?>
                            <span class="badge bg-danger ms-2"><i class="fas fa-exclamation-circle"></i> Contestado</span>
                        <?php endif; ?>
                    </div>
                </h2>
                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $evtIndex === 1 ? 'show' : ''; ?>" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#pendingAccordion">
                    <div class="accordion-body bg-light p-3">
                        
                        <!-- Keys Section -->
                        <?php if (!empty($keys)): ?>
                             <h6 class="text-muted mb-3 border-bottom pb-2"><i class="fas fa-key me-2"></i>Chaves</h6>
                             <div class="row mb-3">
                                <?php foreach ($keys as $item): ?>
                                    <div class="col-md-6 mb-3">
                                        
                                        <div class="card shadow-sm h-100 border-0" style="border-left: 5px solid <?php echo $item['status'] == 'contested' ? '#dc3545' : '#ffc107'; ?> !important;">
                                            <div class="card-body position-relative pt-4">
                                                <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                                                    <div class="position-absolute top-0 end-0 p-2">
                                                        <input class="form-check-input pending-checkbox item-of-<?php echo $collapseId; ?>" type="checkbox" value="<?php echo $item['id']; ?>" data-desc="<?php echo htmlspecialchars($item['description']); ?>" style="transform: scale(1.2); cursor: pointer;">
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-key text-warning me-2 fa-lg"></i>
                                                    <h6 class="card-title fw-bold text-dark mb-0 text-truncate" title="<?php echo htmlspecialchars($item['description']); ?>">
                                                        <?php echo htmlspecialchars($item['description']); ?>
                                                    </h6>
                                                </div>

                                                <div class="mb-2">
                                                    <?php 
                                                        $statusBadges = [
                                                            'pending' => '<span class="badge bg-warning text-dark">Pendente</span>',
                                                            'user_informed' => '<span class="badge bg-info text-dark">Informado</span>',
                                                            'contested' => '<span class="badge bg-danger">Contestado</span>',
                                                            'completed' => '<span class="badge bg-success">Concluído</span>'
                                                        ];
                                                        echo $statusBadges[$item['status']] ?? $item['status'];
                                                    ?>
                                                </div>
                                                
                                                <?php if ($item['observation']): ?>
                                                    <div class="alert alert-danger bg-opacity-10 py-2 px-2 small mb-2">
                                                        <strong>Obs:</strong> <?php echo htmlspecialchars($item['observation']); ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-primary w-100 btn-sm rounded-pill mt-2"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#returnModal"
                                                            data-id="<?php echo $item['id']; ?>"
                                                            data-desc="<?php echo htmlspecialchars($item['description']); ?>">
                                                        <i class="fas fa-check me-1"></i> Devolver
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-light w-100 btn-sm rounded-pill mt-2 text-muted" disabled>
                                                        <i class="fas fa-clock me-1"></i> Aguardando
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                             </div>
                        <?php endif; ?>

                        <!-- Categories Section -->
                        <?php if (!empty($categories)): ?>
                            <?php $catIndex = 0; ?>
                            <?php foreach ($categories as $catName => $catItems): ?>
                                <?php 
                                    $catIndex++;
                                    $catCollapseId = $collapseId . "_cat_" . $catIndex;
                                    $catCheckboxId = $collapseId . "_catCheck_" . $catIndex;
                                ?>
                                <div class="accordion-item border-0 mb-3 bg-white">
                                    <h2 class="accordion-header">
                                        <div class="accordion-button collapsed bg-white border-bottom p-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $catCollapseId; ?>">
                                            <div class="form-check me-3" onclick="event.stopPropagation();">
                                                <input class="form-check-input category-checkbox item-of-<?php echo $collapseId; ?>" type="checkbox" id="<?php echo $catCheckboxId; ?>" data-target-group="<?php echo $catCollapseId; ?>" data-parent-group="<?php echo $collapseId; ?>" style="transform: scale(1.1); cursor: pointer;">
                                            </div>
                                            <span class="fw-bold text-secondary"><?php echo htmlspecialchars($catName); ?></span>
                                            <span class="badge bg-light text-dark ms-2 border"><?php echo count($catItems); ?></span>
                                        </div>
                                    </h2>
                                    <div id="<?php echo $catCollapseId; ?>" class="accordion-collapse collapse show">
                                        <div class="accordion-body pb-0">
                                            <div class="row">
                                                <?php foreach ($catItems as $item): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card shadow-sm h-100 border-0" style="border-left: 5px solid <?php echo $item['status'] == 'contested' ? '#dc3545' : '#17a2b8'; ?> !important;">
                                                            <div class="card-body position-relative pt-4">
                                                                <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                                                                    <div class="position-absolute top-0 end-0 p-2">
                                                                        <input class="form-check-input pending-checkbox item-of-<?php echo $collapseId; ?> item-of-<?php echo $catCollapseId; ?>" type="checkbox" value="<?php echo $item['id']; ?>" data-desc="<?php echo htmlspecialchars($item['description']); ?>" style="transform: scale(1.2); cursor: pointer;">
                                                                    </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="fas fa-box-open text-info me-2 fa-lg"></i>
                                                                    <h6 class="card-title fw-bold text-dark mb-0 text-truncate" title="<?php echo htmlspecialchars($item['description']); ?>">
                                                                        <?php echo htmlspecialchars($item['description']); ?>
                                                                    </h6>
                                                                </div>

                                                                <div class="mb-2">
                                                                    <?php 
                                                                        $statusBadges = [
                                                                            'pending' => '<span class="badge bg-warning text-dark">Pendente</span>',
                                                                            'user_informed' => '<span class="badge bg-info text-dark">Informado</span>',
                                                                            'contested' => '<span class="badge bg-danger">Contestado</span>',
                                                                            'completed' => '<span class="badge bg-success">Concluído</span>'
                                                                        ];
                                                                        echo $statusBadges[$item['status']] ?? $item['status'];
                                                                    ?>
                                                                </div>
                                                                
                                                                <?php if ($item['observation']): ?>
                                                                    <div class="alert alert-danger bg-opacity-10 py-2 px-2 small mb-2">
                                                                        <strong>Obs:</strong> <?php echo htmlspecialchars($item['observation']); ?>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                                                                    <button type="button" 
                                                                            class="btn btn-outline-primary w-100 btn-sm rounded-pill mt-2"
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#returnModal"
                                                                            data-id="<?php echo $item['id']; ?>"
                                                                            data-desc="<?php echo htmlspecialchars($item['description']); ?>">
                                                                        <i class="fas fa-check me-1"></i> Devolver
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-light w-100 btn-sm rounded-pill mt-2 text-muted" disabled>
                                                                        <i class="fas fa-clock me-1"></i> Aguardando
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- History Section -->
<?php if (!empty($historyItems)): ?>
    <hr class="my-5">
    <h4 class="mb-3 text-secondary"><i class="fas fa-history me-2"></i>Histórico de Devoluções</h4>
    <div class="row">
        <?php foreach ($historyItems as $item): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-0 bg-white">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title fw-bold text-muted mb-0">
                                <?php if ($item['item_type'] == 'key'): ?>
                                    <i class="fas fa-key me-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-box-open me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['description']); ?>
                            </h5>
                            <span class="badge bg-success">Concluído</span>
                        </div>
                         <p class="mb-2"><small class="text-muted">Evento:</small> <strong><?php echo htmlspecialchars($item['event_name']); ?></strong></p>
                         <p class="mb-1"><small class="text-muted">Data do Evento:</small> <?php echo date('d/m/Y', strtotime($item['event_date'])); ?></p>
                         <?php if ($item['user_note']): ?>
                            <p class="text-muted small fst-italic mt-2">"<?php echo htmlspecialchars($item['user_note']); ?>"</p>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Floating Action Button for Batch Return -->
<div class="position-fixed bottom-0 end-0 p-4 mb-3 animate__animated animate__fadeInUp" style="z-index: 1030; display: none;" id="batchReturnFab">
     <button type="button" 
            class="btn btn-primary btn-lg rounded-pill shadow-lg fw-bold px-4 py-3"
            data-bs-toggle="modal" 
            data-bs-target="#returnModal"
            data-batch="true">
        <i class="fas fa-check-double me-2"></i>Devolver Selecionados (<span id="selectedCount">0</span>)
    </button>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="/eventos/pending/markReturned" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Informar Devolução</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Dynamic Inputs for Batch or Single -->
                    <div id="modalInputsContainer"></div>
                    
                    <div class="mb-3" id="singleItemDisplay">
                        <label class="form-label fw-bold">Item:</label>
                        <p id="returnItemDesc" class="form-control-plaintext text-muted"></p>
                    </div>
                    
                    <div class="mb-3" id="batchItemDisplay" style="display: none;">
                        <label class="form-label fw-bold">Itens Selecionados:</label>
                        <ul id="batchItemList" class="text-muted small"></ul>
                    </div>
                    <div class="mb-3">
                        <label for="user_note" class="form-label fw-bold">Informações Adicionais (Opcional)</label>
                        <textarea class="form-control" name="user_note" id="user_note" rows="3" placeholder="Ex: Entreguei para Fulano na portaria..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirmar Devolução</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var returnModal = document.getElementById('returnModal');
        var batchFab = document.getElementById('batchReturnFab');
        var selectedCountSpan = document.getElementById('selectedCount');
        
        var selectAllCheckbox = document.getElementById('selectAllPending');
        var eventCheckboxes = document.querySelectorAll('.event-checkbox');
        var categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        var itemCheckboxes = document.querySelectorAll('.pending-checkbox');
        
        var modalInputsContainer = document.getElementById('modalInputsContainer');
        var modalSingleDisplay = document.getElementById('singleItemDisplay');
        var modalBatchDisplay = document.getElementById('batchItemDisplay');
        var modalBatchList = document.getElementById('batchItemList');
        var modalItemDesc = document.getElementById('returnItemDesc');

        // --- Logic Selection ---
        // Reuse same logic from admin but adapted for card layout

        // 1. Select All Global
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                eventCheckboxes.forEach(ecb => ecb.checked = isChecked);
                categoryCheckboxes.forEach(ccb => ccb.checked = isChecked);
                itemCheckboxes.forEach(icb => icb.checked = isChecked);
                updateUI();
            });
        }

        // 2. Select Event Group
        eventCheckboxes.forEach(ecb => {
             ecb.addEventListener('change', function() {
                 const isChecked = this.checked;
                 const targetId = this.getAttribute('data-target-group');
                 
                 // Items belonging to this Event (Direct items + Items in nested categories)
                 // Categories should strictly also be checked? Yes.
                 // item-of-collapseId class is present on both keys and category items (keys have 1 class, cat items have 2)
                 const itemsInGroup = document.querySelectorAll('.item-of-' + targetId);
                 itemsInGroup.forEach(item => {
                    if (!item.disabled) item.checked = isChecked;
                 });
                 
                 updateMasterState();
                 updateUI();
             });
        });

        // 3. Select Category Group
        categoryCheckboxes.forEach(ccb => {
            ccb.addEventListener('change', function() {
                const isChecked = this.checked;
                const targetId = this.getAttribute('data-target-group'); // catCollapseId
                
                const itemsInGroup = document.querySelectorAll('.item-of-' + targetId);
                itemsInGroup.forEach(item => {
                    if (!item.disabled) item.checked = isChecked;
                });
                
                // Update Parent Event
                const parentGroupId = this.getAttribute('data-parent-group'); 
                updateEventState(parentGroupId);
                
                updateMasterState();
                updateUI();
            });
        });

        // 4. Select Individual Item
        itemCheckboxes.forEach(icb => {
            icb.addEventListener('change', function() {
                 const classes = this.className.split(' ');
                 
                 // Check dependencies
                 const catClass = classes.find(c => c.startsWith('item-of-') && c.includes('_cat_'));
                 if (catClass) {
                     const catId = catClass.replace('item-of-', '');
                     updateCategoryState(catId);
                 }

                 const eventClass = classes.find(c => c.startsWith('item-of-collapseEvent'));
                 if (eventClass) {
                     const eventId = eventClass.replace('item-of-', '');
                     updateEventState(eventId);
                 }
                 
                 updateMasterState();
                 updateUI();
            });
        });
        
        function updateCategoryState(catCollapseId) {
            const itemsInGroup = document.querySelectorAll('.item-of-' + catCollapseId);
            const groupCheckbox = document.querySelector('.category-checkbox[data-target-group="' + catCollapseId + '"]');
            
            if (!groupCheckbox || itemsInGroup.length === 0) return;

            const total = itemsInGroup.length;
            const checked = document.querySelectorAll('.item-of-' + catCollapseId + ':checked').length;
            
            groupCheckbox.checked = (total === checked);
            groupCheckbox.indeterminate = (checked > 0 && checked < total);
        }
        
        function updateEventState(collapseId) {
             const itemsInGroup = document.querySelectorAll('.pending-checkbox.item-of-' + collapseId); // Use pending-checkbox class specifically to avoid double counting category checkbox?
             const groupCheckbox = document.querySelector('.event-checkbox[data-target-group="' + collapseId + '"]');
             
             if (!groupCheckbox || itemsInGroup.length === 0) return;

             const total = itemsInGroup.length;
             const checked = Array.from(itemsInGroup).filter(el => el.checked).length;
             
             groupCheckbox.checked = (total === checked);
             groupCheckbox.indeterminate = (checked > 0 && checked < total);
        }

        function updateMasterState() {
             if(!selectAllCheckbox) return;
             const total = itemCheckboxes.length;
             const checked = document.querySelectorAll('.pending-checkbox:checked').length;
             
             selectAllCheckbox.checked = (total === checked && total > 0);
             selectAllCheckbox.indeterminate = (checked > 0 && checked < total);
        }

        function updateUI() {
            const selected = document.querySelectorAll('.pending-checkbox:checked');
            const count = selected.length;
            selectedCountSpan.textContent = count;
            
            if (count > 0) {
                batchFab.style.display = 'block';
            } else {
                batchFab.style.display = 'none';
            }
        }

        // --- Modal Logic ---
        returnModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var isBatch = button.getAttribute('data-batch') === 'true';
            
            modalInputsContainer.innerHTML = ''; // Clear previous inputs
            
            if (isBatch) {
                // Batch Mode
                modalSingleDisplay.style.display = 'none';
                modalBatchDisplay.style.display = 'block';
                modalBatchList.innerHTML = '';
                
                const selected = document.querySelectorAll('.pending-checkbox:checked');
                selected.forEach(cb => {
                    // Add Hidden Input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]'; // Note array name
                    input.value = cb.value;
                    modalInputsContainer.appendChild(input);
                    
                    // Add to list
                    const li = document.createElement('li');
                    li.textContent = cb.getAttribute('data-desc');
                    modalBatchList.appendChild(li);
                });
                
            } else {
                // Single Mode
                modalSingleDisplay.style.display = 'block';
                modalBatchDisplay.style.display = 'none';
                
                var id = button.getAttribute('data-id');
                var desc = button.getAttribute('data-desc');
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                modalInputsContainer.appendChild(input);
                
                modalItemDesc.textContent = desc;
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
