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
    <?php 
    // Navigation Logic & Data Fetching (Available for both Mobile and Desktop)
    require_once __DIR__ . '/../models/Config.php';
    require_once __DIR__ . '/../models/Event.php';
    require_once __DIR__ . '/../models/PendingItem.php';

    $configModel = new Config();
    $globalConfigs = $configModel->getAll();
    
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    function isActive($uri, $path) {
        return strpos($uri, $path) !== false ? 'active' : '';
    }
    $isHome = ($uri == '/eventos/' || $uri == '/eventos/index.php' || $uri == '/eventos/?');

    // Badges Counts
    $pendingEventsCount = 0;
    $adminPendingCount = 0;
    $userPendingCount = 0;

    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['user_role'] == 'admin') {
            $tempEventModel = new Event();
            $pendingEventsCount = $tempEventModel->getPendingEventsCount();
            
            $tempPendingModel = new PendingItem();
            $adminPendingCount = $tempPendingModel->getAllPendingCount();
        }
        
        $tempPendingModel = new PendingItem(); // Re-instantiate or reuse
        $userPendingCount = $tempPendingModel->getPendingCountByUser($_SESSION['user_id']);
    }
    ?>

    <!-- Mobile Hamburger Button -->
    <button class="btn btn-link text-dark position-absolute top-0 start-0 m-3 p-2 d-lg-none" style="z-index: 1050; font-size: 1.5rem;" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile Fullscreen Menu Overlay -->
    <div id="mobileMenuOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex flex-column" style="z-index: 1060; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); overflow-y: auto;">
        <!-- Close Button -->
        <button class="btn btn-link text-white position-absolute top-0 end-0 m-3 p-2" style="font-size: 2rem; z-index: 1070;" onclick="toggleMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="container py-5 d-flex flex-column align-items-center justify-content-center min-vh-100">
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- User Profile Section -->
                <div class="text-center mb-5 animate-slide-down">
                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 80px; height: 80px; color: #1e3c72; font-size: 2rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-0">Olá, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></h3>
                    <small class="text-white-50"><?php echo htmlspecialchars($_SESSION['user_role'] === 'admin' ? 'Administrador' : 'Usuário'); ?></small>
                </div>
            <?php else: ?>
                 <!-- Guest Header -->
                 <div class="text-center mb-5 animate-slide-down">
                    <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; color: #fff; font-size: 2rem; backdrop-filter: blur(5px);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="fw-bold text-white mb-0">Bem-vindo</h3>
                    <small class="text-white-50">Explore nossos eventos</small>
                </div>
            <?php endif; ?>

            <!-- Menu Links -->
            <div class="d-flex flex-column w-100 px-4 gap-3">
                <a href="/eventos/" class="mobile-menu-link" onclick="toggleMobileMenu()">
                    <div class="icon-box"><i class="fas fa-home"></i></div>
                    <span class="flex-grow-1">Início</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>
                
                <a href="/eventos/public/calendar" class="mobile-menu-link" onclick="toggleMobileMenu()">
                    <div class="icon-box"><i class="fas fa-calendar-alt"></i></div>
                    <span class="flex-grow-1">Calendário</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/eventos/auth/login" class="mobile-menu-link highlight-link mt-2" onclick="toggleMobileMenu()">
                        <div class="icon-box"><i class="fas fa-sign-in-alt"></i></div>
                        <span class="flex-grow-1">Fazer Login</span>
                    </a>
                <?php else: ?>
                    <a href="/eventos/request/form" class="mobile-menu-link" onclick="toggleMobileMenu()">
                        <div class="icon-box"><i class="fas fa-calendar-plus"></i></div>
                        <span class="flex-grow-1">Solicitar Evento</span>
                    </a>
                    
                    <a href="/eventos/request/my_requests" class="mobile-menu-link" onclick="toggleMobileMenu()">
                        <div class="icon-box"><i class="fas fa-list-ul"></i></div>
                        <span class="flex-grow-1">Minhas Requisições</span>
                    </a>

                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <div class="menu-divider text-white-50 small text-uppercase fw-bold mt-3 mb-2">Administração</div>
                        
                        <a href="/eventos/admin/dashboard" class="mobile-menu-link" onclick="toggleMobileMenu()">
                            <div class="icon-box"><i class="fas fa-tachometer-alt"></i></div>
                            <span class="flex-grow-1">Painel Admin</span>
                            <?php if ($pendingEventsCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $pendingEventsCount; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="/eventos/pending" class="mobile-menu-link" onclick="toggleMobileMenu()">
                            <div class="icon-box"><i class="fas fa-exclamation-circle"></i></div>
                            <span class="flex-grow-1">Pendências</span>
                            <?php if ($adminPendingCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $adminPendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <div class="menu-divider text-white-50 small text-uppercase fw-bold mt-3 mb-2">Conta</div>

                    <a href="/eventos/pending/myPending" class="mobile-menu-link" onclick="toggleMobileMenu()">
                        <div class="icon-box"><i class="fas fa-clipboard-check"></i></div>
                        <span class="flex-grow-1">Minhas Pendências</span>
                         <?php if ($userPendingCount > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $userPendingCount; ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="/eventos/auth/logout" class="mobile-menu-link logout-link mt-3" onclick="toggleMobileMenu()">
                        <div class="icon-box"><i class="fas fa-sign-out-alt"></i></div>
                        <span class="flex-grow-1">Sair do Sistema</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="mt-5 text-white-50 small">
                &copy; <?php echo date('Y'); ?> UAST Eventos
            </div>
        </div>
    </div>

    <!-- Desktop Navbar (Hidden on Mobile) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm d-none d-lg-block">
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
                    <li class="nav-item"><a class="nav-link <?php echo $isHome ? 'active' : ''; ?>" href="/eventos/"><i class="fas fa-home"></i>Início</a></li>
                    
                    <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/public/'); ?>" href="/eventos/public/calendar"><i class="fas fa-calendar-alt"></i>Calendário</a></li>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/auth/login'); ?>" href="/eventos/auth/login"><i class="fas fa-sign-in-alt"></i>Entrar</a></li>

                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/request/form'); ?>" href="/eventos/request/form"><i class="fas fa-calendar-plus"></i>Solicitar Evento</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo isActive($uri, '/eventos/request/my_requests'); ?>" href="/eventos/request/my_requests"><i class="fas fa-list-ul"></i>Minhas Requisições</a></li>
                        <!-- Equipamentos and Locais removed from navbar as per request -->
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($uri == '/eventos/admin/dashboard' || $uri == '/eventos/admin/') ? 'active' : ''; ?>" href="/eventos/admin/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Painel Admin
                                    <?php if ($pendingEventsCount > 0): ?>
                                        <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.7em; vertical-align: top;"><?php echo $pendingEventsCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <!-- Settings Link removed from navbar, moved to Admin Dashboard -->
                            
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
    <!-- Footer -->
    <footer class="mt-auto" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; font-family: 'Poppins', sans-serif; font-size: 0.85rem;">
        <div class="container py-3">
            <div class="row g-3 align-items-center">
                <!-- Coluna 1: Identidade (Logos + Título) - Mobile Toggle -->
                <div class="col-lg-2 col-md-12 text-center text-lg-start border-end-lg position-relative" 
                     style="border-right-color: #dee2e6; cursor: pointer;" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#footerContent" 
                     aria-expanded="false" 
                     aria-controls="footerContent">
                     
                    <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-2 mb-2">
                        <?php 
                        $logo1 = !empty($globalConfigs['footer_logo_1']) ? '/eventos/' . $globalConfigs['footer_logo_1'] : '/eventos/lib/ufrpe.jpeg'; 
                        $logo2 = !empty($globalConfigs['footer_logo_2']) ? '/eventos/' . $globalConfigs['footer_logo_2'] : '/eventos/lib/eventos.jpeg'; 
                        ?>
                        <img src="<?php echo htmlspecialchars($logo1); ?>" alt="Logo 1" style="height: 40px; object-fit: contain;">
                        <span class="text-muted opacity-25">|</span>
                        <img src="<?php echo htmlspecialchars($logo2); ?>" alt="Logo 2" style="height: 40px; object-fit: contain;">
                    </div>
                    <div class="fw-bold text-dark lh-1">
                        <?php echo htmlspecialchars($globalConfigs['footer_col1_title'] ?? 'Seção de Eventos'); ?>
                        <i class="fas fa-chevron-down ms-2 d-lg-none text-muted small" id="footerChevron"></i>
                    </div>
                    <div class="small text-secondary"><?php echo htmlspecialchars($globalConfigs['footer_col1_subtitle'] ?? 'UAST / UFRPE'); ?></div>
                </div>

                <!-- Collapsible Wrapper for Mobile -->
                <div class="col-lg-10 col-12 collapse d-lg-block" id="footerContent">
                    <div class="row g-3 align-items-center">
                        <!-- Coluna 2: Endereço (Compacto) -->
                        <div class="col-lg-6 col-md-6 text-center text-lg-start px-lg-4 border-end-lg" style="border-right-color: #dee2e6;">
                            <div class="d-flex align-items-start justify-content-center justify-content-lg-start gap-2">
                                <i class="fas fa-map-marker-alt text-danger mt-1"></i>
                                <div class="lh-sm text-secondary">
                                    <?php echo $globalConfigs['footer_address'] ?? 'Endereço não definido'; // Allow HTML ?>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna 3: Contatos e Redes -->
                        <div class="col-lg-6 col-md-6">
                            <div class="row g-2">
                                <!-- Redes -->
                                <div class="col-6 text-center text-lg-start">
                                    <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Redes Sociais</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <?php if (!empty($globalConfigs['footer_social_instagram'])): ?>
                                        <a href="<?php echo htmlspecialchars($globalConfigs['footer_social_instagram']); ?>" target="_blank" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-center justify-content-lg-start">
                                            <i class="fab fa-instagram text-danger me-1"></i> Instagram
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($globalConfigs['footer_social_youtube'])): ?>
                                        <a href="<?php echo htmlspecialchars($globalConfigs['footer_social_youtube']); ?>" target="_blank" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-center justify-content-lg-start">
                                            <i class="fab fa-youtube text-danger me-1"></i> YouTube
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Contatos -->
                                <div class="col-6 text-center text-lg-start border-start" style="border-color: #dee2e6;">
                                     <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Fale Conosco</h6>
                                     <div class="d-flex flex-column gap-1">
                                        <?php if(!empty($globalConfigs['footer_email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($globalConfigs['footer_email']); ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center justify-content-center justify-content-lg-start" title="Enviar E-mail">
                                            <i class="far fa-envelope text-primary me-1"></i>Email
                                        </a>
                                        <?php endif; ?>
                                        <?php if(!empty($globalConfigs['footer_phone'])): ?>
                                        <span class="text-secondary d-inline-flex align-items-center justify-content-center justify-content-lg-start">
                                            <i class="fab fa-whatsapp text-success me-1"></i><?php echo htmlspecialchars($globalConfigs['footer_phone']); ?>
                                        </span>
                                        <?php endif; ?>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const footerContent = document.getElementById('footerContent');
        const footerChevron = document.getElementById('footerChevron');
        
        if (footerContent && footerChevron) {
            footerContent.addEventListener('show.bs.collapse', function () {
                footerChevron.classList.remove('fa-chevron-down');
                footerChevron.classList.add('fa-chevron-up');
            });
            footerContent.addEventListener('hide.bs.collapse', function () {
                footerChevron.classList.remove('fa-chevron-up');
                footerChevron.classList.add('fa-chevron-down');
            });
        }
    });
    </script>

        <!-- Rodapé Inferior -->
        <div class="py-2 bg-secondary bg-opacity-10 border-top border-secondary border-opacity-10">
            <div class="container text-center">
                <small style="font-size: 0.75rem;">
                     <?php 
                        if (!empty($globalConfigs['footer_text'])) {
                            echo $globalConfigs['footer_text']; 
                        } else {
                            echo 'Desenvolvido pelo <a href="http://uast.ufrpe.br/sti" target="_blank" class="fw-bold text-decoration-none text-dark">STI-UAST</a>';
                        }
                    ?>
                </small>
            </div>
        </div>
    </footer>
    
    <style>
        @media (min-width: 992px) {
            .border-end-lg {
                border-right: 1px solid #dee2e6;
            }
        }
    </style>
    
    <style>
    /* Mobile Menu Elegant Styles */
    .mobile-menu-link {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        font-size: 1.1rem;
        font-weight: 500;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    .mobile-menu-link:nth-child(1) { animation-delay: 0.1s; }
    .mobile-menu-link:nth-child(2) { animation-delay: 0.2s; }
    .mobile-menu-link:nth-child(3) { animation-delay: 0.3s; }
    .mobile-menu-link:nth-child(4) { animation-delay: 0.4s; }
    .mobile-menu-link:nth-child(5) { animation-delay: 0.5s; }
    
    .mobile-menu-link:hover, .mobile-menu-link:active {
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
        color: white;
        border-color: rgba(255, 255, 255, 0.3);
    }
    
    .mobile-menu-link .icon-box {
        width: 30px;
        display: flex;
        justify-content: center;
        margin-right: 15px;
        color: #89cff0; /* Light blue accent */
    }
    
    .mobile-menu-link.highlight-link {
        background: linear-gradient(90deg, #ff9966, #ff5e62);
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(255, 94, 98, 0.3);
    }
    .mobile-menu-link.highlight-link .icon-box { color: white; }
    
    .mobile-menu-link.logout-link {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #ffcccc;
    }
    .mobile-menu-link.logout-link .icon-box { color: #ff6b6b; }
    
    .animate-slide-down {
        animation: slideDown 0.6s ease forwards;
    }
    
    .menu-divider {
        opacity: 0;
        animation: fadeInUp 0.5s ease forwards 0.3s;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.1); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CropperJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="/eventos/public/js/image_cropper.js?v=<?php echo time(); ?>"></script>
    <script>
        function toggleMobileMenu() {
            const overlay = document.getElementById('mobileMenuOverlay');
            if (overlay.classList.contains('d-none')) {
                overlay.classList.remove('d-none');
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.add('d-none');
                document.body.style.overflow = '';
            }
        }
    </script>
    <style>
        /* Mobile Footer Styling */
        @media (max-width: 991px) {
            footer {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
                color: white !important;
                border-top: none !important;
            }
            footer .text-dark, footer .text-secondary, footer .text-muted {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            footer .fw-bold.text-dark {
                color: white !important;
            }
            /* Social Icons on Mobile Footer */
            footer .text-danger, footer .text-primary, footer .text-success {
                color: white !important; /* Make icons white or keep colored? White usually looks better on dark blue */
                opacity: 0.9;
            }
            /* Chevron */
            #footerChevron {
                color: white !important;
            }
            /* Lower Footer */
            footer .bg-secondary.bg-opacity-10 {
                background-color: rgba(0, 0, 0, 0.2) !important;
                border-top-color: rgba(255, 255, 255, 0.1) !important;
            }
            footer a {
                color: rgba(255, 255, 255, 0.9) !important;
            }
        }
    </style>
</body>
</html>