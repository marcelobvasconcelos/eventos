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
                                <i class="fas fa-file-alt me-2"></i>Conteúdo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" type="button" role="tab" aria-controls="footer" aria-selected="false">
                                <i class="fas fa-shoe-prints me-2"></i>Rodapé
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
                                <label for="event_creation_info_text" class="form-label fw-bold">Mensagem Informativa (Criação de Evento - Admin)</label>
                                <textarea class="form-control" id="event_creation_info_text" name="event_creation_info_text" rows="5"><?php echo htmlspecialchars($configs['event_creation_info_text']); ?></textarea>
                                <div class="form-text">Exibido no topo da página de criação de evento (Acesso Admin/Interno). Aceita HTML.</div>
                            </div>

                            <div class="mb-3">
                                <label for="request_info_text" class="form-label fw-bold">Mensagem Informativa (Solicitação de Evento - Público)</label>
                                <textarea class="form-control" id="request_info_text" name="request_info_text" rows="5"><?php echo htmlspecialchars($configs['request_info_text'] ?? ''); ?></textarea>
                                <div class="form-text">Exibido no topo da página de solicitação pública. Aceita HTML.</div>
                            </div>

                            <div class="mb-3">
                                <label for="normative_pdf" class="form-label fw-bold">Arquivo da Orientação Normativa (PDF)</label>
                                <input type="file" class="form-control" id="normative_pdf" name="normative_pdf" accept=".pdf">
                                <div class="form-text">Faça upload do PDF contendo a orientação normativa. Será exibido no modal de concordância.</div>
                                <?php if (!empty($configs['normative_pdf'])): ?>
                                    <div class="mt-2 text-success">
                                        <i class="fas fa-check-circle me-1"></i> Arquivo atual: 
                                        <a href="/eventos/<?php echo htmlspecialchars($configs['normative_pdf']); ?>" target="_blank" class="text-decoration-none fw-bold">Ver PDF Atual</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <!-- Footer Customization Tab -->
                        <div class="tab-pane fade" id="footer" role="tabpanel" aria-labelledby="footer-tab">
                            <h5 class="mb-3 text-secondary border-bottom pb-2">Coluna 1: Identidade</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Logo 1</label>
                                    <input type="file" class="form-control mb-2" name="footer_logo_1" accept="image/*">
                                    <?php if (!empty($configs['footer_logo_1'])): ?>
                                        <img src="/eventos/<?php echo htmlspecialchars($configs['footer_logo_1']); ?>" height="40" class="border rounded p-1">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Logo 2 (Quadrada)</label>
                                    <input type="file" class="form-control mb-2" name="footer_logo_2" accept="image/*" data-aspect-ratio="1">
                                    <?php if (!empty($configs['footer_logo_2'])): ?>
                                        <img src="/eventos/<?php echo htmlspecialchars($configs['footer_logo_2']); ?>" height="40" class="border rounded p-1">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="footer_col1_title" class="form-label fw-semibold">Título</label>
                                    <input type="text" class="form-control" id="footer_col1_title" name="footer_col1_title" value="<?php echo htmlspecialchars($configs['footer_col1_title'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="footer_col1_subtitle" class="form-label fw-semibold">Subtítulo</label>
                                    <input type="text" class="form-control" id="footer_col1_subtitle" name="footer_col1_subtitle" value="<?php echo htmlspecialchars($configs['footer_col1_subtitle'] ?? ''); ?>">
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3 text-secondary border-bottom pb-2">Coluna 2: Endereço</h5>
                            <div class="mb-3">
                                <label for="footer_address" class="form-label fw-semibold">Endereço Completo (HTML Permitido - use &lt;br&gt;)</label>
                                <textarea class="form-control" id="footer_address" name="footer_address" rows="4"><?php echo htmlspecialchars($configs['footer_address'] ?? ''); ?></textarea>
                            </div>

                            <h5 class="mt-4 mb-3 text-secondary border-bottom pb-2">Coluna 3: Contatos e Redes</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="footer_email" class="form-label fw-semibold">E-mail de Contato</label>
                                    <input type="email" class="form-control" id="footer_email" name="footer_email" value="<?php echo htmlspecialchars($configs['footer_email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="footer_phone" class="form-label fw-semibold">Telefone / WhatsApp</label>
                                    <input type="text" class="form-control" id="footer_phone" name="footer_phone" value="<?php echo htmlspecialchars($configs['footer_phone'] ?? ''); ?>">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label for="footer_social_instagram" class="form-label">Link do Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                    <input type="url" class="form-control" id="footer_social_instagram" name="footer_social_instagram" value="<?php echo htmlspecialchars($configs['footer_social_instagram']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="footer_social_youtube" class="form-label">Link do YouTube</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                    <input type="url" class="form-control" id="footer_social_youtube" name="footer_social_youtube" value="<?php echo htmlspecialchars($configs['footer_social_youtube']); ?>">
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3 text-secondary border-bottom pb-2">Rodapé Inferior (Socket)</h5>
                             <div class="mb-3">
                                <label for="footer_text" class="form-label fw-bold">Texto de Crédito (HTML)</label>
                                <textarea class="form-control" id="footer_text" name="footer_text" rows="2"><?php echo htmlspecialchars($configs['footer_text']); ?></textarea>
                                <div class="form-text">Ex: Desenvolvido pelo &lt;a href="..."&gt;STI&lt;/a&gt;</div>
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
