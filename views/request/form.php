<?php
$title = 'Enviar Solicitação de Evento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white py-4 border-0 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-calendar-plus fa-2x"></i>
                </div>
                <h2 class="fw-bold text-primary mb-1">Nova Solicitação de Evento</h2>
                <p class="text-muted mb-0">Preencha os detalhes abaixo para agendar seu evento</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <?php if (isset($errorMessages)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($errorMessages); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/eventos/request/submit" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-secondary">Título do Evento</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-heading"></i></span>
                            <input type="text" name="title" id="title" class="form-control border-start-0 ps-0 bg-light" placeholder="Ex: Reunião de Departamento" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="date" class="form-label fw-semibold text-secondary">Data Início</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-calendar"></i></span>
                            <input type="date" name="date" id="date" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-semibold text-secondary">Data Término <span class="small fw-normal">(se evento durar mais de um dia)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-calendar-check"></i></span>
                            <input type="date" name="end_date" id="end_date_input" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" placeholder="Se vazio, igual a data início">
                        </div>
                        <div class="form-text">Deixe em branco p/ evento de 1 dia.</div>
                    </div>

                    <div class="col-md-3">
                        <label for="time" class="form-label fw-semibold text-secondary">Hora Início</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-clock"></i></span>
                            <input type="time" name="time" id="time" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_time" class="form-label fw-semibold text-secondary">Hora Término</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-hourglass-end"></i></span>
                            <input type="time" name="end_time" id="end_time" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                        </div>
                    </div>

<!-- ... skipped ... -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const endDateInput = document.getElementById('end_date_input');
    const timeInput = document.getElementById('time');
    const endTimeInput = document.getElementById('end_time');
    const locationSelect = document.getElementById('location');

    function checkAvailability() {
        const date = dateInput.value;
        const endDateRaw = endDateInput.value;
        const time = timeInput.value;
        const endTime = endTimeInput.value;

        if (!date || !time) return;

        const effectiveEndDate = endDateRaw || date;
        const effectiveEndTime = endTime || '23:59';
        
        const startDateTime = `${date} ${time}`;
        const endDateTime = `${effectiveEndDate} ${effectiveEndTime}`;

        fetch(`/eventos/api/check_locations.php?start_date=${encodeURIComponent(startDateTime)}&end_date=${encodeURIComponent(endDateTime)}`)
            .then(response => response.json())
            .then(data => {
                const occupancyMap = new Map(data.map(loc => [loc.id, loc.is_occupied]));

                Array.from(locationSelect.options).forEach(option => {
                    if (option.value === "") return;

                    const isOccupied = occupancyMap.get(parseInt(option.value));
                    const originalText = option.text.replace(/ \(Ocupado neste horário\)$/, '');

                    if (isOccupied) {
                        option.disabled = true;
                        option.text = originalText + ' (Ocupado neste horário)';
                    } else {
                        option.disabled = false;
                        option.text = originalText;
                    }
                });
            })
            .catch(error => console.error('Error checking availability:', error));
    }

    dateInput.addEventListener('change', checkAvailability);
    endDateInput.addEventListener('change', checkAvailability);
    timeInput.addEventListener('change', checkAvailability);
    endTimeInput.addEventListener('change', checkAvailability);
    
    // Initial check
    if (dateInput.value && timeInput.value) {
        checkAvailability();
    }
});
</script>

                    <div class="col-md-6">
                        <label for="location" class="form-label fw-semibold text-secondary">Localização</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-map-marker-alt"></i></span>
                            <select name="location" id="location" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="">Selecione um local...</option>
                                <?php foreach ($locations as $location): ?>
                                    <?php 
                                        $isOccupied = !empty($location['is_occupied']);
                                        $disabledAttr = $isOccupied ? 'disabled' : '';
                                        $occupiedText = $isOccupied ? ' (Ocupado neste horário)' : '';
                                        $selected = (isset($_POST['location']) && $_POST['location'] == $location['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $location['id']; ?>" <?php echo $selected; ?> <?php echo $disabledAttr; ?>><?php echo htmlspecialchars($location['name']) . $occupiedText; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label fw-semibold text-secondary">Categoria</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                            <select name="category" id="category" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="">Selecione uma categoria...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-secondary">Visibilidade do Evento</label>
                        <div class="d-flex gap-3">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="is_public" id="public_yes" value="1" <?php echo (!isset($_POST['is_public']) || $_POST['is_public'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="public_yes">
                                    <i class="fas fa-globe-americas me-2 text-primary"></i>
                                    <strong>Público</strong>
                                    <small class="d-block text-muted">Todos podem ver os detalhes (Título, Descrição, etc).</small>
                                </label>
                            </div>
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="is_public" id="public_no" value="0" <?php echo (isset($_POST['is_public']) && $_POST['is_public'] == '0') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="public_no">
                                    <i class="fas fa-lock me-2 text-danger"></i>
                                    <strong>Privado</strong>
                                    <small class="d-block text-muted">Apenas horário e local visíveis. Detalhes ocultos.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold text-secondary">Descrição Detalhada</label>
                        <textarea name="description" id="description" class="form-control bg-light" rows="4" placeholder="Descreva os detalhes do evento..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="card bg-light border-0 rounded-3">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-secondary mb-3"><i class="fas fa-boxes me-2"></i>Equipamentos Necessários</h6>
                                <div class="row g-3">
                                    <?php foreach ($assets as $asset): ?>
                                        <div class="col-md-6">
                                            <div class="form-check p-3 bg-white rounded border shadow-sm h-100 position-relative <?php echo ($asset['available_count'] <= 0) ? 'opacity-50' : ''; ?>">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="assets[]" value="<?php echo $asset['id']; ?>" id="asset_<?php echo $asset['id']; ?>" 
                                                <?php 
                                                $checked = false;
                                                if (isset($_POST['assets']) && in_array($asset['id'], $_POST['assets'])) {
                                                    $checked = true;
                                                } elseif (isset($_GET['asset_id']) && $_GET['asset_id'] == $asset['id']) {
                                                    $checked = true;
                                                }
                                                echo $checked ? 'checked' : '';
                                                ?>
                                                <?php echo ($asset['available_count'] <= 0) ? 'disabled' : ''; ?>
                                                onchange="document.getElementById('qty_<?php echo $asset['id']; ?>').disabled = !this.checked; if(this.checked) document.getElementById('qty_<?php echo $asset['id']; ?>').focus();">
                                                
                                                <label class="form-check-label w-100" for="asset_<?php echo $asset['id']; ?>">
                                                    <span class="fw-medium"><?php echo htmlspecialchars($asset['name']); ?></span>
                                                    <br>
                                                    <?php if ($asset['available_count'] > 0): ?>
                                                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Disponível: <?php echo $asset['available_count']; ?></small>
                                                    <?php else: ?>
                                                        <small class="text-danger"><i class="fas fa-times-circle me-1"></i>Esgotado</small>
                                                    <?php endif; ?>
                                                </label>
                                                <div class="mt-2" style="position: relative; z-index: 2;">
                                                    <label class="small text-muted">Quantidade:</label>
                                                    <input type="number" name="quantities[<?php echo $asset['id']; ?>]" id="qty_<?php echo $asset['id']; ?>" class="form-control form-control-sm d-inline-block w-auto ms-1" value="<?php echo htmlspecialchars($_POST['quantities'][$asset['id']] ?? 1); ?>" min="1" max="<?php echo $asset['available_count']; ?>" <?php echo $checked ? '' : 'disabled'; ?> onclick="event.stopPropagation()">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/eventos/public/calendar" class="btn btn-outline-secondary px-4 rounded-pill">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm"><i class="fas fa-paper-plane me-2"></i>Enviar Solicitação</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');
    const endTimeInput = document.getElementById('end_time');
    const locationSelect = document.getElementById('location');

    function checkAvailability() {
        const date = dateInput.value;
        const time = timeInput.value;
        const endTime = endTimeInput.value;

        if (!date || !time) return;

        const effectiveEndTime = endTime || '23:59';
        const startDateTime = `${date} ${time}`;
        const endDateTime = `${date} ${effectiveEndTime}`;

        fetch(`/eventos/api/check_locations.php?start_date=${encodeURIComponent(startDateTime)}&end_date=${encodeURIComponent(endDateTime)}`)
            .then(response => response.json())
            .then(data => {
                const occupancyMap = new Map(data.map(loc => [loc.id, loc.is_occupied]));

                Array.from(locationSelect.options).forEach(option => {
                    if (option.value === "") return;

                    const isOccupied = occupancyMap.get(parseInt(option.value));
                    // Regex to robustly remove existing occupied text if present
                    const originalText = option.text.replace(/ \(Ocupado neste horário\)$/, '');

                    if (isOccupied) {
                        option.disabled = true;
                        option.text = originalText + ' (Ocupado neste horário)';
                    } else {
                        option.disabled = false;
                        option.text = originalText;
                    }
                });
            })
            .catch(error => console.error('Error checking availability:', error));
    }

    dateInput.addEventListener('change', checkAvailability);
    timeInput.addEventListener('change', checkAvailability);
    endTimeInput.addEventListener('change', checkAvailability);
    
    // Initial check
    if (dateInput.value && timeInput.value) {
        checkAvailability();
    }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';