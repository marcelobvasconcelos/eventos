<?php
$title = 'Ativos';
ob_start();
?>

<div class="row">
    <!-- Assets List Card -->
    <div class="col-12 mb-5">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-0"><i class="fas fa-boxes-stacked me-2"></i>Ativos Disponíveis</h2>
                    <p class="text-muted small mb-0 mt-1">Lista de todos os equipamentos e recursos do sistema</p>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                     <div>
                        <a href="/eventos/asset/create" class="btn btn-primary rounded-pill me-2"><i class="fas fa-plus me-2"></i>Novo Ativo</a>
                        <a href="/eventos/request/form" class="btn btn-outline-primary rounded-pill"><i class="fas fa-calendar-plus me-2"></i>Novo Evento</a>
                     </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-uppercase small text-muted">
                                <th class="ps-4">Nome do Ativo</th>
                                <th>Descrição</th>
                                <th class="text-center">Disponibilidade</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($asset['description']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $ratio = $asset['quantity'] > 0 ? $asset['available_quantity'] / $asset['quantity'] : 0;
                                        $badgeClass = $ratio == 0 ? 'bg-danger-subtle text-danger' : ($ratio < 0.3 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');
                                        ?>
                                        <span class="badge rounded-pill <?php echo $badgeClass; ?> border border-opacity-10">
                                            <?php echo htmlspecialchars($asset['available_quantity']); ?> / <?php echo htmlspecialchars($asset['quantity']); ?> Unid.
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if (isset($_SESSION['user_id']) && $asset['available_quantity'] > 0): ?>
                                            <a href="/eventos/request/form?asset_id=<?php echo htmlspecialchars($asset['id']); ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-hand-holding me-1"></i>Solicitar
                                            </a>
                                        <?php elseif (isset($_SESSION['user_id'])): ?>
                                             <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>Indisponível</button>
                                        <?php else: ?>
                                            <a href="/eventos/auth/login" class="btn btn-sm btn-outline-primary rounded-pill px-3">Entrar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- User Loans Section -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $loanModel = new Loan();
        $loans = $loanModel->getLoansByUser($_SESSION['user_id']);
        ?>
        <?php if (!empty($loans)): ?>
        <div class="col-12">
            <div class="card shadow-sm rounded-lg border-0">
                <div class="card-header bg-light border-0 py-3">
                    <h4 class="fw-bold text-secondary mb-0"><i class="fas fa-history me-2"></i>Seus Empréstimos Ativos</h4>
                </div>
                <div class="card-body p-0">
                     <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small text-muted">
                                    <th class="ps-4">Ativo</th>
                                    <th>Evento</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium"><?php echo htmlspecialchars($loan['asset_name']); ?></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary"><i class="fas fa-calendar-day me-1"></i><?php echo htmlspecialchars($loan['event_name']); ?></span></td>
                                        <td>
                                            <?php 
                                            // PHP < 8.0 compatibility: use array lookup instead of match
                                            $statusClasses = [
                                                'Emprestado' => 'text-primary',
                                                'Devolvido' => 'text-success',
                                                'Atrasado' => 'text-danger'
                                            ];
                                            $statusClass = $statusClasses[$loan['status']] ?? 'text-secondary';

                                            $statusIcons = [
                                                'Emprestado' => 'fa-clock',
                                                'Devolvido' => 'fa-check-circle',
                                                'Atrasado' => 'fa-exclamation-circle'
                                            ];
                                            $statusIcon = $statusIcons[$loan['status']] ?? 'fa-circle';
                                            ?>
                                            <span class="fw-bold <?php echo $statusClass; ?>"><i class="fas <?php echo $statusIcon; ?> me-1"></i><?php echo htmlspecialchars($loan['status']); ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if ($loan['status'] === 'Emprestado'): ?>
                                                <form method="POST" action="/eventos/asset/checkin" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                    <input type="hidden" name="loan_id" value="<?php echo htmlspecialchars($loan['id']); ?>">
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-undo me-1"></i>Devolver</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';