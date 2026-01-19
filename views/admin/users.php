<?php
$title = 'Gerenciar Usuários';
ob_start();
?>
<div class="card shadow rounded-lg border-0">
    <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-primary mb-0"><i class="fas fa-users me-2"></i>Gerenciar Usuários</h2>
            <p class="text-muted small mb-0 mt-1">Administração de contas e permissões do sistema</p>
        </div>
        <a href="/eventos/admin/dashboard" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Voltar</a>
    </div>
    <div class="card-body p-0">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-uppercase small text-muted">
                        <th class="ps-4">Nome</th>
                        <th>Email</th>
                        <th>Função</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4 fw-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form action="/eventos/admin/updateRole" method="post" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <select name="role" class="form-select form-select-sm d-inline w-auto border-0 bg-light fw-bold <?php echo $user['role'] === 'admin' ? 'text-primary' : 'text-secondary'; ?>" onchange="this.form.submit()" style="cursor: pointer;">
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Usuário</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = 'bg-secondary';
                                    $statusText = $user['status'] ?? 'N/A';
                                    if ($statusText === 'Ativo') $statusClass = 'bg-success';
                                    elseif ($statusText === 'Pendente') $statusClass = 'bg-warning text-dark';
                                    elseif ($statusText === 'Inativo') $statusClass = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $statusClass; ?> rounded-pill"><?php echo htmlspecialchars($statusText); ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if (($user['status'] ?? '') === 'Pendente'): ?>
                                    <form action="/eventos/admin/approveUser" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success rounded-circle me-1" title="Aprovar"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="/eventos/admin/rejectUser" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger rounded-circle me-1" title="Rejeitar"><i class="fas fa-times"></i></button>
                                    </form>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal" 
                                        data-user-id="<?php echo $user['id']; ?>" 
                                        data-user-name="<?php echo htmlspecialchars($user['name']); ?>" 
                                        data-user-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal" 
                                        data-user-id="<?php echo $user['id']; ?>" 
                                        data-user-name="<?php echo htmlspecialchars($user['name']); ?>"
                                        title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="editUserModalLabel"><i class="fas fa-user-edit me-2"></i>Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/eventos/admin/updateUser" method="POST" id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="mb-3">
                        <label for="editUserName" class="form-label text-secondary fw-semibold">Nome</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0 bg-light" id="editUserName" name="name" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="editUserEmail" class="form-label text-secondary fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0 bg-light" id="editUserEmail" name="email" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                 <h5 class="modal-title fw-bold text-danger" id="deleteUserModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o usuário <strong id="deleteUserName" class="text-dark">User</strong>?</p>
                <div id="dependencyStats" class="alert alert-warning d-none">
                     <small class="d-block fw-bold mb-2">Impacto da exclusão:</small>
                     <ul class="mb-0 small ps-3">
                         <li id="statLoans"></li>
                         <li id="statRequests"></li>
                         <li id="statEvents"></li>
                         <li id="statApprovals"></li>
                     </ul>
                </div>
                <p class="text-muted small mb-0">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <form action="/eventos/admin/deleteUser" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal Logic
    var editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var userName = button.getAttribute('data-user-name');
        var userEmail = button.getAttribute('data-user-email');

        var userIdInput = editUserModal.querySelector('#editUserId');
        var userNameInput = editUserModal.querySelector('#editUserName');
        var userEmailInput = editUserModal.querySelector('#editUserEmail');

        userIdInput.value = userId;
        userNameInput.value = userName;
        userEmailInput.value = userEmail;
    });

    // Delete Modal Logic
    var deleteUserModal = document.getElementById('deleteUserModal');
    var dependencyAlert = document.getElementById('dependencyStats');
    
    deleteUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var userName = button.getAttribute('data-user-name');
        
        // Reset UI
        deleteUserModal.querySelector('#deleteUserName').textContent = userName;
        deleteUserModal.querySelector('#deleteUserId').value = userId;
        dependencyAlert.classList.add('d-none');
        
        // Fetch stats
        fetch('/eventos/admin/getUserStats?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                // Populate stats
                document.getElementById('statLoans').textContent = data.loans + ' Empréstimos serão excluídos';
                document.getElementById('statRequests').textContent = data.requests + ' Solicitações de evento serão excluídas';
                document.getElementById('statEvents').textContent = data.events_created + ' Eventos criados ficarão sem autor (anônimo)';
                document.getElementById('statApprovals').textContent = data.approvals + ' Aprovações feitas ficarão sem aprovador (anônimo)';
                
                // Show warning if any count > 0
                if (data.loans > 0 || data.requests > 0 || data.events_created > 0 || data.approvals > 0) {
                    dependencyAlert.classList.remove('d-none');
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';