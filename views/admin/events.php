<?php
$title = 'Gerenciar Eventos';
ob_start();
?>
<h1>Eventos Pendentes</h1>
<div class="mb-3">
    <a href="/eventos/admin/dashboard" class="btn btn-secondary">Voltar ao Painel</a>
</div>
<?php if (empty($events)): ?>
    <div class="alert alert-info">Nenhum evento pendente.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                        <p class="card-text"><strong>Data:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                        <p class="card-text"><strong>Localização:</strong> <?php echo htmlspecialchars($event['location_name'] ?? 'N/A'); ?></p>
                        <p class="card-text"><strong>Ativos Solicitados:</strong> <?php echo htmlspecialchars($event['assets_display']); ?></p>
                        <div class="d-flex gap-2">
                            <a href="/eventos/admin/editEvent?id=<?php echo $event['id']; ?>" class="btn btn-primary">Editar</a>
                            <form action="/eventos/admin/approve" method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                <button type="submit" class="btn btn-success">Aprovar</button>
                            </form>
                            <form action="/eventos/admin/reject" method="post" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                <button type="submit" class="btn btn-danger">Rejeitar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';