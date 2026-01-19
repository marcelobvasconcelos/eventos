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
            <a class="navbar-brand fw-bold text-primary" href="/">UAST</a>
            <form class="d-flex ms-3 flex-grow-1" style="max-width: 400px;">
                <input class="form-control rounded-pill" type="search" placeholder="Buscar..." aria-label="Search">
            </form>
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
                    <li class="nav-item"><a class="nav-link <?php echo $isHome ? 'active' : ''; ?>" href="/eventos/"><i class="fas fa-list"></i>Eventos</a></li>
                    
                    <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/public/'); ?>" href="/eventos/public/calendar"><i class="fas fa-calendar-alt"></i>Calendário</a></li>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/auth/login'); ?>" href="/eventos/auth/login"><i class="fas fa-sign-in-alt"></i>Entrar</a></li>

                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/request/'); ?>" href="/eventos/request/form"><i class="fas fa-calendar-plus"></i>Solicitar Evento</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/asset'); ?>" href="/eventos/asset"><i class="fas fa-boxes-stacked"></i>Ativos</a></li>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link <?php echo ($uri == '/eventos/admin/dashboard' || $uri == '/eventos/admin/') ? 'active' : ''; ?>" href="/eventos/admin/dashboard"><i class="fas fa-tachometer-alt"></i>Painel Admin</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/admin/events'); ?>" href="/eventos/admin/events"><i class="fas fa-calendar-check"></i>Gerenciar Eventos</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/admin/users'); ?>" href="/eventos/admin/users"><i class="fas fa-users"></i>Gerenciar Usuários</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="/eventos/auth/logout"><i class="fas fa-sign-out-alt"></i>Sair</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php echo $content; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>