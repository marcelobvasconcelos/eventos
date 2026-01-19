# Documento de Validação: Regras de Negócio e Nuances do Sistema

Este documento descreve comportamentos específicos, regras de negócio e lógicas implementadas no Sistema de Gestão de Eventos. O objetivo é apresentar esses pontos ao solicitante/gestor para validar se o funcionamento atual atende às expectativas ou se ajustes são necessários.

## 1. Visibilidade e Privacidade de Dados
### Regra Atual
A seção **"Ativos Solicitados"** (lista de equipamentos como Projetores, Cadeiras, Som) na página de detalhes do evento é **restrita**.
*   **Visível para**: Apenas o **Criador do Evento** e **Administradores**.
*   **Oculto para**: Visitantes (não logados) e outros usuários comuns.

> **Ponto de Validação**: Confirmar se essa informação deve ser privada. Em alguns cenários, pode ser útil para outros verem o que já está alocado, mas atualmente o sistema prioriza a privacidade dos recursos internos.

---

## 2. Reserva de Locais (Eventos Multidias)
### Regra Atual
O sistema permite eventos com **Data de Início** e **Data de Término** diferentes.
*   **Comportamento**: O bloqueio do local é **contínuo** entre a data/hora inicial e final.
*   **Exemplo**: Um evento que vai de 01/01 às 08:00 até 03/01 às 18:00 ocupará o local **24 horas por dia** durante esse período.
*   **Consequência**: Não é possível agendar um evento menor no "meio" desse período (ex: dia 02/01 à noite), pois o local é considerado ocupado pelo evento principal.

> **Ponto de Validação**: Esse bloqueio total é o desejado? Ou o sistema deveria permitir que eventos "multidias" ocupem apenas horários específicos em dias consecutivos (ex: 08h-18h todo dia)? *Nota: A implementação atual é de bloqueio total (estilo "evento imersivo" ou "bloqueio de sala").*

---

## 3. Gestão de Estoque de Ativos (Equipamentos)
### Regra Atual
O controle de estoque é baseado em **horário exato** e **quantidade**.
*   **Disponibilidade Real**: Se existem 5 projetores e 2 estão reservados das 14:00 às 16:00, o sistema permite reservar os outros 3 para o mesmo horário.
*   **Devolução Imediata**: Assim que o horário de término de um evento passa (ex: 16:00), o ativo é considerado "liberado" para um evento que comece às 16:00 ou 16:01.

> **Ponto de Validação**: Existe necessidade de um "tempo de margem" (buffer) para conferência ou manutenção entre devolução e novo empréstimo? Atualmente a liberação é imediata.

---

## 4. Fluxo de Aprovação e Rastreabilidade
### Regra Atual
O sistema registra a identidade exata do administrador que realizou a ação de aprovação.
*   **Exibição**: Nos detalhes do evento, aparece "Aprovado Por: [Nome do Administrador]" (visível para criador/admin).
*   **Edição Posterior**: Se um evento aprovado for editado/reprovado, o histórico pode ser alterado.

> **Ponto de Validação**: A transparência de *quem* aprovou é desejada?

---

## 5. Fluxo de Solicitação (Experiência do Usuário)
### Regra Atual
Para facilitar a adesão, o sistema permite interação parcial de usuários não logados.
*   **Fluxo**: Visitante clica no botão `+` de uma data no calendário -> Sistema pede Login -> Após login, usuário é **automaticamente redirecionado** para o formulário de criação já com a data que ele clicou preenchida.

> **Ponto de Validação**: Este fluxo visa reduzir a fricção de uso. Está aprovado?

---

## 6. Categorias e Organização
### Regra Atual
A seleção de categoria é obrigatória para relatórios e filtros.
*   **Lista**: As categorias são geridas apenas por administradores. Usuários não podem criar "tags" livres.

> **Ponto de Validação**: Manter a taxonomia controlada (apenas admin cria) é o modelo preferido para evitar poluição de dados?
