<?php
$title = 'Painel Administrativo';
ob_start();
?>
<h1 class="text-center mb-5">Painel Administrativo</h1>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-calendar-days fa-3x mb-3"></i>
                <h5 class="card-title">Gerenciar Eventos</h5>
                <p class="card-text">Aprovar e editar eventos.</p>
                <a href="/eventos/admin/events" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h5 class="card-title">Gerenciar Usuários</h5>
                <p class="card-text">Administrar contas.</p>
                <a href="/eventos/admin/users" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                <h5 class="card-title">Gerenciar Locais</h5>
                <p class="card-text">Locais disponíveis.</p>
                <a href="/eventos/admin/locations" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-tags fa-3x mb-3"></i>
                <h5 class="card-title">Gerenciar Categorias</h5>
                <p class="card-text">Categorias de eventos.</p>
                <a href="/eventos/admin/categories" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-boxes-stacked fa-3x mb-3"></i>
                <h5 class="card-title">Patrimônio</h5>
                <p class="card-text">Gestão de Ativos.</p>
                <a href="/eventos/asset" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-home fa-3x mb-3"></i>
                <h5 class="card-title">Página Inicial</h5>
                <p class="card-text">Voltar para o site público.</p>
                <a href="/eventos/" class="btn btn-outline-primary mt-auto">Ir ao Início</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';