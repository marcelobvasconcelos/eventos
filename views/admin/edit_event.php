<?php
$title = 'Editar Evento';
ob_start();
?>
<h1>
    Editar Evento
    <a href="/eventos/admin/printEvent?id=<?php echo $event['id']; ?>" target="_blank" class="btn btn-outline-dark float-end btn-sm no-print">
        <i class="fas fa-print me-2"></i>Imprimir Relatório
    </a>
</h1>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form method="POST" action="/eventos/admin/updateEvent" class="row g-3" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
    <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($returnUrl ?? '/eventos/admin/events'); ?>">
    
    <div class="col-12">
        <label for="image" class="form-label fw-bold">Imagem de Capa</label>
        <?php if (!empty($event['image_path'])): ?>
            <div class="mb-2">
                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="Imagem Atual" class="img-thumbnail" style="max-height: 150px;">
            </div>
        <?php endif; ?>
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
        <div class="form-text">Deixe vazio para manter a imagem atual.</div>
    </div>

    <div class="col-md-6">
        <label for="name" class="form-label">Título</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($event['name']); ?>" required>
    </div>
    <div class="col-md-3">
        <label for="date" class="form-label">Data de Início</label>
        <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($event['date']))); ?>" required>
    </div>
    <div class="col-md-3">
        <label for="time" class="form-label">Hora de Início</label>
        <input type="time" name="time" id="time" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($event['date']))); ?>" required>
    </div>
    
    <div class="col-md-3">
        <label for="end_date" class="form-label">Data de Término</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo !empty($event['end_date']) ? htmlspecialchars(date('Y-m-d', strtotime($event['end_date']))) : ''; ?>">
    </div>
    <div class="col-md-3">
        <label for="end_time" class="form-label">Hora de Término</label>
        <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo !empty($event['end_date']) ? htmlspecialchars(date('H:i', strtotime($event['end_date']))) : ''; ?>">
    </div>
    <div class="col-md-6">
        <label for="location" class="form-label">Localização</label>
        <select name="location" id="location" class="form-control">
            <option value="">Selecione um local</option>
            <?php foreach ($locations as $location): ?>
                <?php 
                    $isOccupied = !empty($location['is_occupied']);
                    $label = htmlspecialchars($location['name']);
                    if ($isOccupied) $label .= ' (OCUPADO)';
                    $style = $isOccupied ? 'color: #dc3545; font-weight: 600;' : '';
                ?>
                <option value="<?php echo $location['id']; ?>" <?php echo ($event['location_id'] == $location['id']) ? 'selected' : ''; ?> style="<?php echo $style; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="category" class="form-label">Categoria</label>
        <select name="category" id="category" class="form-control">
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($event['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" required>
            <option value="Pendente" <?php echo ($event['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
            <option value="Aprovado" <?php echo ($event['status'] == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
            <option value="Rejeitado" <?php echo ($event['status'] == 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
            <option value="Concluido" <?php echo ($event['status'] == 'Concluido') ? 'selected' : ''; ?>>Concluido</option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="is_public" class="form-label">Visibilidade</label>
        <select name="is_public" id="is_public" class="form-select" required>
            <option value="1" <?php echo (isset($event['is_public']) && $event['is_public'] == 1) ? 'selected' : ''; ?>>Público</option>
            <option value="0" <?php echo (isset($event['is_public']) && $event['is_public'] == 0) ? 'selected' : ''; ?>>Privado</option>
        </select>
    </div>
    
    <div class="col-md-6">
        <label for="link_title" class="form-label">Título do Link (Opcional)</label>
        <input type="text" name="link_title" id="link_title" class="form-control" value="<?php echo htmlspecialchars($event['link_title'] ?? ''); ?>" placeholder="Ex: Inscrições">
    </div>
    <div class="col-md-6">
        <label for="external_link" class="form-label">Link Externo (Opcional)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-link"></i></span>
            <input type="url" name="external_link" id="external_link" class="form-control" value="<?php echo htmlspecialchars($event['external_link'] ?? ''); ?>" placeholder="https://...">
        </div>
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Descrição</label>
        <textarea name="description" id="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
    </div>

    <!-- Asset Selection Section -->
    <div class="col-12 mt-4">
        <h5 class="mb-3">Equipamentos Solicitados</h5>
        <div class="card bg-light border-0">
            <div class="card-body">
                <?php if (empty($allAssets)): ?>
                    <p class="text-muted mb-0">Nenhum equipamento cadastrado no sistema.</p>
                <?php else: ?>
                    <?php
                    // Group assets by category
                    $assetsByCategory = [];
                    foreach ($allAssets as $asset) {
                        $catName = $asset['category_name'] ?? 'Outros';
                        if (empty($catName)) $catName = 'Outros';
                        $assetsByCategory[$catName][] = $asset;
                    }
                    ?>
                    
                    <div class="accordion" id="assetsAccordion">
                        <?php 
                        $catIndex = 0;
                        foreach ($assetsByCategory as $categoryName => $categoryAssets): 
                            $collapseId = "collapseCat" . $catIndex;
                            $headingId = "headingCat" . $catIndex;
                            // Check if any asset in this category has selected qty > 0 to expand automatically (optional)
                            $hasSelection = false;
                            foreach($categoryAssets as $a) {
                                if (($currentAssets[$a['id']] ?? 0) > 0) $hasSelection = true;
                            }
                        ?>
                            <div class="accordion-item mb-2 border rounded overflow-hidden">
                                <h2 class="accordion-header" id="<?= $headingId; ?>">
                                    <button class="accordion-button <?= !$hasSelection ? 'collapsed' : ''; ?> bg-white text-dark fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>" aria-expanded="<?= $hasSelection ? 'true' : 'false'; ?>" aria-controls="<?= $collapseId; ?>">
                                        <?= htmlspecialchars($categoryName); ?>
                                        <span class="badge bg-secondary rounded-pill ms-2"><?= count($categoryAssets); ?></span>
                                    </button>
                                </h2>
                                <div id="<?= $collapseId; ?>" class="accordion-collapse collapse <?= $hasSelection ? 'show' : ''; ?>" aria-labelledby="<?= $headingId; ?>" data-bs-parent="#assetsAccordion">
                                    <div class="accordion-body bg-light">
                                        <div class="row g-3">
                                            <?php foreach ($categoryAssets as $asset): ?>
                                                <?php 
                                                    $currentQty = $currentAssets[$asset['id']] ?? 0;
                                                    $available = $asset['available_count'] ?? 0;
                                                    $maxQty = $available;
                                                    $isAvailable = $maxQty > 0;
                                                    $isSelected = $currentQty > 0;
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-white <?php echo (!$isAvailable && $currentQty == 0) ? 'opacity-50' : ''; ?>">
                                                        <div class="form-check mb-0 flex-grow-1">
                                                            <input class="form-check-input asset-checkbox" type="checkbox" 
                                                                   id="asset_check_<?php echo $asset['id']; ?>" 
                                                                   data-target="#asset_qty_<?php echo $asset['id']; ?>"
                                                                   <?php echo $isSelected ? 'checked' : ''; ?>
                                                                   <?php echo (!$isAvailable && $currentQty == 0) ? 'disabled' : ''; ?>>
                                                            <label class="form-check-label d-block user-select-none" for="asset_check_<?php echo $asset['id']; ?>">
                                                                <?php echo htmlspecialchars($asset['name']); ?>
                                                                <div class="small text-muted" style="font-size: 0.8em;">Disp: <?php echo $maxQty; ?></div>
                                                            </label>
                                                        </div>
                                                        <div style="width: 80px;">
                                                            <!-- Note: We must send 0 if unchecked so AdminController removes loans. 
                                                                 We use readonly instead of disabled so value is sent. 
                                                                 JS will toggle readonly and value. -->
                                                            <input type="number" 
                                                                   name="assets[<?php echo $asset['id']; ?>]" 
                                                                   id="asset_qty_<?php echo $asset['id']; ?>"
                                                                   class="form-control form-control-sm text-center asset-quantity" 
                                                                   value="<?php echo $currentQty; ?>" 
                                                                   min="0" 
                                                                   max="<?php echo $maxQty; ?>"
                                                                   <?php echo $isSelected ? '' : 'readonly tabIndex="-1" style="background-color: #e9ecef;"'; ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        $catIndex++;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="<?php echo htmlspecialchars($returnUrl ?? '/eventos/admin/events'); ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<script>
    // Feedback for Occupied Location
    const locSelect = document.getElementById('location');
    if (locSelect) {
        locSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            // Check if label contains (OCUPADO)
            if (selectedOption.text.includes('(OCUPADO)')) {
                alert('Atenção: O local selecionado está marcado como OCUPADO para o horário atual do evento. Se você não alterar o horário para um período livre, a gravação será bloqueada.');
            }
        });
    }

    // Asset Selection Logic
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('asset-checkbox')) {
            const checkbox = e.target;
            const targetId = checkbox.getAttribute('data-target');
            const input = document.querySelector(targetId);
            
            if (checkbox.checked) {
                input.readOnly = false;
                input.tabIndex = 0;
                input.style.backgroundColor = '#fff';
                if (input.value == 0) input.value = 1;
                input.focus();
            } else {
                input.value = 0;
                input.readOnly = true;
                input.tabIndex = -1;
                input.style.backgroundColor = '#e9ecef';
            }
        }
    });

    // Validations (Start vs End Date)
    const form = document.querySelector('form');
    // ... existing validation if any ...
    const startInput = document.getElementById('date');
    const startTimeInput = document.getElementById('time');
    const endInput = document.getElementById('end_date');
    const endTimeInput = document.getElementById('end_time');

    if (form && startInput && startTimeInput) {
         form.addEventListener('submit', function(e) {
            const startDate = startInput.value;
            const startTime = startTimeInput.value;
            if (!startDate || !startTime) return;

            const start = new Date(startDate + 'T' + startTime);
            
            let endDateVal = endInput && endInput.value ? endInput.value : startDate;
            let endTimeVal = endTimeInput && endTimeInput.value ? endTimeInput.value : '23:59';
            
            // If end time is empty, maybe don't validate or assume valid?
            if (endTimeInput && !endTimeInput.value) {
                // If end date is set but no time? 
                return; 
            }
            
            const end = new Date(endDateVal + 'T' + endTimeVal);
            
            if (end <= start) {
                e.preventDefault();
                alert('A data e hora de término devem ser posteriores ao início.');
            }
         });
    }
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>