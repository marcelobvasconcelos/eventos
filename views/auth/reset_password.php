<?php
$title = 'Redefinir Senha';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-primary text-white text-center py-4">
                <h3 class="font-weight-light my-1 fw-bold"><i class="fas fa-lock me-2"></i>Definir Nova Senha</h3>
                <p class="mb-0 small text-white-50">Crie uma senha segura para sua conta.</p>
            </div>
            <div class="card-body p-5">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                         <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/eventos/auth/reset_password">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? $_POST['token']); ?>">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type'] ?? $_POST['type']); ?>">

                    <div class="form-floating mb-3">
                        <input type="password" name="password" id="password" class="form-control rounded-pill" placeholder="Nova Senha" required>
                        <label for="password"><i class="fas fa-lock me-2 text-muted"></i>Nova Senha</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control rounded-pill" placeholder="Confirme a Senha" required>
                        <label for="password_confirm"><i class="fas fa-check-circle me-2 text-muted"></i>Confirme a Senha</label>
                    </div>

                    <div class="d-grid gap-2">
                         <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i>Salvar Senha
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
