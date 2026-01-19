-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/01/2026 às 12:32
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `eventos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `assets`
--

INSERT INTO `assets` (`id`, `name`, `description`, `quantity`, `available_quantity`, `created_at`) VALUES
(1, 'Projector', 'High-definition projector', 2, 8, '2026-01-16 12:48:27'),
(2, 'Microphone', 'Wireless microphone', 10, 12, '2026-01-16 12:48:27'),
(3, 'Chairs', 'Foldable chairs', 100, 103, '2026-01-16 12:48:27'),
(4, 'Cadeira de Escritório', 'Cadeira ergonômica preta', 10, 10, '2026-01-16 18:19:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asset_items`
--

CREATE TABLE `asset_items` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `identification` varchar(255) NOT NULL,
  `status` enum('Dispon├¡vel','Emprestado') NOT NULL DEFAULT 'Dispon├¡vel',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asset_items`
--

INSERT INTO `asset_items` (`id`, `asset_id`, `identification`, `status`, `created_at`) VALUES
(1, 1, 'PROJ-001', '', '2026-01-16 12:48:27'),
(2, 1, 'PROJ-002', '', '2026-01-16 12:48:27'),
(3, 2, 'MIC-001', '', '2026-01-16 12:48:27'),
(4, 2, 'MIC-002', '', '2026-01-16 12:48:27'),
(5, 3, 'CHAIR-001', '', '2026-01-16 12:48:27'),
(6, 4, 'CAD-0004-001', '', '2026-01-16 18:19:06'),
(7, 4, 'CAD-0004-002', '', '2026-01-16 18:19:06'),
(8, 4, 'CAD-0004-003', '', '2026-01-16 18:19:06'),
(9, 4, 'CAD-0004-004', '', '2026-01-16 18:19:06'),
(10, 4, 'CAD-0004-005', '', '2026-01-16 18:19:06'),
(11, 4, 'CAD-0004-006', '', '2026-01-16 18:19:06'),
(12, 4, 'CAD-0004-007', '', '2026-01-16 18:19:06'),
(13, 4, 'CAD-0004-008', '', '2026-01-16 18:19:06'),
(14, 4, 'CAD-0004-009', '', '2026-01-16 18:19:06'),
(15, 4, 'CAD-0004-010', '', '2026-01-16 18:19:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Technology', 'Tech-related events', '2026-01-16 12:48:27'),
(2, 'Music', 'Music and entertainment events', '2026-01-16 12:48:27'),
(3, 'Education', 'Educational workshops and seminars', '2026-01-16 12:48:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado','Concluido') NOT NULL DEFAULT 'Pendente',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `date`, `end_date`, `location_id`, `category_id`, `status`, `created_by`, `created_at`, `approved_by`) VALUES
(2, 'Music Festival', 'Summer music festival', '2024-07-20 18:00:00', '2024-07-21 02:00:00', 2, 2, 'Aprovado', 2, '2026-01-16 12:48:27', NULL),
(3, 'Workshop on AI', 'Hands-on AI workshop', '2024-08-10 10:00:00', '2024-08-10 16:00:00', 1, 1, '', 1, '2026-01-16 12:48:27', NULL),
(4, 'dia dezessete evento', 'evento tal e coisa', '2026-01-17 08:00:00', NULL, 1, 3, 'Aprovado', 6, '2026-01-16 15:28:00', NULL),
(5, 'Event A', 'Testing projector availability.', '2026-02-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 15:40:02', NULL),
(6, 'Event B', 'Testing projector availability for the second booking.', '2026-02-01 14:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 15:40:19', NULL),
(7, 'outro evento simultâneo', 'yrdyr', '2026-02-17 09:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 15:43:51', NULL),
(8, 'evento simultaneo', 'teste', '2026-01-17 09:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 15:45:58', NULL),
(9, 'Event Morning', 'Test description', '2026-03-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:08:34', NULL),
(10, 'Event Afternoon', 'Afternoon event description', '2026-03-01 14:00:00', NULL, 3, 2, 'Aprovado', 7, '2026-01-16 16:09:40', NULL),
(11, 'Event Overlap', 'Overlap event description', '2026-03-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:18:36', NULL),
(12, 'Event Impossible', 'Impossible event description', '2026-03-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:23:13', NULL),
(13, 'Event Validation Test', 'Testing if assets are saved', '2026-03-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:26:43', NULL),
(14, 'Test Morning 1', 'Test description for morning event', '2026-04-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:47:54', NULL),
(15, 'Test Afternoon', 'Test afternoon event', '2026-04-01 14:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:53:36', NULL),
(16, 'Test Morning 2', 'Test morning 2 event', '2026-04-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:57:05', NULL),
(17, 'Test Failure', 'This should fail if availability check works', '2026-04-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 16:57:37', NULL),
(18, 'Event M1', 'Test Event M1', '2026-05-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:05:31', NULL),
(19, 'Event M1', 'Test Event M1', '2026-05-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:14:23', NULL),
(20, 'Event M2', 'Test Event M2', '2026-05-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:19:25', NULL),
(21, 'Event Fail', 'This should fail if availability check works', '2026-05-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:23:10', NULL),
(22, 'Event Fix 1', 'Test event 1', '2026-06-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:27:46', NULL),
(23, 'Event Fix 2', 'Test event 2', '2026-06-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:30:08', NULL),
(24, 'Event Fail', 'Test event fail', '2026-06-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:30:43', NULL),
(25, 'evento manual teste 01', 'Reunião de técnicos', '2026-01-21 09:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:33:15', NULL),
(26, 'evento cadeiras 99', 'cadeiras 100', '2026-01-21 09:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:35:07', NULL),
(27, 'Event Loc 1', 'Testing location overlap', '2026-07-01 10:00:00', NULL, 1, 1, 'Aprovado', 7, '2026-01-16 17:41:23', NULL),
(28, 'eventos', 'teats', '2026-07-01 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:47:24', NULL),
(29, 'Event A', 'Event A description', '2026-07-02 10:00:00', NULL, 1, 3, 'Aprovado', 7, '2026-01-16 17:52:06', NULL),
(30, 'Multi-Day Conference', 'Long event', '2026-08-01 09:00:00', '2026-08-03 17:00:00', 1, 1, 'Aprovado', 7, '2026-01-16 18:08:24', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `event_requests`
--

CREATE TABLE `event_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `status` enum('Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Pendente',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `loan_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('Emprestado','Devolvido') NOT NULL DEFAULT 'Emprestado',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `loans`
--

INSERT INTO `loans` (`id`, `item_id`, `user_id`, `event_id`, `loan_date`, `return_date`, `status`, `created_at`) VALUES
(3, 1, 7, 5, '2026-02-01 10:00:00', '2026-01-16 15:09:29', 'Devolvido', '2026-01-16 15:40:02'),
(4, 2, 7, 6, '2026-02-01 14:00:00', '2026-01-16 15:09:33', 'Devolvido', '2026-01-16 15:40:19'),
(5, 5, 7, 7, '2026-02-17 09:00:00', '2026-01-16 15:16:18', 'Devolvido', '2026-01-16 15:43:51'),
(6, 3, 7, 7, '2026-02-17 09:00:00', '2026-01-16 15:16:17', 'Devolvido', '2026-01-16 15:43:51'),
(7, 1, 7, 7, '2026-02-17 09:00:00', '2026-01-16 15:16:19', 'Devolvido', '2026-01-16 15:43:51'),
(8, 5, 7, 8, '2026-01-17 09:00:00', '2026-01-16 15:10:43', 'Devolvido', '2026-01-16 15:45:58'),
(9, 3, 7, 8, '2026-01-17 09:00:00', '2026-01-16 15:16:16', 'Devolvido', '2026-01-16 15:45:58'),
(10, 1, 7, 8, '2026-01-17 09:00:00', '2026-01-16 15:10:50', 'Devolvido', '2026-01-16 15:45:58'),
(11, 1, 7, 22, '2026-06-01 10:00:00', '2026-01-16 15:16:15', 'Devolvido', '2026-01-16 17:27:46'),
(12, 2, 7, 23, '2026-06-01 10:00:00', '2026-01-16 15:09:35', 'Devolvido', '2026-01-16 17:30:08'),
(13, 5, 7, 25, '2026-01-21 09:00:00', '2026-01-16 15:09:27', 'Devolvido', '2026-01-16 17:33:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `locations`
--

INSERT INTO `locations` (`id`, `name`, `description`, `capacity`, `created_at`) VALUES
(1, 'Main Auditorium', 'Large auditorium for conferences', 200, '2026-01-16 12:48:27'),
(2, 'Outdoor Stage', 'Open-air stage for events', 500, '2026-01-16 12:48:27'),
(3, 'Meeting Room A', 'Small meeting room', 20, '2026-01-16 12:48:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `sent_date`, `is_read`) VALUES
(1, 2, 'Your request for Tech Conference 2024 has been approved.', '2026-01-16 12:48:28', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$PtmD08Vyz3dnlBu1hEIRm.sz8522p0FiJtZ1v91Dm5NjXaoLkORYO', 'admin', '2026-01-16 12:48:27'),
(2, 'John Doe', 'john@example.com', '$2y$10$DsjsJQivT6KCOJ4BBnKn7ebi/YuZ46thp2YDGGN2GLDeLu9qrX9Um', 'user', '2026-01-16 12:48:27'),
(6, 'Teste Usuário', 'teste@example.com', '$2y$10$2102uPZiLcGDaJ1MP9qFO./KbgkM6Gf9WwgXVxrTat5Y5SqTW.492', 'user', '2026-01-16 14:51:21'),
(7, 'Marcelo', 'adm.ti.uast@ufrpe.br', '$2y$10$sGk9CtZh3zunO2hpjP9pUOnY3BRHpyyUbrZIrNYj1.Dga0T1wsRKK', 'admin', '2026-01-16 15:30:18');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `asset_items`
--
ALTER TABLE `asset_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Índices de tabela `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Índices de tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_event_approver` (`approved_by`);

--
-- Índices de tabela `event_requests`
--
ALTER TABLE `event_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Índices de tabela `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices de tabela `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Índices de tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `asset_items`
--
ALTER TABLE `asset_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `event_requests`
--
ALTER TABLE `event_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `asset_items`
--
ALTER TABLE `asset_items`
  ADD CONSTRAINT `asset_items_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_event_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `event_requests`
--
ALTER TABLE `event_requests`
  ADD CONSTRAINT `event_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_requests_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `asset_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
