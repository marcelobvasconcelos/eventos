<?php
$title = 'Editar Equipamento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="fw-bold text-primary mb-0"><i class="fas fa-edit me-2"></i>Editar Equipamento</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/eventos/admin/updateAsset" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($asset['id']); ?>">
                    
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small text-uppercase text-secondary">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($asset['name']); ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="quantity" class="form-label fw-bold small text-uppercase text-secondary">Quantidade Total</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo htmlspecialchars($asset['quantity']); ?>" min="1" required>
                        <div class="form-text text-muted small">Afeta a disponibilidade atual.</div>
                    </div>

                    <div class="col-md-3">
                        <label for="category_id" class="form-label fw-bold small text-uppercase text-secondary">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Sem Categoria</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($asset['category_id']) && $asset['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="requires_patrimony" name="requires_patrimony" value="1" <?php echo !empty($asset['requires_patrimony']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold text-dark" for="requires_patrimony">Requer identificação de patrimônio?</label>
                                </div>
                                <div class="text-muted small mt-1 ms-4">
                                    <i class="fas fa-info-circle me-1"></i> Se marcado, será necessário informar o número do patrimônio ao imprimir a lista de conferência.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold small text-uppercase text-secondary">Descrição</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($asset['description']); ?></textarea>
                    </div>
                    
                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <a href="/eventos/admin/assets" class="btn btn-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salvar Alterações</button>
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
