<?php
$title = 'Editar Solicitação de Evento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white py-4 border-0 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-edit fa-2x"></i>
                </div>
                <h2 class="fw-bold text-dark mb-1">Editar Solicitação</h2>
                <p class="text-muted mb-0">As alterações em eventos aprovados passarão por nova análise</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="/eventos/request/update" class="row g-4" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                    
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold text-secondary">Imagem do Evento (Opcional)</label>
                        <?php if (!empty($event['image_path'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="Atual" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control bg-light" id="image" name="image" accept="image/*">
                    </div>

                    <div class="col-12">
                        <label for="title" class="form-label fw-semibold text-secondary">Título do Evento</label>
                        <input type="text" name="title" id="title" class="form-control bg-light" value="<?php echo htmlspecialchars($event['name']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="date" class="form-label fw-semibold text-secondary">Data do Evento</label>
                        <input type="date" name="date" id="date" class="form-control bg-light" value="<?php echo date('Y-m-d', strtotime($event['date'])); ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label for="start_time" class="form-label fw-semibold text-secondary">Hora Início</label>
                        <input type="time" name="start_time" id="start_time" class="form-control bg-light" value="<?php echo date('H:i', strtotime($event['start_time'])); ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_time" class="form-label fw-semibold text-secondary">Hora Término</label>
                        <input type="time" name="end_time" id="end_time" class="form-control bg-light" value="<?php echo date('H:i', strtotime($event['end_time'])); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label fw-semibold text-secondary">Categoria</label>
                        <select name="category" id="category" class="form-select bg-light" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($event['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                         <label for="custom_location" class="form-label fw-semibold text-secondary">Local (Informativo)</label>
                         <input type="text" name="custom_location_disabled" id="custom_location" class="form-control bg-light" value="<?php echo htmlspecialchars($event['custom_location'] ?? ''); ?>" readonly disabled>
                         <input type="hidden" name="custom_location" value="<?php echo htmlspecialchars($event['custom_location'] ?? ''); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-secondary">Visibilidade</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_public" id="public_yes" value="1" <?php echo ($event['is_public'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="public_yes">Público</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_public" id="public_no" value="0" <?php echo ($event['is_public'] == 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="public_no">Privado</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="link_title" class="form-label fw-semibold text-secondary">Título do Link</label>
                        <input type="text" name="link_title" id="link_title" class="form-control bg-light" value="<?php echo htmlspecialchars($event['link_title'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="external_link" class="form-label fw-semibold text-secondary">Link Externo</label>
                        <input type="url" name="external_link" id="external_link" class="form-control bg-light" value="<?php echo htmlspecialchars($event['external_link'] ?? ''); ?>">
                    </div>

                    <div class="col-12">
                        <label for="schedule_file" class="form-label fw-semibold text-secondary">Programação do Evento (Opcional)</label>
                        <?php if (!empty($event['schedule_file_path'])): ?>
                            <div class="mb-2">
                                <a href="<?php echo htmlspecialchars($event['schedule_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-file-download me-1"></i>Ver Programação Atual
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control bg-light" id="schedule_file" name="schedule_file" accept=".pdf, .doc, .docx, .odt, .jpg, .jpeg, .png">
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Descrição Detalhada</label>
                        <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/eventos/request/my_requests" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">Enviar Alterações</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
