<?php
$title = 'Registrar';
ob_start();
?>
<h1>Registrar</h1>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<form method="POST" action="/eventos/auth/register" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <div class="col-md-6">
        <label for="name" class="form-label">Nome</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label">Senha</label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Registrar</button>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';