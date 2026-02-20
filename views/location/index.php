<?php
$title = 'Locais';
ob_start();
?>

<div class="row">
    <div class="col-12 px-4 py-4">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-0"><i class="fas fa-map-marker-alt me-2"></i>Locais Disponíveis</h2>
                    <p class="text-muted small mb-0 mt-1">Espaços e salas disponíveis para reserva</p>
                </div>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                     <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fas fa-plus me-2"></i>Novo Local
                     </button>
                <?php endif; ?>
            </div>
            
             <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mx-4"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mx-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-uppercase small text-muted">
                                <th class="ps-4">Nome</th>
                                <th>Descrição</th>
                                <th class="text-center">Capacidade</th>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <th class="text-end pe-4">Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">
                                        <i class="fas fa-building text-secondary me-2"></i><?php echo htmlspecialchars($location['name']); ?>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($location['description']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><i class="fas fa-users me-1"></i><?php echo htmlspecialchars($location['capacity']); ?></span>
                                    </td>
                                    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'gestor'])): ?>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <button class="btn btn-sm btn-outline-info px-2 me-1 btn-image-location" 
                                                    data-id="<?php echo $location['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($location['name']); ?>"
                                                    data-images='<?php echo htmlspecialchars(json_encode($location['images']), ENT_QUOTES, 'UTF-8'); ?>'
                                                    title="Gerenciar Imagens">
                                                <i class="fas fa-image"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary px-2 me-1 btn-edit-location" 
                                                    data-id="<?php echo $location['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($location['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($location['description']); ?>"
                                                    data-capacity="<?php echo $location['capacity']; ?>"
                                                    title="Editar Local">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="confirmDeleteLocation(<?php echo $location['id']; ?>)" title="Excluir Local">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/eventos/admin/createLocation" enctype="multipart/form-data">
                <div class="modal-body">
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
                        <label for="capacity" class="form-label">Capacidade (Pessoas)</label>
                        <input type="number" name="capacity" id="capacity" class="form-control" min="1">
                    </div>
                     <div class="mb-3">
                        <label for="images" class="form-label">Imagens do Local</label>
                        <input type="file" name="images[]" id="images" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">
                            Selecione múltiplas imagens (Ctrl + Clique). <br>
                            <small class="text-danger">Formatos: JPG, PNG, WEBP. Máx 2MB por imagem. Resolução ideal: 800x600px.</small>
                        </div>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/eventos/admin/updateLocation" enctype="multipart/form-data">
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
                        <label for="editCapacity" class="form-label">Capacidade (Pessoas)</label>
                        <input type="number" name="capacity" id="editCapacity" class="form-control" min="1">
                    </div>
                    <!-- Imagens removidas do Modal de Edição -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imagens do Local: <span id="imageLocationNameTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/eventos/admin/uploadLocationImages" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" id="imageLocationId">
                    <div class="mb-3">
                        <label for="newImages" class="form-label">Adicionar Imagens</label>
                        <input type="file" name="images[]" id="newImages" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">
                             Selecione múltiplas imagens (Ctrl + Clique). <br>
                             <small class="text-danger">Formatos: JPG, PNG, WEBP. Máx 2MB por imagem. Resolução ideal: 800x600px.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                         <label class="form-label">Imagens Atuais</label>
                         <div id="modalImagesList" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Imagens</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-location');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const capacity = this.getAttribute('data-capacity');
            
            openEditModal(id, name, description, capacity);
        });
    });

    const imageButtons = document.querySelectorAll('.btn-image-location');
    imageButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const images = JSON.parse(this.getAttribute('data-images') || '[]');
            
            openImageModal(id, name, images);
        });
    });
});

function openEditModal(id, name, description, capacity) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editCapacity').value = capacity;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openImageModal(id, name, images) {
    document.getElementById('imageLocationId').value = id;
    document.getElementById('imageLocationNameTitle').innerText = name;
    
    // Render existing images
    const container = document.getElementById('modalImagesList');
    container.innerHTML = '';
    if (images && images.length > 0) {
        images.forEach(img => {
            const div = document.createElement('div');
            div.className = 'position-relative';
            div.style.width = '100px';
            div.style.height = '100px';
            
            const imgEl = document.createElement('img');
            imgEl.src = img.image_path;
            imgEl.className = 'img-thumbnail w-100 h-100 object-fit-cover';
            
            const btn = document.createElement('button');
            btn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 p-0 rounded-circle';
            btn.style.width = '20px';
            btn.style.height = '20px';
            btn.style.lineHeight = '1';
            btn.innerHTML = '&times;';
            btn.onclick = function(e) { e.preventDefault(); deleteLocationImage(img.id, div); };
            
            div.appendChild(imgEl);
            div.appendChild(btn);
            container.appendChild(div);
        });
    } else {
        container.innerHTML = '<span class="text-muted small">Nenhuma imagem cadastrada.</span>';
    }

    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function deleteLocationImage(id, element) {
    if (!confirm('Excluir esta imagem?')) return;
    
    fetch('/eventos/admin/deleteLocationImage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({id: id})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.remove();
        } else {
            alert('Erro ao excluir imagem.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro de conexão.');
    });
}
</script>

<!-- Hidden Delete Form -->
<form id="deleteLocationForm" method="POST" action="/eventos/admin/deleteLocation" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" id="deleteLocationId">
    <input type="hidden" name="confirmed" id="deleteLocationConfirmed" value="0">
</form>

<script>
function confirmDeleteLocation(id) {
    // 1. Check if location has events
    fetch('/eventos/admin/checkLocationDeletion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({id: id})
    })
    .then(response => response.json())
    .then(data => {
        const count = data.count || 0;
        const form = document.getElementById('deleteLocationForm');
        document.getElementById('deleteLocationId').value = id;
        
        if (count > 0) {
            const message = `Este local possui ${count} evento(s) associado(s).\n\n` + 
                            `Se você excluir este local, os eventos serão atualizados para "Local a definir".\n\n` +
                            `Deseja confirmar a exclusão?`;
            
            if (confirm(message)) {
                document.getElementById('deleteLocationConfirmed').value = '1';
                form.submit();
            }
        } else {
            if (confirm('Tem certeza que deseja excluir este local?')) {
                document.getElementById('deleteLocationConfirmed').value = '0';
                form.submit();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao verificar dependências do local.');
    });
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
