<?php
$title = 'Painel Administrativo';
ob_start();
?>
<h1 class="text-center mb-5 text-white fw-bold">Painel Administrativo</h1>
<div class="row">
    <!-- Featured Card (Always Visible) -->
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card dashboard-card h-100 shadow-sm border-0">
            <div class="card-body text-center d-flex flex-column align-items-center">
                <i class="fas fa-calendar-days fa-3x mb-3 text-primary"></i>
                <h5 class="card-title fw-bold">Gerenciar Eventos</h5>
                <p class="card-text text-muted">Aprovar e editar eventos.</p>
                <a href="/eventos/admin/events" class="btn btn-primary rounded-pill px-5 mt-auto">Acessar</a>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 border border-success border-opacity-25">
                        <?php echo $futureEventsCount; ?> Eventos Disponíveis
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Toggle Button -->
    <div class="col-12 d-md-none text-center mb-4">
        <button class="btn btn-light rounded-circle shadow-sm animate-bounce" type="button" data-bs-toggle="collapse" data-bs-target=".extra-card" aria-expanded="false" aria-controls="extraDashboardCards" style="width: 50px; height: 50px;">
            <i class="fas fa-chevron-down text-primary"></i>
        </button>
        <div class="small text-muted mt-1">Ver mais opções</div>
    </div>

            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-users fa-3x mb-3 text-info"></i>
                        <h5 class="card-title fw-bold">Gerenciar Usuários</h5>
                        <p class="card-text text-muted">Administrar contas.</p>
                        <a href="/eventos/admin/users" class="btn btn-outline-info rounded-pill px-5 mt-auto">Acessar</a>
                         <div class="mt-3">
                            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2 border border-info border-opacity-25">
                                <?php echo $userCount; ?> Usuários
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-map-marker-alt fa-3x mb-3 text-warning"></i>
                        <h5 class="card-title fw-bold">Gerenciar Locais</h5>
                        <p class="card-text text-muted">Locais disponíveis.</p>
                        <a href="/eventos/admin/locations" class="btn btn-outline-warning text-dark rounded-pill px-5 mt-auto">Acessar</a>
                        <div class="mt-3">
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2 border border-warning border-opacity-25">
                                <?php echo $locationCount; ?> Locais
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-tags fa-3x mb-3 text-secondary"></i>
                        <h5 class="card-title fw-bold">Gerenciar Categorias</h5>
                        <p class="card-text text-muted">Categorias de eventos.</p>
                        <a href="/eventos/admin/categories" class="btn btn-outline-secondary rounded-pill px-5 mt-auto">Acessar</a>
                        <div class="mt-3">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2 border border-secondary border-opacity-25">
                                <?php echo $categoryCount; ?> Categorias
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-boxes-stacked fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title fw-bold">Patrimônio</h5>
                        <p class="card-text text-muted">Gestão de Equipamentos.</p>
                        <a href="/eventos/asset" class="btn btn-outline-primary rounded-pill px-5 mt-auto">Acessar</a>
                        <div class="mt-3">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 border border-primary border-opacity-25">
                                <?php echo $assetCount; ?> Tipos de Itens
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-chart-line fa-3x mb-3 text-success"></i>
                        <h5 class="card-title fw-bold">Relatórios</h5>
                        <p class="card-text text-muted">Gerar relatórios de eventos.</p>
                        <a href="/eventos/admin/reports" class="btn btn-outline-success rounded-pill px-5 mt-auto">Acessar</a>
                        <div class="mt-3 invisible">
                            <span class="badge px-3 py-2 border">Placeholder</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-cogs fa-3x mb-3 text-dark"></i>
                        <h5 class="card-title fw-bold">Configurações</h5>
                        <p class="card-text text-muted">Configurações globais do site.</p>
                        <a href="/eventos/settings" class="btn btn-outline-dark rounded-pill px-5 mt-auto">Acessar</a>
                        <div class="mt-3 invisible">
                            <span class="badge px-3 py-2 border">Placeholder</span>
                        </div>
                    </div>
                </div>
            </div>
</div>

<style>
/* Dashboard Hover Effects */
.dashboard-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
  40% {transform: translateY(-10px);}
  60% {transform: translateY(-5px);}
}
.animate-bounce {
    animation: bounce 2s infinite;
}

/* Fix for d-md-contents if not supported in all browsers, backup layout */
@media (min-width: 768px) {
    #extraDashboardCards {
        display: contents !important; /* Allows children to participate in the parent grid/row */
    }
}
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';