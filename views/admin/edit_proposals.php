<?php
$title = 'Validar Alterações de Eventos';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Propostas de Edição</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($proposals)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma proposta de edição pendente no momento.</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Data Solicit.</th>
                                    <th>Usuário</th>
                                    <th>Evento</th>
                                    <th>Mudanças Propostas</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proposals as $proposal): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($proposal['proposed_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($proposal['user_name'] ?? 'Usuário'); ?></td>
                                        <td>
                                            <span class="fw-bold"><?php echo htmlspecialchars($proposal['original_name'] ?? 'Evento'); ?></span><br>
                                            <small class="text-muted">ID: #<?php echo $proposal['event_id']; ?></small>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <ul class="list-unstyled mb-0">
                                                    <?php 
                                                    $fields = [
                                                        'name' => 'Título',
                                                        'description' => 'Descrição',
                                                        'date' => 'Data',
                                                        'start_time' => 'Início',
                                                        'end_time' => 'Término',
                                                        'category_id' => 'Categoria',
                                                        'is_public' => 'Privacidade',
                                                        'external_link' => 'Link Ext.',
                                                        'link_title' => 'Tít. Link',
                                                        'image_path' => 'Imagem',
                                                        'schedule_file_path' => 'Programação'
                                                    ];
                                                    
                                                    foreach ($fields as $key => $label): 
                                                        $val = $proposal[$key] ?? null;
                                                        $origKey = 'original_' . $key;
                                                        $origVal = $proposal[$origKey] ?? null;
                                                        
                                                        // Skip if value is strictly NULL (no change proposed)
                                                        if (!isset($proposal[$key]) || $proposal[$key] === null) continue;
                                                        
                                                        // Fallback for original value
                                                        if (!array_key_exists($origKey, $proposal)) continue;
                                                        
                                                        // Skip if they are effectively identical (to avoid whitespace-only changes)
                                                        if (trim((string)$val) === trim((string)$origVal)) continue;
                                                        
                                                        $displayOriginal = $origVal;
                                                        $displayNew = $val;
                                                        
                                                        // Formatting for specific fields
                                                        if ($key === 'category_id') {
                                                            $displayOriginal = $categoryMap[$origVal] ?? 'N/A';
                                                            $displayNew = $categoryMap[$val] ?? 'N/A';
                                                        } elseif ($key === 'is_public') {
                                                            $displayOriginal = $origVal ? 'Público' : 'Privado';
                                                            $displayNew = $val ? 'Público' : 'Privado';
                                                        } elseif ($key === 'date') {
                                                            $displayOriginal = date('d/m/Y', strtotime($origVal));
                                                            $displayNew = date('d/m/Y', strtotime($val));
                                                        }
                                                    ?>
                                                        <li class="mb-2 p-2 bg-light rounded border-start border-primary border-3">
                                                            <div class="fw-bold text-uppercase x-small text-muted mb-1" style="font-size: 0.65rem;"><?php echo $label; ?></div>
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($key === 'image_path'): ?>
                                                                    <div class="d-flex flex-column align-items-center me-2">
                                                                        <span class="x-small text-muted mb-1">Antes</span>
                                                                        <img src="<?php echo htmlspecialchars($origVal ?: '/eventos/public/img/no-image.png'); ?>" class="img-thumbnail" style="max-height: 50px;">
                                                                    </div>
                                                                    <i class="fas fa-arrow-right text-muted small me-2"></i>
                                                                    <div class="d-flex flex-column align-items-center">
                                                                        <span class="x-small text-muted mb-1">Depois</span>
                                                                        <img src="<?php echo htmlspecialchars($val); ?>" class="img-thumbnail border-success" style="max-height: 50px;">
                                                                    </div>
                                                                <?php elseif ($key === 'schedule_file_path'): ?>
                                                                    <div class="d-flex flex-column align-items-center me-2">
                                                                        <span class="x-small text-muted mb-1">Antes</span>
                                                                        <?php if ($origVal): ?>
                                                                            <a href="<?php echo htmlspecialchars($origVal); ?>" target="_blank" class="btn btn-xs btn-outline-danger shadow-sm">
                                                                                <i class="fas fa-file-pdf"></i>
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted small">Nenhum</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <i class="fas fa-arrow-right text-muted small me-2"></i>
                                                                    <div class="d-flex flex-column align-items-center">
                                                                        <span class="x-small text-muted mb-1">Depois</span>
                                                                        <a href="<?php echo htmlspecialchars($val); ?>" target="_blank" class="btn btn-xs btn-success shadow-sm">
                                                                            <i class="fas fa-file-pdf"></i>
                                                                        </a>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-danger text-decoration-line-through me-2"><?php echo htmlspecialchars($displayOriginal ?? 'Vazio'); ?></span>
                                                                    <i class="fas fa-arrow-right text-muted small me-2"></i>
                                                                    <span class="text-success fw-bold"><?php echo htmlspecialchars($displayNew ?? 'Vazio'); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </li>
                                                    <?php 
                                                    endforeach; 
                                                    ?>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <form method="POST" action="/eventos/admin/approveProposal" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Aprovar e Aplicar" onclick="return confirm('Tem certeza que deseja aplicar estas alterações ao evento original?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-sm btn-danger ms-1" title="Rejeitar" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $proposal['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?php echo $proposal['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="/eventos/admin/rejectProposal">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Rejeitar Alteração</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                                                <p>Deseja rejeitar a proposta de alteração de <strong><?php echo htmlspecialchars($proposal['user_name']); ?></strong>?</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Motivo (Opcional):</label>
                                                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Explique por que a alteração foi negada..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
