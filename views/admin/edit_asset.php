<?php
$title = 'Editar Equipamento';
ob_start();
?>
<h1>Editar Equipamento</h1>
<form method="POST" action="/eventos/admin/updateAsset" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($asset['id']); ?>">
    
    <div class="col-md-6">
        <label for="name" class="form-label">Nome</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($asset['name']); ?>" required>
    </div>
    
    <div class="col-md-3">
        <label for="quantity" class="form-label">Quantidade Total</label>
        <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo htmlspecialchars($asset['quantity']); ?>" min="1" required>
        <div class="form-text">Alterar a quantidade ajustará a disponibilidade.</div>
    </div>
    
    <div class="col-12">
        <label for="description" class="form-label">Descrição</label>
        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($asset['description']); ?></textarea>
    </div>
    
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="/eventos/admin/assets" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
