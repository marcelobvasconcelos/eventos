<?php
$title = 'Ficha Técnica dos Locais';
include __DIR__ . '/../layout.php';
?>

<div class="container mt-4">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary">Nossos Espaços</h1>
        <p class="lead text-muted">Conheça os detalhes técnicos de cada ambiente disponível para seus eventos</p>
    </div>

    <div class="row g-4">
        <?php foreach ($locations as $location): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 rounded-4 hover-shadow transition-all">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <h3 class="card-title h4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($location['name']); ?></h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2">
                                <i class="fas fa-users me-2"></i>Capacidade: <strong><?php echo htmlspecialchars($location['capacity']); ?> pessoas</strong>
                            </span>
                        </div>
                        
                        <p class="card-text text-muted">
                            <?php echo nl2br(htmlspecialchars($location['description'])); ?>
                        </p>
                    </div>
                    <!--
                    <div class="card-footer bg-white border-top-0 pb-4">
                        <a href="/eventos/request/form?location_id=<?php echo $location['id']; ?>" class="btn btn-outline-primary rounded-pill w-100">
                            Selecionar este local
                        </a>
                    </div>
                    -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5 mb-5">
        <a href="/eventos/request/form" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
            <i class="fas fa-calendar-plus me-2"></i>Solicitar Evento Agora
        </a>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
