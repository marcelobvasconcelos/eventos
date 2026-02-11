<?php
$title = 'Gestão de Pendências';
ob_start();
?>

<h1 class="text-white mb-4">Gestão de Pendências</h1>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="text-primary fw-bold mb-0"><i class="fas fa-tasks me-2"></i>Itens para Devolução</h5>
        
        <!-- Bulk Actions Toolbar (Hidden by default) -->
        <div id="bulkActions" class="d-none animate__animated animate__fadeIn">
            <span class="me-2 fw-bold text-secondary text-uppercase small"><span id="selectedCount">0</span> Selecionado(s)</span>
            <div class="btn-group">
                <button type="button" class="btn btn-success text-white" onclick="submitBulkAction('completed')">
                    <i class="fas fa-check-double me-1"></i> Receber Selecionados
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="openBulkContestModal()">
                    <i class="fas fa-exclamation-triangle me-1"></i> Contestar Selecionados
                </button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <form id="bulkForm" action="/eventos/pending/updateStatus" method="POST">
            <input type="hidden" name="status" id="bulkStatusInput">
            
            <div class="px-3 pt-3">
                 <?php
                    // Separate Active and History
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

                <?php if (empty($activeItems)): ?>
                    <div class="text-center py-5 text-muted bg-light rounded mb-3">
                        <i class="fas fa-check-circle fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Nenhuma pendência aguardando ação.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" style="cursor: pointer;">
                            <label class="form-check-label fw-bold text-secondary user-select-none" for="selectAll" style="cursor: pointer;">Selecionar Tudo</label>
                        </div>
                    </div>

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

                    <div class="accordion mb-3" id="adminPendingAccordion">
                        <?php $evtIndex = 0; ?>
                        <?php foreach ($groupedItems as $eventName => $items): ?>
                            <?php 
                                $evtIndex++;
                                $collapseId = "collapseEvent" . $evtIndex;
                                $headingId = "headingEvent" . $evtIndex;
                                $eventCheckboxId = "eventCheckbox" . $evtIndex;
                                
                                // Separate Keys and Categories
                                $keys = [];
                                $categories = [];
                                
                                foreach ($items as $item) {
                                    if ($item['item_type'] == 'key' || $item['item_type'] == 'Chave') {
                                        $keys[] = $item;
                                    } else {
                                        // Use item_type as Category Name (defaults to 'asset' or 'Equipamento' if old data)
                                        $catName = $item['item_type'];
                                        if (empty($catName) || $catName == 'asset') {
                                            $catName = 'Equipamentos'; // Fallback
                                        }
                                        if (!isset($categories[$catName])) {
                                            $categories[$catName] = [];
                                        }
                                        $categories[$catName][] = $item;
                                    }
                                }
                            ?>
                            <div class="accordion-item border shadow-sm mb-3 rounded overflow-hidden">
                                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                    <div class="accordion-button <?php echo $evtIndex > 1 ? 'collapsed' : ''; ?> bg-white p-3 d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $evtIndex === 1 ? 'true' : 'false'; ?>" aria-controls="<?php echo $collapseId; ?>">
                                        <div class="form-check me-3" onclick="event.stopPropagation();">
                                            <input class="form-check-input event-checkbox" type="checkbox" id="<?php echo $eventCheckboxId; ?>" data-target-group="<?php echo $collapseId; ?>" style="transform: scale(1.2); cursor: pointer;">
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <span class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($eventName); ?></span>
                                            <small class="text-muted">
                                                <i class="fas fa-list-ul me-1"></i> <?php echo count($items); ?> Pendência(s)
                                            </small>
                                        </div>
                                    </div>
                                </h2>
                                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $evtIndex === 1 ? 'show' : ''; ?>" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#adminPendingAccordion">
                                    <div class="accordion-body p-3 bg-white">
                                        
                                        <!-- Keys Section (Flat List) -->
                                        <?php if (!empty($keys)): ?>
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-muted mb-2 ps-2 border-bottom pb-1"><i class="fas fa-key me-2"></i>Chaves</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <tbody>
                                                            <?php foreach ($keys as $item): ?>
                                                                <tr>
                                                                    <td class="ps-4" style="width: 50px;">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input item-checkbox item-of-<?php echo $collapseId; ?>" type="checkbox" name="ids[]" value="<?php echo $item['id']; ?>" style="cursor: pointer;">
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="fw-bold text-dark">
                                                                            <?php echo htmlspecialchars($item['description']); ?>
                                                                            <?php if (!empty($item['user_note'])): ?>
                                                                                <div class="small text-muted mt-1 fst-italic">
                                                                                    <i class="fas fa-comment fa-xs me-1"></i> "<?php echo htmlspecialchars($item['user_note']); ?>"
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></small>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                                                                    <td>
                                                                        <?php 
                                                                            $bg = $item['status'] == 'contested' ? 'danger' : ($item['status'] == 'user_informed' ? 'info' : 'warning');
                                                                            $labels = ['pending' => 'Pendente', 'user_informed' => 'Informado', 'contested' => 'Contestado'];
                                                                            echo '<span class="badge bg-'.$bg.'">'.($labels[$item['status']] ?? $item['status']).'</span>';
                                                                        ?>
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-sm btn-success text-white" onclick="submitSingleAction(<?php echo $item['id']; ?>, 'completed')" title="Confirmar Recebimento"><i class="fas fa-check"></i></button>
                                                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="openSingleContest(<?php echo $item['id']; ?>)"><i class="fas fa-times"></i></button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Categories Section (Nested Accordions) -->
                                        <?php if (!empty($categories)): ?>
                                            <?php $catIndex = 0; ?>
                                            <?php foreach ($categories as $catName => $catItems): ?>
                                                <?php 
                                                    $catIndex++;
                                                    $catCollapseId = $collapseId . "_cat_" . $catIndex;
                                                    $catCheckboxId = $collapseId . "_catCheck_" . $catIndex;
                                                ?>
                                                <div class="accordion-item border-0 mb-2">
                                                    <h2 class="accordion-header">
                                                        <div class="accordion-button collapsed bg-light p-2 ps-3 rounded" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $catCollapseId; ?>">
                                                            <div class="form-check me-3" onclick="event.stopPropagation();">
                                                                <input class="form-check-input category-checkbox item-of-<?php echo $collapseId; ?>" type="checkbox" id="<?php echo $catCheckboxId; ?>" data-target-group="<?php echo $catCollapseId; ?>" data-parent-group="<?php echo $collapseId; ?>" style="transform: scale(1.1); cursor: pointer;">
                                                            </div>
                                                            <span class="fw-bold text-secondary"><?php echo htmlspecialchars($catName); ?></span>
                                                            <span class="badge bg-secondary ms-2 rounded-pill"><?php echo count($catItems); ?></span>
                                                        </div>
                                                    </h2>
                                                    <div id="<?php echo $catCollapseId; ?>" class="accordion-collapse collapse show">
                                                        <div class="accordion-body p-0 pt-2">
                                                            <div class="table-responsive">
                                                                <table class="table table-hover align-middle mb-0 table-sm">
                                                                    <tbody>
                                                                        <?php foreach ($catItems as $item): ?>
                                                                            <tr>
                                                                                <td class="ps-4" style="width: 50px;">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input item-checkbox item-of-<?php echo $collapseId; ?> item-of-<?php echo $catCollapseId; ?>" type="checkbox" name="ids[]" value="<?php echo $item['id']; ?>" style="cursor: pointer;">
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="fw-bold text-dark">
                                                                                        <i class="fas fa-box-open text-info me-2"></i> <?php echo htmlspecialchars($item['description']); ?>
                                                                                        <?php if (!empty($item['user_note'])): ?>
                                                                                            <div class="small text-muted mt-1 fst-italic">
                                                                                                <i class="fas fa-comment fa-xs me-1"></i> "<?php echo htmlspecialchars($item['user_note']); ?>"
                                                                                            </div>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></small>
                                                                                </td>
                                                                                <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                                                                                 <td>
                                                                                    <?php 
                                                                                        $bg = $item['status'] == 'contested' ? 'danger' : ($item['status'] == 'user_informed' ? 'info' : 'warning');
                                                                                        $labels = ['pending' => 'Pendente', 'user_informed' => 'Informado', 'contested' => 'Contestado'];
                                                                                        echo '<span class="badge bg-'.$bg.'">'.($labels[$item['status']] ?? $item['status']).'</span>';
                                                                                    ?>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="btn-group">
                                                                                        <button type="button" class="btn btn-sm btn-success text-white" onclick="submitSingleAction(<?php echo $item['id']; ?>, 'completed')" title="Confirmar Recebimento"><i class="fas fa-check"></i></button>
                                                                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="openSingleContest(<?php echo $item['id']; ?>)"><i class="fas fa-times"></i></button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
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
            </div>
        </form> <!-- End Bulk Form -->
    </div>
</div>

<!-- History Section -->
<div class="mt-5 mb-4">
    <h4 class="text-secondary"><i class="fas fa-history me-2"></i>Histórico Completo</h4>
</div>
<div class="card border-0 shadow-sm bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Item</th>
                        <th>Evento</th>
                        <th>Responsável</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historyItems)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum histórico disponível.</td></tr>
                    <?php else: ?>
                        <?php foreach ($historyItems as $item): ?>
                             <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-muted">
                                        <?php if ($item['item_type'] == 'key'): ?>
                                            <i class="fas fa-key me-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-box-open me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['description']); ?>
                                        <?php if (!empty($item['user_note'])): ?>
                                            <div class="small text-muted mt-1 fst-italic">
                                                <i class="fas fa-comment fa-xs me-1"></i> "<?php echo htmlspecialchars($item['user_note']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></small>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($item['event_name']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($item['user_name']); ?></td>
                                <td><span class="badge bg-success">Concluído</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Contest Modal -->
<div class="modal fade" id="contestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/eventos/pending/updateStatus" method="POST" id="contestForm">
                <div class="modal-header">
                    <h5 class="modal-title">Contestar Devolução</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- IDs will be injected here -->
                    <div id="contestIdsContainer"></div>
                    
                    <input type="hidden" name="status" value="contested">
                    <div class="mb-3">
                        <label class="form-label">Motivo da Contestação</label>
                        <textarea name="observation" class="form-control" rows="3" required placeholder="Ex: Chave não entregue, Equipamento danificado..."></textarea>
                    </div>
                     <div class="alert alert-warning small d-none" id="bulkContestWarning">
                        <i class="fas fa-exclamation-triangle me-1"></i> Você está contestando <span id="bulkContestCount"></span> itens de uma vez. A mesma observação será aplicada a todos.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Salvar Contestação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkForm');
        
        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');

        // --- Logic Selection ---

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

        // 2. Select Event Group (Father)
        eventCheckboxes.forEach(ecb => {
             ecb.addEventListener('change', function() {
                 const isChecked = this.checked;
                 const targetId = this.getAttribute('data-target-group'); // collapseEventX
                 
                 // Items belonging to this Event (Direct items + Items in nested categories)
                 // Conveniently, all items (keys and categories) have 'item-of-collapseEventX' class.
                 const itemsInGroup = document.querySelectorAll('.item-of-' + targetId);
                 itemsInGroup.forEach(item => {
                     // If item is checkbox (item or category checkbox)
                     if (!item.disabled) { 
                        item.checked = isChecked;
                     }
                 });
                 
                 updateMasterState();
                 updateUI();
             });
        });

        // 3. Select Category Group (Child of Event, Parent of Items)
        categoryCheckboxes.forEach(ccb => {
            ccb.addEventListener('change', function() {
                const isChecked = this.checked;
                const targetId = this.getAttribute('data-target-group'); // catCollapseId
                
                const itemsInGroup = document.querySelectorAll('.item-of-' + targetId); // Only direct children items
                itemsInGroup.forEach(item => item.checked = isChecked);
                
                // Update Parent Event
                const parentGroupId = this.getAttribute('data-parent-group'); // collapseEventX
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
                 // Does it belong to a category?
                 const catClass = classes.find(c => c.startsWith('item-of-') && c.includes('_cat_'));
                 if (catClass) {
                     const catId = catClass.replace('item-of-', '');
                     updateCategoryState(catId);
                 }

                 // Belongs to Event?
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
             const itemsInGroup = document.querySelectorAll('.item-checkbox.item-of-' + collapseId);
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
             const checked = document.querySelectorAll('.item-checkbox:checked').length;
             
             selectAllCheckbox.checked = (total === checked && total > 0);
             selectAllCheckbox.indeterminate = (checked > 0 && checked < total);
        }

        function updateUI() {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            const count = checkedBoxes.length;
            
            if (selectedCountSpan) selectedCountSpan.textContent = count;
            
            if (bulkActions) {
                if (count > 0) {
                    bulkActions.classList.remove('d-none');
                } else {
                    bulkActions.classList.add('d-none');
                }
            }
        }
        
        // Expose functions to global scope for button onclicks
        window.submitBulkAction = function(status) {
            document.getElementById('bulkStatusInput').value = status;
            if (confirm('Tem certeza que deseja marcar os itens selecionados como Recebidos?')) {
                bulkForm.submit();
            }
        };

        window.submitSingleAction = function(id, status) {
            // Create a temporary form to submit single action
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/eventos/pending/updateStatus';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            
            form.appendChild(idInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }

        window.openSingleContest = function(id) {
            var contestModal = new bootstrap.Modal(document.getElementById('contestModal'));
            document.getElementById('contestIdsContainer').innerHTML = '<input type="hidden" name="id" value="' + id + '">';
            document.getElementById('bulkContestWarning').classList.add('d-none');
            contestModal.show();
        };

        window.openBulkContestModal = function() {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            var contestModal = new bootstrap.Modal(document.getElementById('contestModal'));
            const container = document.getElementById('contestIdsContainer');
            container.innerHTML = '';
            
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('bulkContestCount').textContent = checkedBoxes.length;
            document.getElementById('bulkContestWarning').classList.remove('d-none');
            
            contestModal.show();
        };
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
