<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Eventos'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/eventos/public/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/eventos/">
                <img src="/eventos/public/img/logo.png" alt="UAST Logo" height="50">
            </a>
            <!-- Search removed as requested -->
            <div class="flex-grow-1"></div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php 
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
                            <li class="nav-item"><a class="nav-link <?php echo ($uri == '/eventos/admin/dashboard' || $uri == '/eventos/admin/') ? 'active' : ''; ?>" href="/eventos/admin/dashboard"><i class="fas fa-tachometer-alt"></i>Painel Admin</a></li>
                            <!-- Specific Admin links removed from navbar, accessible via Dashboard -->
                            
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
    <div class="container mt-4">
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
        <?php echo $content; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>