# Sistema de Gestão de Eventos

Uma aplicação web baseada em PHP para gerenciar eventos, solicitações de usuários, patrimônio e empréstimos.

## Funcionalidades

- Navegação pública de eventos e visualização de calendário
- Cadastro e autenticação de usuários
- Submissão de solicitações de eventos
- Gestão de patrimônio e sistema de empréstimos
- Painel administrativo para gerenciar eventos, usuários e solicitações
- Notificações por e-mail

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web Apache (recomendado com XAMPP)
- Composer (para gestão de dependências, se necessário)

## Instalação

1. Clone ou baixe o projeto para o diretório raiz do seu servidor web (ex: `htdocs` no XAMPP).

2. Certifique-se de que o projeto esteja em um diretório acessível via web, ex: `http://localhost/eventos`.

## Configuração do Banco de Dados

1. Crie um banco de dados MySQL chamado `eventos`.

2. Execute o arquivo `schema.sql` para criar as tabelas necessárias:
   - Abra o phpMyAdmin (se estiver usando XAMPP) ou seu cliente MySQL.
   - Selecione o banco de dados `eventos`.
   - Importe ou execute o arquivo `schema.sql`.

   Isso criará as seguintes tabelas:
   - users (usuários)
   - events (eventos)
   - event_requests (solicitações de eventos)
   - assets (patrimônio)
   - loans (empréstimos)
   - notifications (notificações)

## Configuração

### Configuração do Banco de Dados

A conexão com o banco de dados é configurada em `config/database.php`. As configurações padrão assumem:
- Host: localhost
- Database: eventos
- User: root
- Password: (vazio)

Atualize esses valores se sua configuração do MySQL for diferente.

### Configuração SMTP

Para notificações por e-mail, configure `config/email.php` com suas configurações SMTP:

```php
return [
    'host' => 'seu_host_smtp.com',
    'port' => 587, // ou 465 para SSL
    'username' => 'seu_email@dominio.com',
    'password' => 'sua_senha_email',
    'from_email' => 'naoresponda@seudominio.com',
    'from_name' => 'Sistema de Eventos'
];
```

Substitua os marcadores pelos detalhes reais do seu servidor SMTP.

## Executando a Aplicação

1. Inicie seu servidor web (Apache) e MySQL.

2. Abra seu navegador e acesse `http://localhost/eventos` (ajuste o caminho conforme necessário).

3. A aplicação carregará a página de eventos públicos por padrão.

## Uso

- **Usuários Públicos**: Navegar por eventos, ver calendário, registrar/entrar.
- **Usuários Registrados**: Enviar solicitações de eventos, ver/gerenciar patrimônio e empréstimos.
- **Administradores**: Acessar painel administrativo para aprovar eventos, gerenciar usuários e supervisionar o sistema.

## Estrutura de Arquivos

- `controllers/`: Controladores da aplicação
- `models/`: Modelos de dados
- `views/`: Templates de visualização
- `config/`: Arquivos de configuração
- `lib/`: Bibliotecas (ex: PHPMailer)
- `public/`: Assets estáticos (CSS, JS, etc.)

## Notas de Segurança

- Garanta permissões de arquivo adequadas.
- Use HTTPS em produção.
- Atualize regularmente as dependências.
- Valide e sanitize todas as entradas de usuário.

## Solução de Problemas

- Se encontrar erros de conexão com o banco de dados, verifique suas credenciais MySQL em `config/database.php`.
- Para problemas de e-mail, verifique as configurações SMTP e garanta que seu servidor permita conexões de saída na porta especificada.
- Ative o relatório de erros do PHP para depuração: adicione `ini_set('display_errors', 1);` ao `index.php` temporariamente.
