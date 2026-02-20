<?php
$title = 'Editar Destaque no Calendário';
ob_start();

$dateStart = isset($highlight['date']) ? date('Y-m-d', strtotime($highlight['date'])) : '';
$dateEnd = isset($highlight['end_date']) ? date('Y-m-d', strtotime($highlight['end_date'])) : '';
?>
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-0 py-4">
        <h2 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Editar Destaque Informativo</h2>
        <p class="text-muted small mb-0 mt-1">Altere os detalhes do aviso de calendário.</p>
    </div>
    <div class="card-body p-4">
        <form action="/eventos/admin/updateHighlight" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($highlight['id']); ?>">
            
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <label for="title" class="form-label fw-semibold">Título do Destaque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($highlight['name']); ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="color" class="form-label fw-semibold">Cor de Destaque</label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="color" class="form-control form-control-color border-0 p-0 shadow-sm" id="color" name="color" value="<?php echo htmlspecialchars($highlight['custom_location'] ?? '#ffc107'); ?>" title="Escolha a cor do destaque" style="width: 50px; height: 50px; cursor: pointer;">
                        <span class="text-muted small">Escolha uma cor pastel suave.</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-semibold">Descrição (Opcional)</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($highlight['description']); ?></textarea>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label for="date_start" class="form-label fw-semibold">Data Inicial <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_start" name="date_start" required value="<?php echo $dateStart; ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_end" class="form-label fw-semibold">Data Final</label>
                    <input type="date" class="form-control" id="date_end" name="date_end" value="<?php echo $dateEnd; ?>">
                    <div class="form-text">Deixe em branco para um único dia.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5">
                <a href="/eventos/admin/highlights" class="btn btn-outline-secondary px-4 rounded-pill">Cancelar</a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
