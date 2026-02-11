# Esquema do Banco de Dados para o Sistema de Gestão de Eventos

Este documento descreve o esquema do banco de dados para o Sistema de Gestão de Eventos. O esquema inclui tabelas para usuários, eventos, solicitações de eventos, patrimônio, empréstimos e notificações. Todas as tabelas usam o motor InnoDB para suporte à integridade referencial.

## Visão Geral das Tabelas

- **users**: Armazena informações dos usuários, incluindo papéis (usuário/admin).
- **events**: Armazena detalhes dos eventos.
- **event_requests**: Gerencia as solicitações de eventos feitas pelos usuários, com aprovação/rejeição por administradores.
- **assets**: Gerencia o patrimônio do evento (ex: equipamentos, materiais).
- **loans**: Rastreia empréstimos de patrimônio para usuários em eventos específicos.
- **notifications**: Armazena notificações enviadas aos usuários.

## Definições Detalhadas das Tabelas

### users (Usuários)
| Coluna       | Tipo          | Restrições                   | Descrição                            |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único do usuário      |
| name         | VARCHAR(255)  | NOT NULL                     | Nome completo do usuário             |
| email        | VARCHAR(255)  | NOT NULL, UNIQUE             | Endereço de e-mail (único)           |
| password     | VARCHAR(255)  | NOT NULL                     | Senha hash criptografada             |
| role         | ENUM('user', 'admin') | NOT NULL, DEFAULT 'user' | Papel do usuário                     |
| created_at   | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data de criação da conta            |

### events (Eventos)
| Coluna       | Tipo          | Restrições                   | Descrição                            |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único do evento       |
| name         | VARCHAR(255)  | NOT NULL                     | Nome do evento                       |
| description  | TEXT          |                              | Descrição do evento                  |
| date         | DATETIME      | NOT NULL                     | Data e hora agendada                 |
| location     | VARCHAR(255)  |                              | Local do evento                      |
| status       | ENUM('Pendente', 'Aprovado', 'Rejeitado', 'Concluido') | NOT NULL, DEFAULT 'Pendente' | Status do evento                     |
| created_by   | INT           | NOT NULL, FOREIGN KEY (users.id) | Usuário que criou o evento          |
| created_at   | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data de criação do evento           |

### event_requests (Solicitações de Eventos)
| Coluna       | Tipo          | Restrições                   | Descrição                            |
|--------------|---------------|------------------------------|--------------------------------------|
| id           | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único da solicitação  |
| user_id      | INT           | NOT NULL, FOREIGN KEY (users.id) | Usuário solicitante                  |
| event_id     | INT           | NOT NULL, FOREIGN KEY (events.id) | Evento solicitado                    |
| status       | ENUM('Pendente', 'Aprovado', 'Rejeitado') | NOT NULL, DEFAULT 'Pendente' | Status da solicitação                |
| request_date | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data da submissão da solicitação     |
| approved_by  | INT           | FOREIGN KEY (users.id)       | Admin que aprovou/rejeitou (pode ser nulo) |
| approved_at  | TIMESTAMP     |                              | Data de aprovação/rejeição (pode ser nula) |

### assets (Patrimônio)
| Coluna             | Tipo          | Restrições                   | Descrição                            |
|--------------------|---------------|------------------------------|--------------------------------------|
| id                 | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único do patrimônio   |
| name               | VARCHAR(255)  | NOT NULL                     | Nome do patrimônio                   |
| description        | TEXT          |                              | Descrição do patrimônio              |
| quantity           | INT           | NOT NULL, DEFAULT 1          | Quantidade total disponível          |
| available_quantity | INT           | NOT NULL, DEFAULT 1          | Quantidade atualmente disponível     |
| created_at         | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data de criação do patrimônio       |

### loans (Empréstimos)
| Coluna      | Tipo          | Restrições                   | Descrição                            |
|-------------|---------------|------------------------------|--------------------------------------|
| id          | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único do empréstimo   |
| asset_id    | INT           | NOT NULL, FOREIGN KEY (assets.id) | Patrimônio emprestado                |
| user_id     | INT           | NOT NULL, FOREIGN KEY (users.id) | Usuário que pegou emprestado         |
| event_id    | INT           | NOT NULL, FOREIGN KEY (events.id) | Evento para o qual foi emprestado    |
| loan_date   | DATETIME      | NOT NULL                     | Data de início do empréstimo         |
| return_date | DATETIME      |                              | Data de retorno prevista/real (pode ser nula) |
| status      | ENUM('Emprestado', 'Devolvido') | NOT NULL, DEFAULT 'Emprestado' | Status do empréstimo                 |
| created_at  | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data de criação do empréstimo       |

### notifications (Notificações)
| Coluna     | Tipo          | Restrições                   | Descrição                            |
|------------|---------------|------------------------------|--------------------------------------|
| id         | INT           | PRIMARY KEY, AUTO_INCREMENT | Identificador único da notificação  |
| user_id    | INT           | NOT NULL, FOREIGN KEY (users.id) | Usuário destinatário                 |
| message    | TEXT          | NOT NULL                     | Mensagem da notificação              |
| sent_date  | TIMESTAMP     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Quando a notificação foi enviada    |
| is_read    | BOOLEAN       | NOT NULL, DEFAULT FALSE      | Se a notificação foi lida           |

## Relacionamentos e Integridade Referencial

- **users.id** referencia:
  - event_requests.user_id (CASCADE na exclusão)
  - event_requests.approved_by (SET NULL na exclusão)
  - events.created_by (RESTRICT na exclusão)
  - loans.user_id (CASCADE na exclusão)
  - notifications.user_id (CASCADE na exclusão)

- **events.id** referencia:
  - event_requests.event_id (CASCADE na exclusão)
  - loans.event_id (CASCADE na exclusão)

- **assets.id** referencia:
  - loans.asset_id (CASCADE na exclusão)

Todas as chaves estrangeiras impõem integridade referencial com as ações apropriadas (CASCADE para dependentes, RESTRICT/SET NULL onde for lógico).
