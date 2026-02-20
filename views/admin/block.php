<?php
$title = 'Bloquear Locais';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow border-0">
            <div class="card-header bg-danger text-white py-3">
                <h4 class="mb-0 fw-bold"><i class="fas fa-ban me-2"></i>Marcar Indisponibilidade / Bloqueio Interno</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="/eventos/admin/storeBlock" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Locais a Bloquear</label>
                        <div class="card p-3 bg-light border-0">
                            <div class="form-check mb-2 pb-2 border-bottom">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label fw-bold" for="selectAll" style="cursor: pointer;">Selecionar Todos</label>
                            </div>
                            <div class="row g-2" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($locations as $loc): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input location-check" type="checkbox" name="locations[]" value="<?php echo $loc['id']; ?>" id="loc_<?php echo $loc['id']; ?>">
                                            <label class="form-check-label" for="loc_<?php echo $loc['id']; ?>" style="cursor: pointer;">
                                                <?php echo htmlspecialchars($loc['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-text text-muted">Selecione os locais que ficarão indisponíveis.</div>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="form-label fw-bold">Motivo / Descrição do Bloqueio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="reason" name="reason" placeholder="Ex: Feriado Municipal, Manutenção Elétrica, Evento Interno..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date" class="form-label fw-bold">Data <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label fw-bold">Hora Início <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required value="08:00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label fw-bold">Hora Fim <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required value="18:00">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger btn-lg text-white">
                            <i class="fas fa-lock me-2"></i>Confirmar Bloqueio
                        </button>
                        <a href="/eventos/admin/dashboard" class="btn btn-light btn-lg border">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.location-check');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
