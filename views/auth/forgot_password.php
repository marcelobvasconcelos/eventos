<?php
$title = 'Recuperar Senha';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-primary text-white text-center py-4">
                <h3 class="font-weight-light my-1 fw-bold"><i class="fas fa-key me-2"></i>Recuperar Senha</h3>
                <p class="mb-0 small text-white-50">Informe seu e-mail para receber o link de redefinição.</p>
            </div>
            <div class="card-body p-5">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                         <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                         <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/eventos/auth/forgot_password">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-floating mb-4">
                        <input type="email" name="email" id="email" class="form-control rounded-pill" placeholder="name@example.com" required>
                        <label for="email"><i class="fas fa-envelope me-2 text-muted"></i>Email Cadastrado</label>
                    </div>

                    <div class="d-grid gap-2">
                         <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Link
                         </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3 bg-light border-0">
                <div class="small"><a href="/eventos/auth/login" class="text-decoration-none">Voltar para Login</a></div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
