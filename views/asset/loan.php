<?php
$title = 'Solicitar Empréstimo';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow rounded-lg border-0">
            <div class="card-header bg-white py-4 border-0 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="fas fa-hand-holding fa-2x"></i>
                </div>
                <h2 class="fw-bold text-primary mb-1">Solicitar Empréstimo de Ativo</h2>
                <p class="text-muted mb-0">Registre a retirada de um equipamento</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/eventos/asset/loan" class="row g-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="col-12">
                        <label for="asset_id" class="form-label fw-semibold text-secondary">Ativo</label>
                        <div class="input-group">
                             <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-box"></i></span>
                            <select name="asset_id" id="asset_id" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="" disabled selected>Selecione o ativo...</option>
                                <?php foreach ($assets as $asset): ?>
                                    <option value="<?php echo htmlspecialchars($asset['id']); ?>" <?php if (isset($_GET['asset_id']) && $_GET['asset_id'] == $asset['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($asset['name']); ?> (Disponível: <?php echo htmlspecialchars($asset['available_quantity']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                         <label for="event_id" class="form-label fw-semibold text-secondary">Evento Associado</label>
                         <div class="input-group">
                             <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                             <select name="event_id" id="event_id" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="" disabled selected>Selecione o evento...</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo htmlspecialchars($event['id']); ?>">
                                        <?php echo htmlspecialchars($event['name']); ?> - <?php echo date('d/m/Y', strtotime($event['date'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-text text-muted small ms-1"><i class="fas fa-info-circle me-1"></i>O empréstimo deve ser vinculado a um evento aprovado.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="loan_date" class="form-label fw-semibold text-secondary">Data do Empréstimo</label>
                         <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-calendar-check"></i></span>
                            <input type="date" name="loan_date" id="loan_date" class="form-control border-start-0 ps-0 bg-light" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="return_date" class="form-label fw-semibold text-secondary">Data Prevista de Devolução</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-calendar-times"></i></span>
                            <input type="date" name="return_date" id="return_date" class="form-control border-start-0 ps-0 bg-light" required>
                        </div>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/eventos/asset" class="btn btn-outline-secondary px-4 rounded-pill">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm"><i class="fas fa-check me-2"></i>Confirmar Empréstimo</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';