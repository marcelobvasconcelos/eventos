<?php
$title = 'Confirmar Exclusão de Equipamento';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width: 70px; height: 70px;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <h1 class="fw-bold text-white shadow-sm">Confirmar Exclusão</h1>
            <p class="text-white-50">Esta ação não pode ser desfeita</p>
        </div>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-trash-alt me-2"></i> Você está prestes a excluir: <?php echo htmlspecialchars($asset['name']); ?>
                </h5>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <?php if (!empty($futureReservations)): ?>
                    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-calendar-times fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Aviso de Conflito de Agendamento</h6>
                                <p class="text-muted small mb-0">
                                    Este equipamento possui <strong><?php echo count($futureReservations); ?></strong> reserva(s) futura(s). 
                                    A exclusão removerá automaticamente as reservas dos eventos abaixo:
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden mb-4">
                        <?php foreach ($futureReservations as $res): ?>
                            <div class="list-group-item list-group-item-action py-3">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-2 me-3">
                                            <i class="fas fa-calendar-day text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($res['name']); ?></h6>
                                            <small class="text-muted">ID: <?php echo $res['id']; ?></small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">
                                        <?php echo date('d/m/Y', strtotime($res['date'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <p class="text-danger fw-bold text-center mb-0">
                        <i class="fas fa-info-circle me-1"></i> 
                        Confirmar a exclusão afetará todos os eventos listados acima.
                    </p>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="lead text-dark mb-4">
                            Tem certeza que deseja excluir o equipamento <strong>"<?php echo htmlspecialchars($asset['name']); ?>"</strong>?
                        </p>
                        <p class="text-muted small mb-0">
                            Não existem reservas futuras associadas a este item no momento.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-light py-4 px-4 p-md-5 border-0">
                <form action="/eventos/admin/deleteAsset" method="POST" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($asset['id']); ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    
                    <div class="col-md-6 order-2 order-md-1">
                        <a href="/eventos/admin/assets" class="btn btn-outline-secondary w-100 py-3 rounded-pill fw-bold">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                    <div class="col-md-6 order-1 order-md-2">
                        <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-trash-alt me-2"></i>Sim, Excluir Equipamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
