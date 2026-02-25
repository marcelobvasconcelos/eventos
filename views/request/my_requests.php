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
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
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
                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Data Evento</th>
                                    <th>Evento</th>
                                    <th>Status</th>
                                    <th>Data Solicitação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($request['event_date'])); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($request['event_name']); ?></td>
                                        <td>
                                            <?php
                                                $status = $request['status'];
                                                $badgeClass = 'bg-secondary';
                                                if ($status == 'Aprovado') $badgeClass = 'bg-success';
                                                elseif ($status == 'Rejeitado' || $status == 'Cancelado') $badgeClass = 'bg-danger';
                                                elseif ($status == 'Pendente') $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
                                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($request['request_date'] ?? 'now')); ?></td>
                                        <td>
                                            <?php 
                                            $eventTimestamp = strtotime(substr($request['event_date'], 0, 10));
                                            $todayTimestamp = strtotime(date('Y-m-d'));
                                            if ($eventTimestamp >= $todayTimestamp): 
                                            ?>
                                                <a href="/eventos/request/edit?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Editar">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Collapsible List -->
                    <div class="d-md-none">
                        <?php foreach ($requests as $index => $request): ?>
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-header bg-white border-0 py-3 text-center" 
                                     data-bs-toggle="collapse" 
                                     data-bs-target="#requestCollapse<?php echo $index; ?>" 
                                     aria-expanded="false" 
                                     style="cursor: pointer;">
                                    
                                    <h5 class="fw-bold text-primary mb-0">
                                        <?php echo htmlspecialchars($request['event_name']); ?>
                                        <i class="fas fa-chevron-down ms-2 small text-muted"></i>
                                    </h5>
                                </div>
                                <div id="requestCollapse<?php echo $index; ?>" class="collapse">
                                    <div class="card-body pt-0 text-center">
                                        <hr class="mt-0 mb-3 opacity-25">
                                        
                                        <div class="mb-3">
                                            <small class="text-muted text-uppercase fw-bold">Status</small><br>
                                            <?php
                                                $status = $request['status'];
                                                $badgeClass = 'bg-secondary';
                                                if ($status == 'Aprovado') $badgeClass = 'bg-success';
                                                elseif ($status == 'Rejeitado') $badgeClass = 'bg-danger';
                                                elseif ($status == 'Pendente') $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2 mt-1"><?php echo htmlspecialchars($status); ?></span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted text-uppercase fw-bold">Data do Evento</small><br>
                                            <span class="fw-medium text-dark"><?php echo date('d/m/Y H:i', strtotime($request['event_date'])); ?></span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted text-uppercase fw-bold">Solicitado em</small><br>
                                            <span class="text-secondary small"><?php echo date('d/m/Y H:i', strtotime($request['request_date'] ?? 'now')); ?></span>
                                        </div>

                                        <?php 
                                        $eventTimestamp = strtotime(substr($request['event_date'], 0, 10));
                                        $todayTimestamp = strtotime(date('Y-m-d'));
                                        if ($eventTimestamp >= $todayTimestamp): 
                                        ?>
                                            <div class="mt-3">
                                                <a href="/eventos/request/edit?id=<?php echo $request['id']; ?>" class="btn btn-outline-primary rounded-pill w-100">
                                                    <i class="fas fa-edit me-2"></i>Editar Solicitação
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
