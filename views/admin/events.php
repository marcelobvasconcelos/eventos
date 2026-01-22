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
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm rounded-3 overflow-hidden" 
                     style="background-color: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.6);">
                    <div class="position-relative" style="height: 180px;">
                        <?php 
                            $img = !empty($event['image_path']) ? $event['image_path'] : '/eventos/lib/banner.jpeg';
                        ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="w-100 h-100 object-fit-cover" alt="Event Image">
                        <div class="position-absolute top-0 end-0 m-2">
                             <span class="badge bg-warning text-dark shadow-sm">Pendente</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-truncate" style="line-height: 1.4;">
                             <span style="background: rgba(255, 243, 205, 0.9); color: #495057; padding: 2px 6px; border-radius: 4px;">
                                <?php echo htmlspecialchars($event['name']); ?>
                             </span>
                        </h5>
                         <p class="card-text text-muted mb-2" style="font-size: 1rem; font-weight: 500;">
                            <i class="far fa-calendar me-1"></i> <?php echo date('d/m/Y H:i', strtotime($event['date'])); ?>
                        </p>
                        <p class="card-text text-muted small text-truncate">
                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location_name'] ?? 'N/A'); ?>
                        </p>
                         <p class="card-text small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($event['description']); ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                         <div class="d-grid gap-2">
                            <a href="/eventos/public/detail?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary rounded-pill btn-sm">
                                <i class="fas fa-eye me-2"></i>Ver Detalhes / Aprovar
                            </a>
                            <a href="/eventos/admin/editEvent?id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
                                <i class="fas fa-edit me-2"></i>Editar Rápido
                            </a>
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