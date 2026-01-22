<?php
$title = 'Confirmar Exclusão de Equipamento';
include __DIR__ . '/../layout.php'; // Wait, layout includes content variable? 
// layout.php structure: expects $content. 
// So I should ob_start(); ... ob_get_clean(); include layout.
// AdminController just includes this file.
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="fas fa-exclamation-triangle me-2"></i> Atenção: Conflito de Agendamento
            </div>
            <div class="card-body">
                <h5 class="card-title">O equipamento "<?php echo htmlspecialchars($asset['name']); ?>" possui reservas futuras.</h5>
                <p class="card-text">
                    A exclusão deste item removerá automaticamente as reservas associadas aos seguintes eventos, o que pode deixá-los sem os equipamentos necessários:
                </p>
                
                <div class="list-group mb-4">
                    <?php foreach ($futureReservations as $res): ?>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($res['name']); ?></h6>
                                <small><?php echo date('d/m/Y', strtotime($res['date'])); ?></small>
                            </div>
                            <small class="text-muted">ID do Evento: <?php echo $res['id']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="mb-0 fw-bold text-danger">Tem certeza que deseja prosseguir?</p>
            </div>
            <div class="card-footer bg-transparent border-warning">
                <form action="/eventos/admin/deleteAsset" method="POST" class="d-flex justify-content-end gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($asset['id']); ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    
                    <a href="/eventos/admin/assets" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger">Sim, Excluir Equipamento e Reservas</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
// include Layout.php logic requires $content to be set.
// AdminController included this view directly. 
// But AdminController usually sets content? No.
// Let's check AdminController::assets(). It does logic then `include view`.
// Does the view include layout?
// My `assets.php` (Line 1) did `$title = ...; ob_start(); ... include layout.php`.
// So YES, this file must include layout.
include __DIR__ . '/../layout.php';
?>
