<?php
$title = 'Criar Evento';
ob_start();
?>
<h1>Criar Evento</h1>
<?php if (isset($errorMessages)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessages); ?></div>
<?php endif; ?>
<form method="POST" action="/eventos/public/create" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
    <div class="col-md-6">
        <label for="name" class="form-label">Título</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="date" class="form-label">Data Início</label>
        <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label">Data Término <span class="text-muted small fw-normal">(se evento durar mais de um dia)</span></label>
        <input type="date" name="end_date" id="end_date_input" class="form-control" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" placeholder="Se vazio, será igual a data de início">
        <div class="form-text">Deixe em branco p/ evento de 1 dia.</div>
    </div>
    <div class="col-md-3">
        <label for="time" class="form-label">Hora Início</label>
        <input type="time" name="time" id="time" class="form-control" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>" required>
    </div>
    <div class="col-md-3">
        <label for="end_time" class="form-label">Hora Término</label>
        <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="location" class="form-label">Localização</label>
        <select name="location" id="location" class="form-control" required>
            <option value="">Selecione um local</option>
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
    <div class="col-md-6">
        <label for="category" class="form-label">Categoria</label>
        <select name="category" id="category" class="form-control" required>
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category']) && $_POST['category'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Descrição</label>
        <textarea name="description" id="description" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Itens Necessários (opcional)</label>
        <div class="row">
            <?php foreach ($assets as $asset): ?>
                <div class="col-md-6">
                    <div class="form-check p-3 bg-white rounded border shadow-sm h-100 position-relative <?php echo ($asset['available_count'] <= 0) ? 'opacity-50' : ''; ?>">
                         <input class="form-check-input ms-0 me-2" type="checkbox" name="assets[]" value="<?php echo $asset['id']; ?>" id="asset_<?php echo $asset['id']; ?>" <?php echo ($asset['available_count'] <= 0) ? 'disabled' : ''; ?> onchange="document.getElementById('qty_<?php echo $asset['id']; ?>').disabled = !this.checked; if(this.checked) document.getElementById('qty_<?php echo $asset['id']; ?>').focus();">
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
                            <input type="number" name="quantities[<?php echo $asset['id']; ?>]" id="qty_<?php echo $asset['id']; ?>" class="form-control form-control-sm d-inline-block w-auto ms-1" value="1" min="1" max="<?php echo $asset['available_count']; ?>" disabled onclick="event.stopPropagation()">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Criar Evento</button>
        <a href="/eventos/public/calendar" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

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

        if (!date || !time) return; // Need at least start date and time

        // Default end date to start date if not set
        const effectiveEndDate = endDateRaw || date;
        // Default end time logic
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
                    const originalText = option.text.replace(' (Ocupado neste horário)', ''); 

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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>