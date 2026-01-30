<?php
$title = 'Cadastrar Novo Equipamento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white py-4 border-0 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-box-open fa-2x"></i>
                </div>
                <h2 class="fw-bold text-primary mb-1">Cadastrar Novo Equipamento</h2>
                <p class="text-muted mb-0">Adicione um novo recurso ao inventário</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/eventos/asset/store" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="col-12">
                        <label for="name" class="form-label fw-semibold text-secondary">Nome do Equipamento</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" name="name" id="name" class="form-control border-start-0 ps-0 bg-light" placeholder="Ex: Projetor Epson" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold text-secondary">Descrição</label>
                        <textarea name="description" id="description" class="form-control bg-light" rows="3" placeholder="Detalhes técnicos, cor, modelo..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="category_id" class="form-label fw-semibold text-secondary">Categoria</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-tags"></i></span>
                            <select name="category_id" id="category_id" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="">Selecione uma categoria...</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-semibold text-secondary">Quantidade Total</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-sort-numeric-up"></i></span>
                            <input type="number" name="quantity" id="quantity" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1'); ?>" min="1" required>
                        </div>
                        <div class="form-text">O sistema criará itens individuais automaticamente.</div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch p-3 bg-light border rounded">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="requires_patrimony" name="requires_patrimony" value="1" <?php echo (isset($_POST['requires_patrimony']) && $_POST['requires_patrimony']) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold text-secondary" for="requires_patrimony">
                                Requer identificação de patrimônio?
                                <div class="small text-muted fw-normal">Se marcado, será necessário informar o número de tombamento individual para cada item.</div>
                            </label>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/eventos/asset" class="btn btn-outline-secondary px-4 rounded-pill">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm"><i class="fas fa-save me-2"></i>Salvar Equipamento</button>
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
