<?php 
$title = 'Configurações Globais';
ob_start(); 
?>

<div class="row">
    <div class="col-md-12">
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="fw-bold text-primary mb-0"><i class="fas fa-cogs me-2"></i>Configurações Globais</h4>
            </div>
            <div class="card-body p-4">
                
                <form action="/eventos/settings/update" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab" aria-controls="images" aria-selected="true">
                                <i class="fas fa-images me-2"></i>Imagens
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab" aria-controls="content" aria-selected="false">
                                <i class="fas fa-file-alt me-2"></i>Conteúdo & Rodapé
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="configTabsContent">
                        
                        <!-- Images Tab -->
                        <div class="tab-pane fade show active" id="images" role="tabpanel" aria-labelledby="images-tab">
                            
                            <div class="mb-4">
                                <label for="home_banner_image" class="form-label fw-bold">Banner da Página Inicial</label>
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="border rounded p-2 text-center bg-light">
                                            <?php if (!empty($configs['home_banner_image'])): ?>
                                                <img src="/eventos/<?php echo htmlspecialchars($configs['home_banner_image']); ?>?t=<?php echo time(); ?>" class="img-fluid" style="max-height: 150px;" alt="Banner Atual">
                                                <div class="small text-muted mt-2">Imagem Atual</div>
                                            <?php else: ?>
                                                <div class="text-muted py-4">Sem imagem definida</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="file" class="form-control" id="home_banner_image" name="home_banner_image" accept=".jpg,.jpeg,.png,.webp">
                                        <div class="form-text">Recomendado: 1920x600px. Formatos: JPG, PNG, WEBP.</div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label for="event_card_default_image" class="form-label fw-bold">Imagem Padrão para Cards de Eventos</label>
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="border rounded p-2 text-center bg-light">
                                            <?php if (!empty($configs['event_card_default_image'])): ?>
                                                <img src="/eventos/<?php echo htmlspecialchars($configs['event_card_default_image']); ?>?t=<?php echo time(); ?>" class="img-fluid" style="max-height: 150px;" alt="Card Default">
                                                <div class="small text-muted mt-2">Imagem Atual</div>
                                            <?php else: ?>
                                                <div class="text-muted py-4">Sem imagem definida</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="file" class="form-control" id="event_card_default_image" name="event_card_default_image" accept=".jpg,.jpeg,.png,.webp">
                                        <div class="form-text">Usada quando o evento não tem imagem. Formatos: JPG, PNG, WEBP.</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Content Tab -->
                        <div class="tab-pane fade" id="content" role="tabpanel" aria-labelledby="content-tab">
                            
                            <div class="mb-3">
                                <label for="footer_text" class="form-label fw-bold">Texto do Rodapé</label>
                                <textarea class="form-control" id="footer_text" name="footer_text" rows="3"><?php echo htmlspecialchars($configs['footer_text']); ?></textarea>
                                <div class="form-text">Aceita HTML básico (ex: &lt;br&gt;, &copy;).</div>
                            </div>

                            <h5 class="mt-4 mb-3 text-secondary">Redes Sociais</h5>
                            
                            <div class="mb-3">
                                <label for="footer_social_instagram" class="form-label">Link do Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                    <input type="url" class="form-control" id="footer_social_instagram" name="footer_social_instagram" value="<?php echo htmlspecialchars($configs['footer_social_instagram']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="footer_social_facebook" class="form-label">Link do Facebook</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                    <input type="url" class="form-control" id="footer_social_facebook" name="footer_social_facebook" value="<?php echo htmlspecialchars($configs['footer_social_facebook']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="footer_social_youtube" class="form-label">Link do YouTube</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                    <input type="url" class="form-control" id="footer_social_youtube" name="footer_social_youtube" value="<?php echo htmlspecialchars($configs['footer_social_youtube']); ?>">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layout.php'; 
?>
