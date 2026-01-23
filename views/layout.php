<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Eventos'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <link rel="stylesheet" href="/eventos/public/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="/eventos/public/img/logo.png" type="image/png">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand py-0 me-2" href="/eventos/">
                <!-- Logo removida do menu conforme solicitado -->
            </a>
            <!-- Search removed as requested -->
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-grow-1" id="navbarNav">
                <ul class="navbar-nav w-100 justify-content-between">
                    <?php 
                    require_once __DIR__ . '/../models/Config.php';
                    $configModel = new Config();
                    $globalConfigs = $configModel->getAll();

                    $uri = $_SERVER['REQUEST_URI'] ?? '/';
                    // Simple helper to check if path contains string
                    function isActive($uri, $path) {
                        return strpos($uri, $path) !== false ? 'active' : '';
                    }
                    // Specific check for Home to avoid matching everything
                    $isHome = ($uri == '/eventos/' || $uri == '/eventos/index.php' || $uri == '/eventos/?');
                    ?>
                    <li class="nav-item"><a class="nav-link <?php echo $isHome ? 'active' : ''; ?>" href="/eventos/"><i class="fas fa-home"></i>Início</a></li>
                    
                    <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/public/'); ?>" href="/eventos/public/calendar"><i class="fas fa-calendar-alt"></i>Calendário</a></li>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/auth/login'); ?>" href="/eventos/auth/login"><i class="fas fa-sign-in-alt"></i>Entrar</a></li>

                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/request/form'); ?>" href="/eventos/request/form"><i class="fas fa-calendar-plus"></i>Solicitar Evento</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/request/my_requests'); ?>" href="/eventos/request/my_requests"><i class="fas fa-list-ul"></i>Minhas Requisições</a></li>
                        <!-- Equipamentos and Locais removed from navbar as per request -->
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <?php 
                                require_once __DIR__ . '/../models/Event.php';
                                $tempEventModel = new Event();
                                $pendingEventsCount = $tempEventModel->getPendingEventsCount();
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($uri == '/eventos/admin/dashboard' || $uri == '/eventos/admin/') ? 'active' : ''; ?>" href="/eventos/admin/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Painel Admin
                                    <?php if ($pendingEventsCount > 0): ?>
                                        <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.7em; vertical-align: top;"><?php echo $pendingEventsCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo isActive($uri, '/eventos/settings'); ?>" href="/eventos/settings">
                                    <i class="fas fa-cogs"></i> Configurações
                                </a>
                            </li>
                            
                            <?php 
                                require_once __DIR__ . '/../models/PendingItem.php';
                                $tempPendingModel = new PendingItem();
                                $adminPendingCount = $tempPendingModel->getAllPendingCount();
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($uri == '/eventos/pending' || $uri == '/eventos/pending/') ? 'active' : ''; ?>" href="/eventos/pending">
                                    <i class="fas fa-exclamation-circle"></i> Pendências
                                    <?php if ($adminPendingCount > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo $adminPendingCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user_role'] != 'admin' || true): // Allow admins to see personal pending items too ?>
                             <?php 
                                require_once __DIR__ . '/../models/PendingItem.php';
                                $tempPendingModel = new PendingItem();
                                $userPendingCount = $tempPendingModel->getPendingCountByUser($_SESSION['user_id']);
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo isActive($uri, '/eventos/pending/myPending'); ?>" href="/eventos/pending/myPending">
                                    <i class="fas fa-clipboard-check"></i> Minhas Pendências
                                    <?php if ($userPendingCount > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo $userPendingCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="/eventos/auth/logout"><i class="fas fa-sign-out-alt"></i>Sair</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4 flex-grow-1">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <!-- Cropper Modal -->
    <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Imagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="img-container" style="max-height: 500px; overflow: hidden;">
                        <img id="cropperImage" src="" style="max-width: 100%; display: block;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="cropBtn">Cortar e Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <?php echo $content; ?>
    </div>

    <!-- Footer -->
    <!-- Footer -->
    <footer class="bg-white py-3 border-top mt-auto shadow-sm">
        <div class="container">
            <div class="row align-items-center">
                <!-- Col 1: UAST Link & Address -->
                <div class="col-md-5 text-center text-md-start mb-2 mb-md-0">
                    <a href="http://uast.ufrpe.br" target="_blank" class="fw-bold text-primary text-decoration-none fs-5 d-block mb-1">
                        UAST <span class="text-secondary small fw-normal">| Unidade Acadêmica de Serra Talhada</span>
                    </a>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem; line-height: 1.2;">
                        Av. Gregório Ferraz Nogueira, S/N - José Tomé de Souza Ramos<br>
                        CEP: 56909-535 - Serra Talhada/PE
                    </p>
                </div>

                <!-- Col 2: Social Media (Brand Colors) -->
                <div class="col-md-3 text-center mb-2 mb-md-0">
                    <?php if (!empty($globalConfigs['footer_social_instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($globalConfigs['footer_social_instagram']); ?>" target="_blank" class="text-decoration-none me-3 hover-scale d-inline-block" title="Instagram">
                        <i class="fab fa-instagram fa-2x" style="background: -webkit-linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($globalConfigs['footer_social_facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($globalConfigs['footer_social_facebook']); ?>" target="_blank" class="text-decoration-none me-3 hover-scale d-inline-block" title="Facebook">
                        <i class="fab fa-facebook fa-2x" style="color: #3b5998;"></i>
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($globalConfigs['footer_social_youtube'])): ?>
                    <a href="<?php echo htmlspecialchars($globalConfigs['footer_social_youtube']); ?>" target="_blank" class="text-decoration-none hover-scale d-inline-block" title="YouTube">
                        <i class="fab fa-youtube fa-2x" style="color: #FF0000;"></i>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Col 3: Copyright -->
                <div class="col-md-4 text-center text-md-end">
                    <small class="text-muted" style="font-size: 0.8rem;">
                        <?php 
                            echo !empty($globalConfigs['footer_text']) 
                                ? $globalConfigs['footer_text'] 
                                : '&copy; ' . date('Y') . ' UAST/UFRPE<br>Todos os direitos reservados.'; 
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.1); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CropperJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="/eventos/public/js/image_cropper.js?v=<?php echo time(); ?>"></script>
</body>
</html>