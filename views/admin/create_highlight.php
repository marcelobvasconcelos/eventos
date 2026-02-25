<?php
$title = 'Adicionar Destaque no Calendário';
ob_start();
?>
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-0 py-4">
        <h2 class="mb-0 fw-bold text-primary"><i class="fas fa-bullhorn me-2"></i>Adicionar Destaque Informativo</h2>
        <p class="text-muted small mb-0 mt-1">Crie um aviso que aparecerá no topo dos dias no calendário (ex: Início do Semestre, Feriado sem bloqueio direto).</p>
    </div>
    <div class="card-body p-4">
        <form action="/eventos/admin/storeHighlight" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <label for="title" class="form-label fw-semibold">Título do Destaque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required placeholder="Ex: Início das Aulas">
                </div>
                
                <div class="col-md-4">
                    <label for="color" class="form-label fw-semibold">Cor de Destaque</label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="color" class="form-control form-control-color border-0 p-0 shadow-sm" id="color" name="color" value="#17a2b8" title="Escolha a cor do destaque" style="width: 50px; height: 50px; cursor: pointer;">
                        <span class="text-muted small">Escolha uma cor pastel suave.</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-semibold">Descrição (Opcional)</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Insira detalhes adicionais aqui..."></textarea>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <label for="date" class="form-label fw-semibold">Data do Destaque <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" required>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5">
                <a href="/eventos/admin/highlights" class="btn btn-outline-secondary px-4 rounded-pill">Cancelar</a>
                <button type="submit" class="btn btn-primary px-5 rounded-pill"><i class="fas fa-save me-2"></i>Salvar Destaque</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
