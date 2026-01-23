<?php
$title = 'Gestão de Pendências';
ob_start();
?>

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-primary fw-bold mb-0"><i class="fas fa-tasks me-2"></i>Gestão de Devoluções</h2>
            <p class="text-muted small mb-0 mt-1">Confirme o recebimento de chaves e equipamentos.</p>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Item</th>
                        <th>Evento</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Separate Active and History
                        $activeItems = [];
                        $historyItems = [];
                        foreach ($pendingItems as $item) {
                            if ($item['status'] == 'completed') {
                                $historyItems[] = $item;
                            } else {
                                $activeItems[] = $item;
                            }
                        }
                    ?>
                    
                    <?php if (empty($activeItems)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Nenhuma pendência aguardando ação.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activeItems as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">
                                        <?php if ($item['item_type'] == 'key'): ?>
                                            <i class="fas fa-key text-warning me-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-box-open text-info me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['description']); ?>
                                        <?php if (!empty($item['user_note'])): ?>
                                            <div class="small text-muted mt-1 fst-italic">
                                                <i class="fas fa-comment fa-xs me-1"></i> "<?php echo htmlspecialchars($item['user_note']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($item['event_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                                <td>
                                    <?php 
                                        $badges = [
                                            'pending' => 'warning',
                                            'user_informed' => 'info',
                                            'contested' => 'danger',
                                            'completed' => 'success'
                                        ];
                                        $labels = [
                                            'pending' => 'Pendente',
                                            'user_informed' => 'Informado',
                                            'contested' => 'Contestado',
                                            'completed' => 'Concluído'
                                        ];
                                        $bg = $badges[$item['status']] ?? 'secondary';
                                        $label = $labels[$item['status']] ?? $item['status'];
                                    ?>
                                    <span class="badge bg-<?php echo $bg; ?>"><?php echo $label; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form action="/eventos/pending/updateStatus" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-success text-white" title="Confirmar Recebimento">
                                                <i class="fas fa-check"></i> Receber
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#contestModal" 
                                                data-id="<?php echo $item['id']; ?>">
                                            Contestar
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

<!-- History Section -->
<div class="mt-5 mb-4">
    <h4 class="text-secondary"><i class="fas fa-history me-2"></i>Histórico Completo</h4>
</div>
<div class="card border-0 shadow-sm bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Item</th>
                        <th>Evento</th>
                        <th>Responsável</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historyItems)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum histórico disponível.</td></tr>
                    <?php else: ?>
                        <?php foreach ($historyItems as $item): ?>
                             <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-muted">
                                        <?php if ($item['item_type'] == 'key'): ?>
                                            <i class="fas fa-key me-2"></i>
                                        <?php else: ?>
                                            <i class="fas fa-box-open me-2"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['description']); ?>
                                        <?php if (!empty($item['user_note'])): ?>
                                            <div class="small text-muted mt-1 fst-italic">
                                                <i class="fas fa-comment fa-xs me-1"></i> "<?php echo htmlspecialchars($item['user_note']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></small>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($item['event_name']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($item['user_name']); ?></td>
                                <td><span class="badge bg-success">Concluído</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Contest Modal -->
<div class="modal fade" id="contestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/eventos/pending/updateStatus" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Contestar Devolução</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="contestId">
                    <input type="hidden" name="status" value="contested">
                    <div class="mb-3">
                        <label class="form-label">Motivo da Contestação</label>
                        <textarea name="observation" class="form-control" rows="3" required placeholder="Ex: Chave não entregue, Equipamento danificado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Salvar Contestação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var contestModal = document.getElementById('contestModal');
    contestModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var input = contestModal.querySelector('#contestId');
        input.value = id;
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
