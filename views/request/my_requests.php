<?php
$title = 'Minhas Solicitações';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-list-alt me-2"></i>Minhas Solicitações</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($requests)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma solicitação encontrada.</h5>
                        <a href="/eventos/request/form" class="btn btn-primary mt-3">Nova Solicitação</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Data Evento</th>
                                    <th>Evento</th>
                                    <th>Status Solicitação</th>
                                    <th>Status Evento</th>
                                    <th>Data Solicitação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($request['event_date'])); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($request['event_name']); ?></td>
                                        <td>
                                            <?php
                                                $status = $request['status'];
                                                $badgeClass = 'bg-secondary';
                                                if ($status == 'Aprovado') $badgeClass = 'bg-success';
                                                elseif ($status == 'Rejeitado') $badgeClass = 'bg-danger';
                                                elseif ($status == 'Pendente') $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['event_status']); ?></td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($request['request_date'] ?? 'now')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
