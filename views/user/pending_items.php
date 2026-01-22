<?php
$title = 'Minhas Pendências';
ob_start();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary"><i class="fas fa-clipboard-list me-2"></i>Minhas Pendências de Devolução</h2>
        <p class="text-muted">Gerencie a devolução de chaves e equipamentos dos seus eventos finalizados.</p>
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
<h4 class="mb-3 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Pendências Ativas</h4>
<?php if (empty($activeItems)): ?>
    <div class="alert alert-success shadow-sm rounded-3">
        <i class="fas fa-check-circle me-2"></i> Você não possui pendências de devolução no momento. Parabéns!
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($activeItems as $item): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-0" style="border-left: 5px solid <?php echo $item['status'] == 'contested' ? '#dc3545' : '#ffc107'; ?> !important;">
                    <div class="card-body position-relative">
                        <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                            <div class="position-absolute top-0 end-0 p-3">
                                <input class="form-check-input pending-checkbox" type="checkbox" value="<?php echo $item['id']; ?>" data-desc="<?php echo htmlspecialchars($item['description']); ?>" style="transform: scale(1.3);">
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title fw-bold text-dark mb-0">
                                <?php if ($item['item_type'] == 'key'): ?>
                                    <i class="fas fa-key text-warning me-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-box-open text-info me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['description']); ?>
                            </h5>
                            <?php 
                                $statusBadges = [
                                    'pending' => '<span class="badge bg-warning text-dark">Pendente</span>',
                                    'user_informed' => '<span class="badge bg-info text-dark">Aguardando Conferência</span>',
                                    'contested' => '<span class="badge bg-danger">Contestado</span>',
                                    'completed' => '<span class="badge bg-success">Concluído</span>'
                                ];
                                echo $statusBadges[$item['status']] ?? $item['status'];
                            ?>
                        </div>
                        
                        <p class="mb-2"><small class="text-muted">Evento:</small> <strong><?php echo htmlspecialchars($item['event_name']); ?></strong></p>
                        <p class="mb-3"><small class="text-muted">Data do Evento:</small> <?php echo date('d/m/Y', strtotime($item['event_date'])); ?></p>
                        
                        <?php if ($item['observation']): ?>
                            <div class="alert alert-danger bg-opacity-10 py-2 px-3 small">
                                <strong>Observação do Admin:</strong> <?php echo htmlspecialchars($item['observation']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($item['status'] == 'pending' || $item['status'] == 'contested'): ?>
                            <button type="button" 
                                    class="btn btn-primary w-100 btn-sm rounded-pill"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#returnModal"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-desc="<?php echo htmlspecialchars($item['description']); ?>">
                                <i class="fas fa-check me-2"></i>Informar Devolução
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 btn-sm rounded-pill" disabled>
                                <i class="fas fa-clock me-2"></i>Aguardando Confirmação
                            </button>
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
                <div class="card shadow-sm h-100 border-0 bg-light opacity-75">
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
<div class="position-fixed bottom-0 end-0 p-4 mb-3" style="z-index: 1030; display: none;" id="batchReturnFab">
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
    var returnModal = document.getElementById('returnModal');
    var batchFab = document.getElementById('batchReturnFab');
    var selectedCountSpan = document.getElementById('selectedCount');
    var checkboxes = document.querySelectorAll('.pending-checkbox');
    var modalInputsContainer = document.getElementById('modalInputsContainer');
    var modalSingleDisplay = document.getElementById('singleItemDisplay');
    var modalBatchDisplay = document.getElementById('batchItemDisplay');
    var modalBatchList = document.getElementById('batchItemList');
    var modalItemDesc = document.getElementById('returnItemDesc');

    // Checkbox Logic
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchUI);
    });

    function updateBatchUI() {
        const selected = document.querySelectorAll('.pending-checkbox:checked');
        const count = selected.length;
        selectedCountSpan.textContent = count;
        
        if (count > 0) {
            batchFab.style.display = 'block';
        } else {
            batchFab.style.display = 'none';
        }
    }

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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
