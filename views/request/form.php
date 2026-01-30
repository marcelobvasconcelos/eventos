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

                <?php if (!empty($globalConfigs['request_info_text'])): ?>
                    <div class="alert alert-info border-info shadow-sm rounded-3 mb-4">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fa-2x me-3 mt-1 text-info"></i>
                            <div>
                                <?php echo $globalConfigs['request_info_text']; // Allow HTML ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/eventos/request/submit" class="row g-4" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold text-secondary">Imagem do Evento (Opcional)</label>
                        <input type="file" class="form-control bg-light" id="image" name="image" accept="image/*">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1 text-primary"></i> 
                            Recomendado: <strong>Formato Horizontal (ex: 1200x600px ou 16:9)</strong> para melhor visualização nos cards.
                            <br>Formatos aceitos: JPG, PNG, GIF, WEBP.
                        </div>
                    </div>

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
                        <label for="location" class="form-label fw-semibold text-secondary">
                            Localização 
                            <a href="/eventos/public/locations" target="_blank" class="small ms-2 text-decoration-none"><i class="fas fa-external-link-alt me-1"></i>Ver ficha técnica</a>
                        </label>
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
                                        $capacityText = !empty($location['capacity']) ? ' (Cap: ' . $location['capacity'] . ')' : '';
                                    ?>
                                    <option value="<?php echo $location['id']; ?>" <?php echo $selected; ?> <?php echo $disabledAttr; ?>>
                                        <?php echo htmlspecialchars($location['name']) . $occupiedText; ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="other" <?php echo (isset($_POST['location']) && $_POST['location'] == 'other') ? 'selected' : ''; ?>>Outros (Especifique)</option>
                            </select>
                        </div>
                        <div id="custom_location_div" class="mt-2 <?php echo (isset($_POST['location']) && $_POST['location'] == 'other') ? '' : 'd-none'; ?>">
                            <label for="custom_location" class="form-label small text-secondary">Nome do Local <span class="text-danger">*</span></label>
                            <input type="text" name="custom_location" id="custom_location" class="form-control bg-light" placeholder="Digite o nome do local..." value="<?php echo htmlspecialchars($_POST['custom_location'] ?? ''); ?>">
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const locSelect = document.getElementById('location');
                            const customDiv = document.getElementById('custom_location_div');
                            const customInput = document.getElementById('custom_location');

                            function toggleCustom() {
                                if (locSelect.value === 'other') {
                                    customDiv.classList.remove('d-none');
                                    customInput.required = true;
                                } else {
                                    customDiv.classList.add('d-none');
                                    customInput.required = false;
                                }
                            }
                            locSelect.addEventListener('change', toggleCustom);
                            // Initial check handled by PHP class output but robust JS init good too
                            toggleCustom();
                        });
                        </script>
                    </div>

                    <div class="col-md-6">
                         <label for="public_estimation" class="form-label fw-semibold text-secondary">Estimativa de Público</label>
                         <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-users"></i></span>
                            <input type="number" name="public_estimation" id="public_estimation" class="form-control border-start-0 ps-0 bg-light" placeholder="Ex: 50" min="1" value="<?php echo htmlspecialchars($_POST['public_estimation'] ?? ''); ?>" required>
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

                    <!-- External Link Section -->
                    <div class="col-md-6">
                        <label for="link_title" class="form-label fw-semibold text-secondary">Título do Link (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" name="link_title" id="link_title" class="form-control border-start-0 ps-0 bg-light" placeholder="Ex: Página do evento, Inscrição, Site Oficial" value="<?php echo htmlspecialchars($_POST['link_title'] ?? ''); ?>">
                        </div>
                        <div class="form-text">Nome que aparecerá no botão (Ex: "Inscrições").</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="external_link" class="form-label fw-semibold text-secondary">Link Externo (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-link"></i></span>
                            <input type="url" name="external_link" id="external_link" class="form-control border-start-0 ps-0 bg-light" placeholder="https://..." value="<?php echo htmlspecialchars($_POST['external_link'] ?? ''); ?>">
                        </div>
                        <div class="form-text">URL completa para onde o usuário será direcionado.</div>
                    </div>

    <div class="col-12">
        <label for="schedule_file" class="form-label fw-semibold text-secondary">Programação do Evento (Opcional)</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-file-alt"></i></span>
            <input type="file" class="form-control bg-light" id="schedule_file" name="schedule_file" accept=".pdf, .doc, .docx, .odt, .jpg, .jpeg, .png">
        </div>
        <div class="form-text">
            Formatos aceitos: PDF, DOCX, ODT, JPG, PNG. Anexe o cronograma ou programação detalhada.
        </div>
    </div>
    
    <div class="col-12">
        <label for="description" class="form-label fw-bold text-dark">Descrição Detalhada <span class="text-danger">*</span></label>
        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Descreva os detalhes do evento, como pauta, objetivos ou necessidades especiais..." required style="background-color: #fff; color: #000;"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
    </div>

                    <div class="col-12">
                        <div class="card bg-light border-0 rounded-3">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-secondary mb-3"><i class="fas fa-boxes me-2"></i>Equipamentos Necessários</h6>
                                
                                <?php
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
                                            <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                                <button class="accordion-button <?php echo $catIndex > 0 ? 'collapsed' : ''; ?> bg-light text-primary fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $catIndex === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo $collapseId; ?>">
                                                    <?php echo htmlspecialchars($categoryName); ?>
                                                    <span class="badge bg-primary rounded-pill ms-2"><?php echo count($categoryAssets); ?></span>
                                                </button>
                                            </h2>
                                            <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $catIndex === 0 ? 'show' : ''; ?>" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#assetsAccordion">
                                                <div class="accordion-body bg-white">
                                                    <div class="row g-3">
                                                        <?php foreach ($categoryAssets as $asset): ?>
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
                                    <?php 
                                    $catIndex++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Agreement Section -->
                    <div class="col-12 mt-4">
                        <div class="form-check p-3 bg-light border rounded">
                            <input class="form-check-input mt-1" type="checkbox" name="terms_agreement" id="terms_agreement" value="1" required>
                            <label class="form-check-label text-secondary" for="terms_agreement">
                                Li e concordo com a 
                                <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary text-decoration-underline fw-bold">
                                    ORIENTAÇÃO NORMATIVA SOBRE A REALIZAÇÃO DE EVENTOS DA UNIDADE ACADÊMICA DE SERRA TALHADA DA UNIVERSIDADE FEDERAL RURAL DE PERNAMBUCO E DÁ OUTRAS PROVIDÊNCIAS
                                </a>
                            </label>
                        </div>
                    </div>

                    <!-- Terms Modal -->
                    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="termsModalLabel">Orientação Normativa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <?php if (!empty($globalConfigs['normative_pdf'])): ?>
                                        <div class="ratio ratio-1x1" style="height: 70vh;">
                                            <iframe src="/eventos/<?php echo htmlspecialchars($globalConfigs['normative_pdf']); ?>#toolbar=0" title="Orientação Normativa" allowfullscreen></iframe>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4">
                                            <h6 class="fw-bold mb-3 text-center">ORIENTAÇÃO NORMATIVA SOBRE A REALIZAÇÃO DE EVENTOS DA UNIDADE ACADÊMICA DE SERRA TALHADA DA UNIVERSIDADE FEDERAL RURAL DE PERNAMBUCO E DÁ OUTRAS PROVIDÊNCIAS</h6>
                                            
                                            <div class="text-muted text-center py-5">
                                                <i class="fas fa-file-pdf fa-3x mb-3 text-secondary opacity-50"></i>
                                                <p>O documento PDF ainda não foi configurado pelo administrador.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
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