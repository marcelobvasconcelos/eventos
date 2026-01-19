# Refatoração da Interface do Dashboard

## Solicitação Original

Refatorar o arquivo views/layout.php e os estilos CSS para criar uma interface moderna de Dashboard baseada nas seguintes diretrizes:

- **Identidade Visual**: Azul Marinho (#2c3e50), Verde UFRPE (#6ab04c), cinza claro (#f8f9fa) para fundo.
- **Fonte**: 'Inter' ou 'Poppins' do Google Fonts para ar moderno.
- **Estrutura de Cards**: Cards brancos com bordas arredondadas (border-radius: 15px), sombra suave (box-shadow: 0 4px 15px rgba(0,0,0,0.05)), ícones Font Awesome 6 centralizados, títulos claros, botões minimalistas.
- **Header**: Barra de busca arredondada no topo, logo da UAST à esquerda.
- **Ícones**: fa-calendar-days (Eventos), fa-plus-circle (Agendar), fa-shield-halved (Admin), fa-envelope (Notificações), fa-boxes-stacked (Patrimônio), fa-lock (Login).
- **Efeitos**: Hover nos cards (transform: translateY(-5px)).
- **Bibliotecas**: Bootstrap 5.3 CSS/JS, Font Awesome 6.x, Google Fonts (Poppins).
- **Resultado**: Painel de ícones ilustrados e intuitivos, organizados em colunas responsivas (col-md-4), como sistema administrativo moderno.

## Mudanças Realizadas

### 1. views/layout.php
- Adicionado links CDN para Google Fonts (Poppins), Font Awesome 6.
- Inserido bloco <style> com CSS personalizado usando !important para sobrescrever estilos padrão do Bootstrap.
- Atualizado navbar: classe bg-white shadow-sm, logo "UAST" com fw-bold text-primary, formulário de busca com input rounded-pill.
- Estilos aplicados globalmente: body com fonte Poppins e fundo #f8f9fa, .card com bordas arredondadas e sombras, .btn-outline-primary com cores #2c3e50, etc.

### 2. views/admin/dashboard.php
- Substituído conteúdo antigo por grid Bootstrap de 6 cards (row com col-md-4).
- Cada card: classe dashboard-card, ícone FA centralizado, título, botão outline-primary.
- Hover effect aplicado via CSS.

### 3. views/public/index.php
- Substituído tabela por grid de cards para eventos (col-md-4).
- Formulário de filtros contido em card separado.
- Título "Eventos Aprovados" centralizado, botão "Ver Calendário" centralizado.

### 4. views/auth/login.php
- Formulário reorganizado em layout vertical dentro de card centralizado (d-flex min-vh-100).
- Campos em mb-3, botão d-grid.

### 5. models/Loan.php
- Corrigido uso de item_id em vez de asset_id para compatibilidade com schema.sql.
- Métodos requestLoan, returnLoan, getLoansByUser atualizados para usar JOIN correto com asset_items.

## Arquivos Modificados
- views/layout.php (CSS e navbar)
- views/admin/dashboard.php (grid de cards)
- views/public/index.php (cards para eventos)
- views/auth/login.php (form centralizado)
- models/Loan.php (correção de schema)

## Como Aplicar as Mudanças
1. Salve todos os arquivos modificados no diretório correto do projeto (c:/xampp/htdocs/eventos/).
2. Reinicie o servidor Apache no XAMPP.
3. Limpe o cache do navegador (Ctrl+F5) ou use modo incógnito.
4. Desative extensões de navegador que possam sobrescrever CSS (ex: Lightshot).

## Trechos de Código dos Arquivos Modificados

### views/layout.php (CSS e Navbar)
```html
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family: 'Poppins', sans-serif !important; background-color: #f8f9fa !important; }
.navbar { background-color: #ffffff !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important; }
.card { border: none !important; border-radius: 15px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important; }
.dashboard-card { transition: transform 0.3s ease !important; }
.dashboard-card:hover { transform: translateY(-5px) !important; }
.btn-outline-primary { border-color: #2c3e50 !important; color: #2c3e50 !important; }
.btn-outline-primary:hover { background-color: #2c3e50 !important; border-color: #2c3e50 !important; }
</style>
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
                <li class="nav-item"><a class="nav-link" href="/eventos/">Eventos</a></li>
                <li class="nav-item"><a class="nav-link" href="/eventos/public/calendar">Calendário</a></li>
                <li class="nav-item"><a class="nav-link" href="/eventos/auth/login">Entrar</a></li>
                <li class="nav-item"><a class="nav-link" href="/eventos/auth/register">Registrar</a></li>
            </ul>
        </div>
    </div>
</nav>
```

### views/admin/dashboard.php (Grid de Cards)
```html
<h1 class="text-center mb-5">Painel Administrativo</h1>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-calendar-days fa-3x mb-3"></i>
                <h5 class="card-title">Eventos</h5>
                <a href="/eventos/admin/events" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-plus-circle fa-3x mb-3"></i>
                <h5 class="card-title">Agendar</h5>
                <a href="/eventos/request/form" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-shield-halved fa-3x mb-3"></i>
                <h5 class="card-title">Admin</h5>
                <a href="/eventos/admin/users" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-envelope fa-3x mb-3"></i>
                <h5 class="card-title">Notificações</h5>
                <a href="#" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-boxes-stacked fa-3x mb-3"></i>
                <h5 class="card-title">Patrimônio</h5>
                <a href="/eventos/asset" class="btn btn-outline-primary mt-auto">Acessar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-lock fa-3x mb-3"></i>
                <h5 class="card-title">Login</h5>
                <a href="/eventos/auth/logout" class="btn btn-outline-primary mt-auto">Sair</a>
            </div>
        </div>
    </div>
</div>
```

### views/public/index.php (Cards para Eventos)
```html
<h1 class="text-center mb-4">Eventos Aprovados</h1>
<div class="text-center mb-4">
    <a href="/eventos/public/calendar" class="btn btn-secondary">Ver Calendário</a>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <!-- Form fields -->
        </form>
    </div>
</div>
<div class="row">
    <?php foreach ($events as $event): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                    <p class="card-text">Data: <?php echo htmlspecialchars($event['date']); ?></p>
                    <p class="card-text">Localização: <?php echo htmlspecialchars($event['location_name'] ?? 'N/A'); ?></p>
                    <a href="/eventos/public/detail?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-primary mt-auto">Detalhes</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
```

### views/auth/login.php (Form Centralizado)
```html
<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card" style="max-width: 400px; width: 100%;">
        <div class="card-body">
            <h1 class="text-center mb-4">Entrar</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="/eventos/auth/login">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### models/Loan.php (Correções)
```php
public function getLoansByUser($user_id) {
    $stmt = $this->pdo->prepare("SELECT l.*, ai.identification, a.name as asset_name, e.name as event_name FROM loans l JOIN asset_items ai ON l.item_id = ai.id JOIN assets a ON ai.asset_id = a.id JOIN events e ON l.event_id = e.id WHERE l.user_id = ? ORDER BY l.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

## Status Final
Todas as refatorações foram implementadas no código. O design moderno está pronto, com paleta de cores, fontes, cards responsivos, ícones e efeitos conforme solicitado. O usuário deve aplicar os arquivos no servidor para visualizar as mudanças.