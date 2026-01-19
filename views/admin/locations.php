<?php
$title = 'Gerenciar Locais';
ob_start();
?>
<h1>Locais</h1>
<div class="mb-3">
    <a href="/eventos/admin/dashboard" class="btn btn-secondary">Voltar ao Painel</a>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Capacidade</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($locations as $location): ?>
            <tr>
                <td><?php echo htmlspecialchars($location['name']); ?></td>
                <td><?php echo htmlspecialchars($location['description']); ?></td>
                <td><?php echo htmlspecialchars($location['capacity']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editLocation(<?php echo $location['id']; ?>, '<?php echo addslashes($location['name']); ?>', '<?php echo addslashes($location['description']); ?>', <?php echo $location['capacity']; ?>)">Editar</button>
                    <form method="POST" action="/eventos/admin/deleteLocation" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este local?')">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="id" value="<?php echo $location['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/eventos/admin/updateLocation">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Nome</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Descrição</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editCapacity" class="form-label">Capacidade</label>
                        <input type="number" name="capacity" id="editCapacity" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<h2>Adicionar Local</h2>
<form method="POST" action="/eventos/admin/createLocation">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Descrição</label>
        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="capacity" class="form-label">Capacidade</label>
        <input type="number" name="capacity" id="capacity" class="form-control" min="1">
    </div>
    <button type="submit" class="btn btn-primary">Adicionar Local</button>
</form>

<script>
function editLocation(id, name, description, capacity) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editCapacity').value = capacity;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>