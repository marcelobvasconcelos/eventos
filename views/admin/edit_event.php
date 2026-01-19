<?php
$title = 'Editar Evento';
ob_start();
?>
<h1>Editar Evento</h1>
<form method="POST" action="/eventos/admin/updateEvent" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
    <div class="col-md-6">
        <label for="name" class="form-label">Título</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($event['name']); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="date" class="form-label">Data</label>
        <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($event['date']))); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="time" class="form-label">Hora</label>
        <input type="time" name="time" id="time" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($event['date']))); ?>" required>
    </div>
    <div class="col-md-6">
        <label for="location" class="form-label">Localização</label>
        <select name="location" id="location" class="form-control">
            <option value="">Selecione um local</option>
            <?php foreach ($locations as $location): ?>
                <option value="<?php echo $location['id']; ?>" <?php echo ($event['location_id'] == $location['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($location['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="category" class="form-label">Categoria</label>
        <select name="category" id="category" class="form-control">
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($event['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-control" required>
            <option value="Pendente" <?php echo ($event['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
            <option value="Aprovado" <?php echo ($event['status'] == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
            <option value="Rejeitado" <?php echo ($event['status'] == 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
            <option value="Concluido" <?php echo ($event['status'] == 'Concluido') ? 'selected' : ''; ?>>Concluido</option>
        </select>
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Descrição</label>
        <textarea name="description" id="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="/eventos/admin/events" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>