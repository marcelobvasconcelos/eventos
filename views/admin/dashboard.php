<?php
$title = 'Painel Administrativo';
ob_start();
?>
<h1 class="text-center mb-5 text-white fw-bold">Painel Administrativo</h1>
</div>

<?php if (!empty($pendingUsers) || !empty($pendingEvents)): ?>
<div class="row mt-2 animate-slide-down">
    <div class="col-12">
        <div class="card shadow border-0 bg-light text-dark mb-5" style="border: 1px solid rgba(0,0,0,0.1) !important;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center">
                <i class="fas fa-bolt text-warning me-2"></i>
                <h4 class="mb-0 fw-bold">Lista Rápida de Aprovações</h4>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Pending Users -->
                    <?php if (!empty($pendingUsers)): ?>
                    <div class="col-md-6">
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 border border-white border-opacity-10 h-100">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="fas fa-user-clock text-info me-2"></i> Novos Usuários
                                <span class="badge bg-info ms-auto rounded-pill"><?php echo count($pendingUsers); ?></span>
                            </h5>
                            <div class="list-group list-group-flush bg-transparent">
                                <?php foreach ($pendingUsers as $pUser): ?>
                                <div class="list-group-item bg-transparent text-dark border-dark border-opacity-10 px-0 d-flex align-items-center justify-content-between">
                                    <div class="me-3 overflow-hidden">
                                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($pUser['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($pUser['email']); ?></small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="/eventos/admin/approveUser" method="POST" class="m-0">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $pUser['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" title="Aceitar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="/eventos/admin/rejectUser" method="POST" class="m-0">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $pUser['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3" title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Pending Events and Proposals -->
                    <?php if (!empty($pendingEvents) || $pendingProposalsCount > 0): ?>
                    <div class="col-md-6">
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 border border-white border-opacity-10 h-100">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-warning me-2"></i> Solicitações de Eventos
                                <span class="badge bg-warning text-dark ms-auto rounded-pill"><?php echo count($pendingEvents) + $pendingProposalsCount; ?></span>
                            </h5>
                            <div class="list-group list-group-flush bg-transparent">
                                <!-- New Events -->
                                <?php foreach ($pendingEvents as $pEvent): ?>
                                <div class="list-group-item bg-transparent text-dark border-dark border-opacity-10 px-0 d-flex align-items-center justify-content-between">
                                    <div class="me-3 overflow-hidden">
                                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($pEvent['name']); ?></div>
                                        <small class="text-muted">Novo Evento • <?php echo date('d/m/Y', strtotime($pEvent['date'])); ?></small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="/eventos/admin/events" class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Analisar Novo">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Proposals -->
                                <?php 
                                    require_once __DIR__ . '/../../models/EventEdit.php';
                                    $editModel = new EventEdit();
                                    $quickProposals = $editModel->getPendingProposals();
                                    foreach (array_slice($quickProposals, 0, 5) as $prop): 
                                ?>
                                <div class="list-group-item bg-transparent text-dark border-dark border-opacity-10 px-0 d-flex align-items-center justify-content-between">
                                    <div class="me-3 overflow-hidden">
                                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($prop['original_name']); ?></div>
                                        <small class="text-info">Proposta de Edição • <?php echo date('d/m/Y', strtotime($prop['proposed_at'])); ?></small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="/eventos/admin/listProposals" class="btn btn-sm btn-outline-info rounded-pill px-3" title="Analisar Edição">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($pendingProposalsCount > 5): ?>
                                <div class="text-center mt-2">
                                    <small class="text-muted">+ <?php echo $pendingProposalsCount - 5; ?> outras edições pendentes</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-4 px-4 text-end">
                 <small class="text-muted">* Clique na lupa ou no lápis para analisar detalhadamente.</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <!-- Featured Card (Always Visible) -->
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card dashboard-card h-100 shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body text-center d-flex flex-column align-items-center">
                <i class="fas fa-calendar-check fa-3x mb-3 text-primary"></i>
                <h5 class="card-title fw-bold">Gerenciar Eventos</h5>
                <p class="card-text text-muted">Aprovar novos e validar edições.</p>
                <div class="d-flex flex-column gap-2 w-100 mt-auto">
                    <a href="/eventos/admin/events" class="btn btn-primary rounded-pill px-4">
                        Novos <span class="badge bg-white text-primary ms-1"><?php echo count($pendingEvents); ?></span>
                    </a>
                    <a href="/eventos/admin/listProposals" class="btn btn-outline-primary rounded-pill px-4">
                        Edições <span class="badge bg-primary ms-1"><?php echo $pendingProposalsCount; ?></span>
                    </a>
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
                         <div class="mt-3 d-flex gap-2">
                            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2 border border-info border-opacity-25">
                                <?php echo $userCount; ?> Total
                            </span>
                            <?php if ($pendingUsersCount > 0): ?>
                            <span class="badge bg-danger rounded-pill px-3 py-2 animate-pulse">
                                <?php echo $pendingUsersCount; ?> Pendentes
                            </span>
                            <?php endif; ?>
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
                        <i class="fas fa-bullhorn fa-3x mb-3 text-secondary"></i>
                        <h5 class="card-title fw-bold">Bloqueios e Destaques</h5>
                        <p class="card-text text-muted">Avisos e disponibilidade no calendário.</p>
                        <div class="d-flex flex-column gap-2 w-100 mt-auto">
                            <a href="/eventos/admin/block" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-ban me-1"></i> Bloquear Local
                            </a>
                            <a href="/eventos/admin/highlights" class="btn btn-outline-dark rounded-pill px-4">
                                <i class="fas fa-star me-1"></i> Destaques
                            </a>
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
            <div class="col-12 col-sm-6 col-lg-4 mb-4 extra-card collapse d-md-block">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column align-items-center">
                        <i class="fas fa-chart-pie fa-3x mb-3 text-info"></i>
                        <h5 class="card-title fw-bold">Analytics</h5>
                        <p class="card-text text-muted">Estatísticas e gráficos.</p>
                        <a href="/eventos/admin/analytics" class="btn btn-outline-info rounded-pill px-5 mt-auto">Visualizar</a>
                        <div class="mt-3">
                             <span class="badge bg-warning text-dark border border-dark rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($realizedHours ?? 0); ?>h Realizadas
                             </span>
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

@keyframes pulse {
  0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
  70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down {
    animation: slideDown 0.6s ease forwards;
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