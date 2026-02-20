<?php
$title = 'Gerenciar Destaques no Calendário';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0 fw-bold text-white"><i class="fas fa-bullhorn me-2"></i>Destaques Informativos</h2>
        <p class="text-white-50 small mb-0 mt-1">Gerencie os avisos que aparecem no topo dos dias no calendário (ex: Início do Semestre, Feriados etc).</p>
    </div>
    <a href="/eventos/admin/createHighlight" class="btn btn-primary shadow-sm rounded-pill fw-bold">
        <i class="fas fa-plus me-2"></i>Novo Destaque
    </a>
</div>

<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['message']); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Título</th>
                        <th>Cor</th>
                        <th>Período</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($highlights)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3 opacity-50"></i><br>
                                Nenhum destaque cadastrado.<br>
                                Clque acima para criar o primeiro!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($highlights as $h): 
                            $startDate = date('d/m/Y', strtotime($h['date']));
                            $endDate = date('d/m/Y', strtotime($h['end_date'] ?? $h['date']));
                            $period = ($startDate === $endDate) ? $startDate : "$startDate até $endDate";
                            $color = htmlspecialchars($h['custom_location'] ?? '#ffc107');
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($h['name']); ?></div>
                                    <?php if (!empty($h['description'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($h['description']); ?>">
                                            <?php echo htmlspecialchars($h['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 20px; height: 20px; border-radius: 50%; background-color: <?php echo $color; ?>; border: 1px solid rgba(0,0,0,0.1);"></div>
                                        <span class="small text-muted font-monospace"><?php echo $color; ?></span>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo $period; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="/eventos/admin/editHighlight?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteHighlightModal" data-id="<?php echo $h['id']; ?>" data-name="<?php echo htmlspecialchars($h['name']); ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteHighlightModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                <h5 class="fw-bold text-dark">Deseja excluir este destaque?</h5>
                <p class="text-muted">Esta ação limpará este informativo do calendário de eventos de todos os usuários imediatamente.</p>
                <div class="bg-light p-3 rounded border mb-3">
                    <h6 class="fw-bold mb-0 text-primary" id="modalHighlightName">Nome</h6>
                </div>
                
                <form action="/eventos/admin/deleteHighlight" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" id="modalHighlightId" value="">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger fw-bold shadow-sm">Sim, Excluir</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteHighlightModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            
            deleteModal.querySelector('#modalHighlightId').value = id;
            deleteModal.querySelector('#modalHighlightName').textContent = name;
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
