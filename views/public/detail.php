<?php
$title = 'Detalhes do Evento';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-lg border-0 mb-4">
            <div class="card-header bg-white py-4 border-0 text-center">
                <?php 
                    $displayImage = !empty($event['image_path']) ? $event['image_path'] : '/eventos/lib/banner.jpeg';
                ?>
                <div class="mb-4">
                    <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="img-fluid rounded-3 shadow-sm" style="max-height: 400px; width: 100%; object-fit: cover;">
                </div>

                <?php
                    $isPublic = $event['is_public'] ?? 1;
                    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($event['created_by'] ?? 0);
                    
                    if (!$isPublic && !$isAdmin && !$isOwner) {
                        $event['name'] = "Agendamento Privado";
                        $event['description'] = "Detalhes restritos ao responsável.";
                    }
                ?>
                <h1 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($event['name']); ?></h1>
                <p class="text-muted"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($event['category_name'] ?? 'Sem Categoria'); ?></p>
                <?php if ($isAdmin): ?>
                    <a href="/eventos/admin/printEvent?id=<?php echo $event['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill mt-2">
                        <i class="fas fa-file-pdf me-2"></i>Gerar Relatório PDF
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-4 p-md-5">
                <?php if (($event['status'] ?? '') === 'Cancelado'): ?>
                    <div class="alert alert-danger text-center mb-4">
                        <i class="fas fa-ban me-2"></i><strong>EVENTO CANCELADO</strong>
                    </div>
                <?php endif; ?>
                <div class="row g-4">
                    <!-- Date and Time -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <div class="bg-light text-secondary rounded-circle p-3 me-3">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Data e Hora</h6>
                                <p class="mb-0 text-muted">
                                    <?php echo date('d/m/Y', strtotime($event['date'])); ?><br>
                                    <?php echo date('H:i', strtotime($event['date'])); ?>
                                    <?php if ($event['end_date']): ?>
                                        até <?php echo date('d/m/Y H:i', strtotime($event['end_date'])); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <div class="col-md-6">
                         <div class="d-flex align-items-start">
                            <div class="bg-light text-secondary rounded-circle p-3 me-3">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Localização</h6>
                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($event['location_name'] ?? 'Local não definido'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <div class="bg-light p-4 rounded-3">
                             <h6 class="fw-bold mb-2"><i class="fas fa-align-left me-2"></i>Descrição</h6>
                             <p class="text-muted mb-0" style="white-space: pre-line;"><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                    </div>

                    <div class="col-12"><hr class="text-muted opacity-25"></div>

                    <!-- Requested Assets -->
                    <?php
// Check if the current user is the creator or an admin
$canViewAssets = isset($_SESSION['user_id']) && (
    $_SESSION['user_id'] == $event['created_by'] || 
    ($_SESSION['user_role'] ?? '') === 'admin'
);
?>

<?php if ($canViewAssets): ?>
                    <div class="col-12">
                        <div class="card bg-white border shadow-sm">
                            <div class="card-header bg-light border-0 py-3">
                                <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-boxes me-2"></i>Equipamentos Solicitados</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($loans)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">Item / Equipamento</th>
                                                    <th>Status</th>
                                                    <th class="text-end pe-4">Data de Devolução Prevista</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($loans as $loan): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-medium text-dark"><?php echo htmlspecialchars($loan['asset_name']); ?></td>
                                                        <td>
                                                            <?php
                                                            $statusClass = match($loan['status']) {
                                                                'Emprestado' => 'bg-warning-subtle text-warning-emphasis',
                                                                'Devolvido' => 'bg-success-subtle text-success-emphasis',
                                                                'Atrasado' => 'bg-danger-subtle text-danger-emphasis',
                                                                default => 'bg-secondary-subtle text-secondary-emphasis'
                                                            };
                                                            ?>
                                                            <span class="badge rounded-pill <?php echo $statusClass; ?> px-3 py-2">
                                                                <?php echo htmlspecialchars($loan['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end pe-4 text-muted small">
                                                            <?php echo date('d/m/Y H:i', strtotime($loan['return_date'])); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 text-secondary opacity-25"></i>
                                        <p class="mb-0">Nenhum equipamento solicitado para este evento.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
<?php endif; ?>

                    <div class="col-12"><hr class="text-muted opacity-25"></div>

                    <!-- Responsible Parties -->
                     <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 border rounded-3 h-100">
                             <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div>
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Solicitado Por</small>
                                <p class="mb-0 fw-medium text-dark"><?php echo htmlspecialchars($event['creator_name'] ?? 'Sistema'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 border rounded-3 h-100">
                             <div class="bg-success-subtle text-success rounded-circle p-2 me-3">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Aprovado Por</small>
                                <p class="mb-0 fw-medium text-dark"><?php echo htmlspecialchars($event['approver_name'] ?? 'Aguardando / Automático'); ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer bg-white border-0 text-center pb-4">
                <a href="/eventos/public/calendar" class="btn btn-outline-secondary rounded-pill px-4 me-2"><i class="fas fa-arrow-left me-2"></i>Voltar ao Calendário</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="/eventos/admin/editEvent?id=<?php echo $event['id']; ?>&return_url=<?php echo urlencode('/eventos/public/detail?id=' . $event['id']); ?>" class="btn btn-primary rounded-pill px-4 ms-2"><i class="fas fa-edit me-2"></i>Editar</a>
                    
                    <?php if (($event['status'] ?? '') === 'Pendente'): ?>
                         <form action="/eventos/admin/approve" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                             <button type="submit" class="btn btn-success rounded-pill px-4 ms-2"><i class="fas fa-check-circle me-2"></i>Aprovar</button>
                        </form>
                        <form action="/eventos/admin/reject" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                             <button type="submit" class="btn btn-danger rounded-pill px-4 ms-2"><i class="fas fa-times-circle me-2"></i>Rejeitar</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($event['status'] !== 'Cancelado'): ?>
                        <form action="/eventos/admin/cancelEvent" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja CANCELAR este evento? Ele continuará visível no mapa/calendário como CANCELADO.');">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                            <button type="submit" class="btn btn-warning rounded-pill px-4 ms-2"><i class="fas fa-ban me-2"></i>Cancelar</button>
                        </form>
                    <?php endif; ?>

                    <form action="/eventos/admin/deleteEvent" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja EXCLUIR permanentemente este evento? Ele sumirá do mapa. Se quiser manter histórico, opte por CANCELAR.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 ms-2"><i class="fas fa-trash me-2"></i>Excluir</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';