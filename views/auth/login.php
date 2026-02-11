<?php
$title = 'Entrar';
ob_start();
?>
<style>
    body {
        background-image: url('/eventos/public/img/audi.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
    }
    .card {
        background-color: rgba(255, 255, 255, 0.95) !important; /* Slight transparency for the card */
    }
</style>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-primary text-white text-center py-4" style="border-radius: 15px 15px 0 0;">
                <h3 class="font-weight-light my-1 fw-bold"><i class="fas fa-sign-in-alt me-2"></i>Entrar</h3>
                <p class="mb-0 small text-white-50">Bem-vindo de volta! Acesse sua conta.</p>
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
                
                <form method="POST" action="/eventos/auth/login">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo ?? ''); ?>">
                    
                    <div class="form-floating mb-3">
                        <input type="email" name="email" id="email" class="form-control rounded-pill" placeholder="name@example.com" required>
                        <label for="email"><i class="fas fa-envelope me-2 text-muted"></i>Email</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" name="password" id="password" class="form-control rounded-pill" placeholder="Password" required>
                        <label for="password"><i class="fas fa-lock me-2 text-muted"></i>Senha</label>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            Entrar <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="/eventos/auth/forgot_password" class="small text-decoration-none text-muted">Esqueceu sua senha?</a>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3 bg-light border-0" style="border-radius: 0 0 15px 15px;">
                <div class="small"><a href="/eventos/auth/register" class="text-decoration-none">Não tem uma conta? Cadastre-se!</a></div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';