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

<form method="POST" action="/eventos/admin/updateEvent" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
    <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($returnUrl ?? '/eventos/admin/events'); ?>">
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
                    <div class="row g-3">
                        <?php foreach ($allAssets as $asset): ?>
                            <?php 
                                $currentQty = $currentAssets[$asset['id']] ?? 0;
                                $available = $asset['available_count'] ?? 0;
                                // Since we excluded this event's loans in the controller, 
                                // $available represents the TOTAL items we can have (inclusive of what we already have).
                                $maxQty = $available;
                                $isAvailable = $maxQty > 0;
                            ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="d-flex align-items-center justify-content-between p-2 bg-white rounded shadow-sm border <?php echo (!$isAvailable && $currentQty == 0) ? 'opacity-50' : ''; ?>">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($asset['name']); ?></div>
                                        <div class="small text-muted">Disponível Total: <?php echo $maxQty; ?></div>
                                    </div>
                                    <div style="width: 80px;">
                                        <input type="number" 
                                               name="assets[<?php echo $asset['id']; ?>]" 
                                               class="form-control form-control-sm text-center" 
                                               value="<?php echo $currentQty; ?>" 
                                               min="0" 
                                               max="<?php echo $maxQty; ?>"
                                               <?php echo (!$isAvailable && $currentQty == 0) ? 'disabled' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>