<?php
$title = 'Criar Evento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white py-4 border-0 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-calendar-plus fa-2x"></i>
                </div>
                <h2 class="fw-bold text-primary mb-1">Criar Novo Evento</h2>
                <p class="text-muted mb-0">Preencha os detalhes do evento abaixo</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <?php if (isset($errorMessages)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($errorMessages); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($globalConfigs['event_creation_info_text'])): ?>
                    <div class="alert alert-info border-info shadow-sm rounded-3 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fa-2x me-3 mt-1 text-info"></i>
                            <div>
                                <?php echo $globalConfigs['event_creation_info_text']; // Allow HTML ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/eventos/public/create" class="row g-4" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
                    
                    <!-- Section: Basic Info -->
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3 text-secondary">
                            <i class="fas fa-info-circle me-2"></i>Informações Básicas
                        </h5>
                    </div>

                    <div class="col-12">
                        <label for="image" class="form-label fw-bold text-secondary">Imagem do Evento (Opcional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Formatos aceitos: JPG, PNG, GIF, WEBP.</div>
                    </div>

                    <div class="col-md-12">
                        <label for="name" class="form-label fw-semibold text-secondary">Título do Evento</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Digite o nome do evento" required>
                    </div>

                    <div class="col-md-6">
                        <label for="date" class="form-label fw-semibold text-secondary">Data Início</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-semibold text-secondary">Data Término</label>
                        <input type="date" name="end_date" id="end_date_input" class="form-control" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                        <div class="form-text small">Deixe em branco para eventos de um dia.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="time" class="form-label fw-semibold text-secondary">Hora Início</label>
                        <input type="time" name="time" id="time" class="form-control" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="end_time" class="form-label fw-semibold text-secondary">Hora Término</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                    </div>

                    <!-- Section: Location -->
                    <div class="col-12 mt-4">
                        <h5 class="border-bottom pb-2 mb-3 text-secondary">
                            <i class="fas fa-map-marker-alt me-2"></i>Localização
                        </h5>
                    </div>

                    <div class="col-12">
                        <label for="location" class="form-label fw-semibold text-secondary">
                            Localização 
                            <a href="/eventos/public/locations" target="_blank" class="small ms-2 text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i>Ver ficha técnica
                            </a>
                        </label>
                        <select name="location" id="location" class="form-select" required>
                            <option value="">Selecione um local</option>
                            <?php foreach ($locations as $location): ?>
                                <?php 
                                    $isOccupied = !empty($location['is_occupied']);
                                    $disabledAttr = $isOccupied ? 'disabled' : '';
                                    $occupiedText = $isOccupied ? ' (Ocupado neste horário)' : '';
                                    
                                    // Add capacity info
                                    $capacityText = isset($location['capacity']) ? " (Cap: {$location['capacity']})" : "";
                                    
                                    $selected = (isset($_POST['location']) && $_POST['location'] == $location['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo $selected; ?> <?php echo $disabledAttr; ?>>
                                    <?php echo htmlspecialchars($location['name']) . $capacityText . $occupiedText; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="availability-message" class="form-text mt-2"></div>
                    </div>

                    <!-- Section: Assets -->
                    <div class="col-12 mt-4">
                        <h5 class="border-bottom pb-2 mb-3 text-secondary">
                            <i class="fas fa-boxes me-2"></i>Recursos e Ativos
                        </h5>
                    </div>

                    <div class="col-12">
                        <p class="text-muted small mb-3">Selecione os itens necessários para o seu evento:</p>
                        
                        <?php
                        // Group assets by category
                        $assetsByCategory = [];
                        foreach ($assets as $asset) {
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
                            ?>
                                <div class="accordion-item mb-2 border rounded overflow-hidden">
                                    <h2 class="accordion-header" id="<?= $headingId; ?>">
                                        <button class="accordion-button <?= $catIndex > 0 ? 'collapsed' : ''; ?> bg-light text-primary fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId; ?>" aria-expanded="<?= $catIndex === 0 ? 'true' : 'false'; ?>" aria-controls="<?= $collapseId; ?>">
                                            <?= htmlspecialchars($categoryName); ?>
                                            <span class="badge bg-primary rounded-pill ms-2"><?= count($categoryAssets); ?></span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId; ?>" class="accordion-collapse collapse <?= $catIndex === 0 ? 'show' : ''; ?>" aria-labelledby="<?= $headingId; ?>" data-bs-parent="#assetsAccordion">
                                        <div class="accordion-body bg-white">
                                            <div class="row g-3">
                                                <?php foreach ($categoryAssets as $asset): ?>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center justify-content-between p-2 rounded border hover-bg-light">
                                                            <div class="form-check mb-0 flex-grow-1">
                                                                <input class="form-check-input asset-checkbox" type="checkbox" 
                                                                    name="assets[<?php echo $asset['id']; ?>][selected]" 
                                                                    id="asset_<?php echo $asset['id']; ?>" 
                                                                    value="1"
                                                                    <?php echo isset($_POST['assets'][$asset['id']]['selected']) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label d-block user-select-none" for="asset_<?php echo $asset['id']; ?>">
                                                                    <?php echo htmlspecialchars($asset['name']); ?>
                                                                    <?php if (!empty($asset['description'])): ?>
                                                                        <small class="d-block text-muted" style="font-size: 0.8em;"><?php echo htmlspecialchars($asset['description']); ?></small>
                                                                    <?php endif; ?>
                                                                    <div class="text-xs text-info mt-1">Disp: <?php echo $asset['available_quantity']; ?></div>
                                                                </label>
                                                            </div>
                                                            <div class="ms-3" style="width: 80px;">
                                                                <input type="number" 
                                                                    class="form-control form-control-sm asset-quantity" 
                                                                    name="assets[<?php echo $asset['id']; ?>][quantity]" 
                                                                    min="1" 
                                                                    max="<?php echo $asset['available_quantity']; ?>"
                                                                    value="<?php echo isset($_POST['assets'][$asset['id']]['quantity']) ? intval($_POST['assets'][$asset['id']]['quantity']) : 1; ?>"
                                                                    <?php echo isset($_POST['assets'][$asset['id']]['selected']) ? '' : 'disabled'; ?>>
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
                    </div>

                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Criar Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa;
}
.text-xs {
    font-size: 0.75rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable quantity inputs based on checkbox
    const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
    assetCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const quantityInput = this.closest('.d-flex').querySelector('.asset-quantity');
            quantityInput.disabled = !this.checked;
            if (this.checked && !quantityInput.value) {
                quantityInput.value = 1;
            }
        });
    });

    // Validations (Start vs End Date)
    const form = document.querySelector('form');
    const startInput = document.getElementById('date');
    const endInput = document.getElementById('end_date_input');
    const startTimeInput = document.getElementById('time');
    const endTimeInput = document.getElementById('end_time');

    form.addEventListener('submit', function(e) {
        const start = new Date(startInput.value + 'T' + startTimeInput.value);
        
        // Handle optional end date by assuming start date if empty
        let endDateValue = endInput.value || startInput.value;
        const end = new Date(endDateValue + 'T' + endTimeInput.value);

        if (end <= start) {
            e.preventDefault();
            alert('A data e hora de término devem ser posteriores ao início.');
        }
    });

    // Check location availability details
    // Note: Since we don't have a JSON endpoint for realtime checks in this snapshot, 
    // we primarily rely on server-side checks. The UI logic here is for UX enhancement.
    const locationSelect = document.getElementById('location');
    
    // We can keep the event listeners ready for future availability checking logic
    function checkAvailability() {
        // Placeholder for future AJAX check
    }

    [locationSelect, startInput, endInput, startTimeInput, endTimeInput].forEach(el => {
        el.addEventListener('change', checkAvailability);
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>