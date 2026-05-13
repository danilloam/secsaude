-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 08/05/2026 às 20:18
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `secsauderecife`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cbos`
--

DROP TABLE IF EXISTS `cbos`;
CREATE TABLE IF NOT EXISTS `cbos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cod` int DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cbos`
--

INSERT INTO `cbos` (`id`, `cod`, `descricao`, `tipo`) VALUES
(1, 6904, 'MÉDICO DE FAMÍLIA E COMUNIDADE', 'individual'),
(2, 6879, 'MÉDICO DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'individual'),
(3, 6894, 'TÉCNICO DE ENFERMAGEM DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'individual'),
(4, 6924, 'MÉDICO RESIDENTE', 'individual'),
(5, 6890, 'ENFERMEIRO DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'individual'),
(6, 6883, 'TÉCNICO DE ENFERMAGEM', 'individual'),
(7, 6888, 'CIRURGIÃO-DENTISTA DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'odonto'),
(8, 6892, 'AUXILIAR EM SAÚDE BUCAL DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'odonto'),
(9, 6893, 'TÉCNICO EM SAÚDE BUCAL DA ESTRATÉGIA DE SAÚDE DA FAMÍLIA', 'odonto');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `descricao`, `valor`, `created_at`) VALUES
(1, 'USF+', '3000', '2026-04-28 13:36:37'),
(2, 'USF', '3500', '2026-04-28 13:36:37'),
(3, 'UBT', '0', NULL),
(4, 'odonto', '6888', NULL),
(5, 'ambiente', 'prod', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(191) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `distrito`
--

DROP TABLE IF EXISTS `distrito`;
CREATE TABLE IF NOT EXISTS `distrito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `distrito`
--

INSERT INTO `distrito` (`id`, `nome`) VALUES
(1, 'DISTRITO SANITARIO I'),
(2, 'DISTRITO SANITARIO II'),
(3, 'DISTRITO SANITARIO III'),
(4, 'DISTRITO SANITARIO IV'),
(5, 'DISTRITO SANITARIO V'),
(6, 'DISTRITO SANITARIO VI'),
(7, 'DISTRITO SANITARIO VII'),
(8, 'DISTRITO SANITARIO VIII');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ine_us`
--

DROP TABLE IF EXISTS `ine_us`;
CREATE TABLE IF NOT EXISTS `ine_us` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ine` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`login`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `login`, `ip_address`, `attempt_time`) VALUES
(45, 'damarques', '::1', '2026-04-28 14:30:56'),
(46, 'damarques', '::1', '2026-04-28 14:42:11'),
(47, 'damarques', '::1', '2026-04-28 17:00:07'),
(48, 'damarques', '172.22.25.153', '2026-04-28 19:58:18'),
(49, 'sferreira', '172.22.25.190', '2026-05-08 12:10:02'),
(50, 'sferreira', '172.22.25.190', '2026-05-08 12:10:08'),
(51, 'rgomes', '172.22.25.212', '2026-05-08 13:55:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

DROP TABLE IF EXISTS `mensagens`;
CREATE TABLE IF NOT EXISTS `mensagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `de_id` int NOT NULL,
  `para_id` int NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `visualizada` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('text','image','audio') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `mensagens`
--

INSERT INTO `mensagens` (`id`, `de_id`, `para_id`, `mensagem`, `visualizada`, `created_at`, `type`, `file_path`, `read_at`) VALUES
(5, 8, 1, 'Bom dia', 0, '2026-05-08 12:31:15', 'text', NULL, NULL),
(4, 1, 8, 'Bom dia', 0, '2026-05-08 12:30:54', 'text', NULL, NULL),
(6, 8, 1, 'Oi', 0, '2026-05-08 12:31:25', 'text', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `token` varchar(191) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`login`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `security_logs`
--

INSERT INTO `security_logs` (`id`, `user_id`, `event`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(2, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(3, 1, 'LOGIN', '172.22.25.145', '0000-00-00 00:00:00'),
(4, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(5, 1, 'LOGIN', '172.22.25.149', '0000-00-00 00:00:00'),
(6, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(7, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(8, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(9, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(10, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(11, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(12, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(13, 1, 'LOGIN', '172.22.25.164', '0000-00-00 00:00:00'),
(14, 1, 'LOGIN', '172.22.25.149', '0000-00-00 00:00:00'),
(15, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(16, 5, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(17, 1, 'LOGIN', '172.22.25.129', '0000-00-00 00:00:00'),
(18, 1, 'LOGIN', '172.22.25.85', '0000-00-00 00:00:00'),
(19, 1, 'LOGIN', '172.22.25.153', '0000-00-00 00:00:00'),
(20, 1, 'LOGIN', '172.22.25.125', '0000-00-00 00:00:00'),
(21, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(22, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(23, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(24, 1, 'LOGIN', '172.22.25.243', '0000-00-00 00:00:00'),
(25, 1, 'LOGIN', '172.22.25.173', '0000-00-00 00:00:00'),
(26, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(27, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(28, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(29, 1, 'LOGIN', '172.22.25.131', '0000-00-00 00:00:00'),
(30, 6, 'LOGIN', '172.22.25.77', '0000-00-00 00:00:00'),
(31, 6, 'LOGIN', '172.22.25.77', '0000-00-00 00:00:00'),
(32, 1, 'LOGIN', '172.22.25.85', '0000-00-00 00:00:00'),
(33, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(34, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(35, 7, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(36, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(37, 1, 'LOGIN', '172.22.25.198', '0000-00-00 00:00:00'),
(38, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(39, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(40, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(41, 8, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(42, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(43, 10, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(44, 10, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(45, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(46, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(47, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(48, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(49, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(50, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(51, 1, 'LOGIN', '172.22.25.102', '0000-00-00 00:00:00'),
(52, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(53, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(54, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(55, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(56, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(57, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(58, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(59, 8, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(60, 10, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(61, 7, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(62, 9, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(63, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(64, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(65, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(66, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(67, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(68, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(69, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(70, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(71, 5, 'LOGIN', '172.22.25.97', '0000-00-00 00:00:00'),
(72, 1, 'LOGIN', '172.22.25.69', '0000-00-00 00:00:00'),
(73, 1, 'LOGIN', '172.22.25.40', '0000-00-00 00:00:00'),
(74, 1, 'LOGIN', '172.22.25.109', '0000-00-00 00:00:00'),
(75, 1, 'LOGIN', '172.22.25.85', '0000-00-00 00:00:00'),
(76, 1, 'LOGIN', '172.22.25.85', '0000-00-00 00:00:00'),
(77, 12, 'LOGIN', '172.22.25.85', '0000-00-00 00:00:00'),
(78, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(79, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(80, 5, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(81, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(82, 5, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(83, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(84, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(85, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(86, 5, 'LOGIN', '172.22.25.97', '0000-00-00 00:00:00'),
(87, 1, 'LOGIN', '172.22.25.149', '0000-00-00 00:00:00'),
(88, 13, 'LOGIN', '172.22.25.149', '0000-00-00 00:00:00'),
(89, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(90, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(91, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(92, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(93, 1, 'LOGIN', '172.22.25.97', '0000-00-00 00:00:00'),
(94, 1, 'LOGIN', '172.22.25.43', '0000-00-00 00:00:00'),
(95, 1, 'LOGIN', '172.22.25.225', '0000-00-00 00:00:00'),
(96, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(97, 1, 'LOGIN', '172.22.25.134', '0000-00-00 00:00:00'),
(98, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(99, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(100, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(101, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(102, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(103, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(104, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(105, 8, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(106, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(107, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(108, 4, 'LOGIN', '172.22.25.190', '0000-00-00 00:00:00'),
(109, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(110, 8, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(111, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(112, 5, 'LOGIN', '172.22.25.60', '0000-00-00 00:00:00'),
(113, 1, 'LOGIN', '172.22.25.212', '0000-00-00 00:00:00'),
(114, 14, 'LOGIN', '172.22.25.212', '0000-00-00 00:00:00'),
(115, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(116, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(117, 1, 'LOGIN', '172.22.25.129', '0000-00-00 00:00:00'),
(118, 4, 'LOGIN', '172.22.25.190', '0000-00-00 00:00:00'),
(119, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(120, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(121, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(122, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(123, 1, 'LOGIN', '::1', '0000-00-00 00:00:00'),
(124, 1, 'LOGIN', '::1', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `sobrenome` varchar(255) NOT NULL,
  `login` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `distrito_id` int NOT NULL,
  `unidade_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `sobrenome`, `login`, `password`, `distrito_id`, `unidade_id`, `created_at`, `last_seen`) VALUES
(1, 'Danillo', 'Almeida Trajano Marques', 'damarques', '$2y$10$5jmulRfmv3ygH0MZq5LIduaRQlCR8f2joviIOUqi4vPdQfp3h5gPS', 1, NULL, '2026-04-17 17:48:15', NULL),
(4, 'Silas Emanoel', 'Ferreira do Nascimento', 'sferreira', '$2y$10$3Vn7GIS7Tge73YbJTS5gneMxeO0VntJUW.AVZLoy/EbvMh0MVR4yC', 1, 0, '2026-04-27 20:45:28', NULL),
(5, 'MARIA TAYNA', 'SILVA FEITOSA', 'mfeitosa', '$2y$10$GAh6zp.7Vul.cyLxghKwN.6C63gS6zSNedyLhXY4Y/dSoHPW6e4Je', 1, 0, '2026-04-28 19:33:01', NULL),
(6, 'Roberta', 'Gomes', 'rgomes', '$2y$10$UgV4S5nhAAM608Nk29NTrOV3aPXltuUQKN5AaJ2sz8jj6HcFz6wDS', 1, 0, '2026-04-30 18:28:26', NULL),
(7, 'Rafael', 'Pinheiro', 'rpinheiro', '$2y$10$m9TLtNalb7hAhqHD/uLlieHd2TK5If1XS6CYpVB8.zCnrA01RhTRu', 2, 0, '2026-05-04 12:13:09', NULL),
(8, 'Matheus', 'Alves', 'malves', '$2y$10$p/RVa5RoxgQJqpaV9brm0OwPPVVXV5T7vZH9w/OhyRuqb1xRLvboa', 5, 0, '2026-05-04 12:13:42', NULL),
(9, 'Diego', 'Almeida', 'dalmeida', '$2y$10$TEi4e12RDS1/QV3./usODehoTBHtHwXvwCYr5mq5.gDxQllLuJRXe', 7, 0, '2026-05-04 12:14:05', NULL),
(10, 'Flavio', 'ROGERIO MENDES FIGUEIROA', 'figueiroa', '$2y$10$QTYd6MvlyADGUkeqbYlMxOvu68nOTJytETmsR5J0yGa1/CHHmH3U6', 8, 0, '2026-05-04 12:15:42', NULL),
(11, 'Sttela', 'Fabricia Mendes de Moraes', 'sfabriciamendesdemoraes', '$2y$10$kgWKYpwL9X2ZCrBA14JCVOHN8SZeT.MQB3T2K1k7Xv2WZ0StNsFAW', 1, 0, '2026-05-05 19:55:08', NULL),
(12, 'Rhauana', 'Silva', 'rsilva', '$2y$10$XlAqeAly.NhFr0MYcVMGSuNPzSKYGLPP0e/c0.b9SLMx.jbjSEbwC', 1, 0, '2026-05-05 19:55:55', NULL),
(13, 'PALOMA', 'MACENA', 'pmacena', '$2y$10$f3SWZ0tjtepwk22hfoo18uSTpgwVHIstRX.xokJxQdNXHiSvxUT..', 6, 0, '2026-05-06 14:04:26', NULL),
(14, 'Roberta', 'Gomes', 'rgomes1', '$2y$10$vlQJZssu1/remUKgvUn..edVUkIg57y.wvxOomooWarmfBX.dVmty', 1, 0, '2026-05-08 13:54:47', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `last_activity` int DEFAULT NULL,
  `created_at` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `fingerprint`, `ip_address`, `user_agent`, `last_activity`, `created_at`, `is_active`) VALUES
(1, 1, 'din2oifhhcrva3vcmi7lr3nkjh', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778242551, NULL, 0),
(2, 4, '8n27b1c36ji9gtfpeof4blcre4', 'ef0fbf175ed531980e72c2658e6d13d5b4d9b69a9e5e43ae59a95b6b548c3488', NULL, NULL, 1778242226, NULL, 0),
(3, 1, 'dvt1aj3ug5d68dtchpg2s55bkv', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778243705, NULL, 0),
(4, 8, 'h0rn0uft281t5jb0us936s0k6q', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778243446, NULL, 1),
(5, 1, 'iu5k65b05tj36c9745gt09ffs0', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778245353, NULL, 0),
(6, 5, 't2c2jg6l1p7kr6crqjlbjr97ti', '2abb7f4b60244911ec3ecf2f58394a0e2cfd68621bcf575ac8f7f5ee7f67caf4', NULL, NULL, 1778246488, NULL, 1),
(7, 1, 'aej25pni24h1ancvrls81oedc7', '259645334e83f08b209bc8a0a17be63cc292da016783d9ecbaec0d15cd7afa7e', NULL, NULL, 1778248399, NULL, 0),
(8, 14, 'fm67a0epq84p8m6p1p3ifaccat', '259645334e83f08b209bc8a0a17be63cc292da016783d9ecbaec0d15cd7afa7e', NULL, NULL, 1778248567, NULL, 1),
(9, 1, 'k4ujhun3e70d4ft7rdnsl5omi6', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778250055, NULL, 0),
(10, 1, 'ot8344br3sjt357mpc3h75227s', '8118dcd3d8bfc90e6b53d1ac0e34b0afbef3c711cdf606b406c9501a6ff0c2a3', NULL, NULL, 1778254223, NULL, 0),
(11, 1, '10lvnfp4jp516r3vm3g1tn4014', 'b46bace13361aa6c813b5874cece767c0814a4c682ea774cd9ad434ac9066bfe', NULL, NULL, 1778260862, NULL, 0),
(12, 4, 'kuuuodce2p20mnb6il1aht8t1p', '210a27059c0233d6b9754dea7533be8e0fa9c3e27f4ddadbe4d33337a33e7f3a', NULL, NULL, 1778261092, NULL, 1),
(13, 1, 'gpuf8ccibtud1gok66cmi0r5j5', '794272037c59cd7c7f344982f7608d45168ed26e0d7a254e5f40e5e1671d7b41', NULL, NULL, 1778261161, NULL, 0),
(14, 1, 'i7hsibl00sf5i4qadlrsl3p31p', '5cededfd0e8939d15ac1cf605b1033cbef080700d49364ea41bd5f25aaea1af0', NULL, NULL, 1778266550, NULL, 0),
(15, 1, '1uplnb9c0ljeu3uleic8264ilr', 'ded65f721930035d1719a1a3b0c5114a2cc0376a4b34750488cf97cc90a169e2', NULL, NULL, 1778268634, NULL, 0),
(16, 1, 'g4tklc57l1t6p7bp4qut99bo5i', '04c5c5f0e5c3f688c25a9bcdbc53a1c86bdf7e80c22b37b3a4f72a5125b54a7c', NULL, NULL, 1778269418, NULL, 0),
(17, 1, 'i7dmck6egvh3c0s4lu9pimoca0', 'ffb5ecafa4718dcc2a3df6e0d736882cdf73a6086b10565c8cf62a966a54a023', NULL, NULL, 1778269742, NULL, 0),
(18, 1, '791a8cv52vp50mmpsqsaltf2p2', '6ced9dd31446b38da122cfafc57db42c9469347983b71e530d3af86bba2065b9', NULL, NULL, 1778269753, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `us_distrito`
--

DROP TABLE IF EXISTS `us_distrito`;
CREATE TABLE IF NOT EXISTS `us_distrito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cnes_us` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_unidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distrito_id` int DEFAULT NULL,
  `tipo` int NOT NULL,
  `ativo` int NOT NULL,
  `qtd_ine` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_us_distrito_distrito` (`distrito_id`)
) ENGINE=MyISAM AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `us_distrito`
--

INSERT INTO `us_distrito` (`id`, `cnes_us`, `nome_unidade`, `distrito_id`, `tipo`, `ativo`, `qtd_ine`) VALUES
(1, '0000639', 'Us 106 CS Prof Joaquim Cavalcante', 4, 0, 1, 0),
(2, '0000760', 'Us 161 CS Prof Romero Marques', 5, 2, 1, 4),
(3, '0000817', 'US 149 USF MAIS OLINTO OLIVEIRA', 4, 0, 1, 0),
(4, '0000825', 'Us 155 CS Prof Monteiro de Morais', 2, 0, 1, 0),
(5, '0000833', 'Us 137 CS Prof Djair Brindeiro', 6, 0, 1, 0),
(6, '0000841', 'Us 150 CS Professor Fernandes Figueiras', 5, 2, 1, 2),
(7, '0000868', 'US 142 USF MAIS BIDU KRAUSE', 5, 1, 1, 7),
(8, '0000876', 'Us 138 Usf Dr Luiz Wilsom', 2, 0, 1, 0),
(9, '0000957', 'US 174 USF MAIS SITIO GRANDE', 6, 0, 1, 0),
(10, '0000965', 'Us 177 Usf Chico Mendes', 5, 2, 1, 3),
(11, '0001058', 'US 121 USF MAIS PROFESSOR BRUNO MAIA', 7, 0, 1, 0),
(12, '0001082', 'US 104 CS SEBASTIAO IVO RABELO', 8, 0, 1, 0),
(13, '0001090', 'Us 126 CS Ver Romildo Gomes', 6, 0, 1, 0),
(14, '0001112', 'US 186 USF MAIS JARDIM UCHOA', 5, 1, 1, 4),
(15, '0001163', 'Us 109 CS Francisco Pignatari', 3, 0, 1, 0),
(16, '0001198', 'Us 113 CS Dr Aristarcho Dourado de Azevedo', 8, 0, 1, 0),
(17, '0001236', 'Us 152 CS Ina Rosa Borges', 7, 0, 1, 0),
(18, '0001244', 'Us 175 Usf Dr Diogenes Cavalcanti', 7, 0, 1, 0),
(19, '0001252', 'Us 218 Usf Coque', 1, 1, 1, 4),
(20, '0001317', 'Us 120 CS Mario Monteiro Melo', 7, 0, 1, 0),
(21, '0001368', 'Us 158 CS Pam Ceasa', 5, 2, 1, 1),
(22, '0001392', 'Us 148 CS Dom Miguel de Lima Valverde', 6, 0, 1, 0),
(23, '0001414', 'US 117 USF MAIS GASPAR REGUEIRA COSTA', 5, 1, 1, 4),
(24, '0001503', 'Us 179 Usf Mais Alto do Ceu', 2, 0, 1, 0),
(25, '0001511', 'Us 184 Usf Vila Uniao', 4, 0, 1, 0),
(26, '0001759', 'Us 103 CS Prof Mario Ramos', 3, 0, 1, 0),
(27, '0001813', 'Us 112 CS Dr Jose Dustan Carvalho Soares', 4, 0, 1, 0),
(28, '0002011', 'US 171 USF MAIS JOAQUIM COSTA CARVALHO', 3, 0, 1, 0),
(29, '0002062', 'Us 172 USF MAIS TRES CARNEIROS', 8, 0, 1, 0),
(30, '0002070', 'US 173 USF MAIS DANCING DAYS', 6, 0, 1, 0),
(31, '0002097', 'Us 183 Usf Sitio dos Macacos', 7, 0, 1, 0),
(32, '0002100', 'Us 187 Usf Ilha de Deus', 6, 0, 1, 0),
(33, '0002127', 'Us 216 Usf Apipucos', 3, 0, 1, 0),
(34, '0002135', 'Us 221 Usf Ilha de Joaneiro', 2, 0, 1, 0),
(35, '0002143', 'Us 123 CS Prof Cesar Montezuma', 1, 3, 1, 0),
(36, '0020567', 'Us 182 Upinha Usf Padre Jose Edwaldo Gomes', 3, 0, 1, 0),
(37, '0020648', 'Us 222 Usf Corrego do Curio', 2, 0, 1, 0),
(38, '0022187', 'US 232 USF ILHA SANTA TEREZINHA', 1, 1, 1, 2),
(39, '0022195', 'Us 240 Usf Coelhos I', 1, 2, 0, 0),
(40, '0022209', 'US 241 USF MAIS COELHOS', 1, 1, 1, 4),
(41, '0022217', 'Us 242 Usf Santo Amaro I Sitio do Ceu', 1, 2, 1, 1),
(42, '0022225', 'US 243 USF MAIS SANTO AMARO II', 1, 1, 1, 2),
(43, '0022233', 'Us 226 Usf Mais Chao de Estrelas', 2, 0, 1, 0),
(44, '0022268', 'Us 244 Usf Prof Antonio Francisco Areias', 2, 0, 1, 0),
(45, '0022276', 'US 231 USF MAIS CORREGO DA BICA', 7, 0, 1, 0),
(46, '0022292', 'Us 251 Usf da Guabiraba', 7, 0, 1, 0),
(47, '0022306', 'Us 225 Usf Skylab II', 4, 0, 1, 0),
(48, '0022314', 'Us 248 Usf Barreiras', 4, 0, 1, 0),
(49, '0022322', 'Us 233 Usf Vietna', 4, 0, 1, 0),
(50, '0022330', 'Us 234 Usf Roda de Fogo Cosirof', 4, 0, 1, 0),
(51, '0022349', 'Us 235 Usf Roda de Fogo Sinos', 4, 0, 1, 0),
(52, '0022357', 'Us 236 Usf Roda de Fogo Macae', 4, 0, 1, 0),
(53, '0022365', 'Us 237 Usf Sitio das Palmeiras', 4, 0, 1, 0),
(54, '0022373', 'US 224 USF MAIS CARANGUEJO', 4, 0, 1, 0),
(55, '0022381', 'Us 247 Usf Rosa Selvagem', 4, 0, 1, 0),
(56, '0022403', 'US 238 USF MAIS IRAQUE', 5, 1, 1, 3),
(57, '0022411', 'Us 239 Usf Coqueiral I e II', 5, 2, 1, 2),
(58, '0022438', 'US 245 USF MAIS PLANETA DOS MACACOS II', 5, 1, 1, 2),
(59, '0022454', 'Us 228 USF MAIS DES JOSE MANOEL DE FREITAS UR 4 UR 5', 8, 0, 1, 0),
(60, '0022462', 'Us 229 USF MAIS UR 10', 8, 0, 1, 0),
(61, '0022470', 'Us 230 USF MAIS LAGOA ENCANTADA', 8, 0, 1, 0),
(62, '0022489', 'Us 250 Usf Ur12 Ur5 3 Etapa', 8, 0, 1, 0),
(63, '0024503', 'Us 252 Usf Engenho do Meio', 4, 0, 1, 0),
(64, '0024511', 'Us 254 Usf Brasilit', 4, 0, 1, 0),
(65, '0024538', 'US 255 USF MAIS UPINHA 24H VILA ARRAES', 4, 0, 1, 0),
(66, '0026204', 'Us 256 Usf Passarinho Baixo', 7, 0, 1, 0),
(67, '0026212', 'US 259 – USF Sítio São Braz', 3, 0, 1, 0),
(68, '0026220', 'US 260 USF CORREGO DA FORTUNA', 3, 0, 1, 0),
(69, '0026301', 'Us 261 Usf Alto do Eucalipto', 7, 0, 1, 0),
(70, '0026328', 'Us 262 Usf Mais Jose Severiano da Silva', 2, 0, 1, 0),
(71, '0026336', 'Us 265 Usf Mangueira I', 5, 2, 1, 2),
(72, '0026344', 'Us 266 Usf Mangueira II', 5, 2, 1, 2),
(73, '0026352', 'Us 267 Usf Ur 2', 8, 0, 1, 0),
(74, '0026360', 'Us 268 Usf Cafesopolis', 6, 0, 1, 0),
(75, '0026379', 'Us 269 Usf Beira do Rio Comunidade Boa Viagem', 6, 0, 1, 0),
(76, '0026387', 'Us 270 USF MAIS MONTE VERDE', 8, 0, 1, 0),
(77, '0028045', 'Us 257 USF Gilberto Freire', 7, 0, 1, 0),
(78, '0028053', 'US 258 USF SITIO DOS PINTOS', 3, 0, 1, 0),
(79, '0028061', 'Us 272 Usf Santa Tereza', 7, 0, 1, 0),
(80, '0028088', 'Us 273 Usf Mais Bianor Teodosio', 2, 0, 1, 0),
(81, '0028096', 'Us 274 Usf TIA Regina', 2, 0, 1, 0),
(82, '0028649', 'Us 276 Usf Mais Alto do Pascoal', 2, 0, 1, 0),
(83, '0028665', 'US 278 USF NOSSA SRA DO PILAR BAIRRO DO RECIFE', 1, 2, 1, 1),
(84, '0028673', 'Us 279 Usf Passarinho Alto', 7, 0, 1, 0),
(85, '0028975', 'Us 280 Usf Sitio Cardoso', 4, 0, 1, 0),
(86, '0029041', 'Us 281 USF VILA DOS MILAGRES', 8, 0, 1, 0),
(87, '0029068', 'Us 282 USF MAIS VILA DAS AEROMOÇAS', 8, 0, 1, 0),
(88, '0029106', 'Us 283 Usf Vila Boa Vista', 7, 0, 1, 0),
(89, '0029114', 'US 284 USF MAIS VILA SAO MIGUEL MARROM GLACE', 5, 1, 1, 4),
(90, '0029122', 'Us 286 Usf Irma Terezinha', 2, 0, 1, 0),
(91, '0029130', 'US 285 USF MAIS SAO JOSE DO COQUE', 1, 1, 1, 4),
(92, '0266493', 'Us 108 CS Boa Vista', 1, 0, 0, 0),
(93, '0266507', 'Us 110 CS Joao de Barros', 1, 0, 0, 0),
(94, '2679779', 'Us 287 Usf Alto Jose do Pinho', 7, 0, 1, 0),
(95, '2679787', 'Us 288 Usf Morro da Conceicao', 7, 0, 1, 0),
(96, '2752824', 'Us 289 USF MAIS JOSUE DE CASTRO', 8, 0, 1, 0),
(97, '3006468', 'Us 291 Usf Alto dos Coqueiros Corrego da Jaqueira', 2, 0, 1, 0),
(98, '3006476', 'Us 290 Usf da Mangabeira', 7, 0, 1, 0),
(99, '3007995', 'Us 292 Usf Vila do Ipsep', 6, 0, 1, 0),
(100, '3037908', 'Us 294 Usf Vila Tamandare / Beirinha', 5, 2, 1, 4),
(101, '3131521', 'US 300 USF MAIS DR DR GERALDO BARRETO CAMPELO SAN MARTIN', 5, 1, 1, 4),
(102, '3131572', 'Us 301 Usf Bongi Boa Ideia', 5, 2, 1, 3),
(103, '3153460', 'U. S. 298 – USF Jordão Alto', 8, 0, 1, 0),
(104, '3153479', 'Us 299 USF MAIS JORDAO BAIXO', 8, 0, 1, 0),
(105, '3153487', 'Us 295 Usf Cosme e Damiao', 4, 0, 1, 0),
(106, '3153568', 'US 297 USF MAIS DO PINA', 6, 0, 1, 0),
(107, '3153584', 'Us 296 Usf Coqueiral Imbiribeira', 6, 0, 1, 0),
(108, '3301974', 'Us 302 Usf Byron Sarinho', 2, 0, 1, 0),
(109, '3302008', 'US 305 USF MAIS MACAXEIRA BURITY', 7, 0, 1, 0),
(110, '3302032', 'Us 309 Usf Mais Ponto de Parada', 2, 0, 1, 0),
(111, '3371328', 'US 323 USF MAIS MUSTARDINHA', 5, 1, 1, 4),
(112, '3371336', 'Us 324 Usf Alto Jose Bonifacio', 7, 0, 1, 0),
(113, '3380300', 'Us 317 Usf Alto da Bela Vista', 8, 0, 1, 0),
(114, '3445275', 'Us 327 Usf Clube dos Delegados', 2, 0, 1, 0),
(115, '3470253', 'Us 312 USF MAIS VILA DO SESI', 8, 0, 1, 0),
(116, '3470261', 'Us 326 Usf Jader de Andrade Comunidade Entra Apulso', 6, 0, 1, 0),
(117, '3562581', 'US 316 USF MAIS BERNARD VAN LEER', 6, 0, 1, 0),
(118, '3562638', 'Us 315 USF MAIS UR 3', 8, 0, 1, 0),
(119, '3567826', 'Us 328 Usf Alto do Maracana', 2, 0, 1, 0),
(120, '3569322', 'US 313 USF TRES CARNEIROS DE BAIXO ZUMBI DO PACHECO', 8, 0, 1, 0),
(121, '3569349', 'Us 311 Usf Agua Viva', 8, 0, 1, 0),
(122, '3639827', 'Us 154 USF MAIS RIO PAJEU', 8, 0, 1, 0),
(123, '3703223', 'Us 331 Usf Prof Amaury de Medeiros', 4, 0, 1, 0),
(124, '3862836', 'US 334 USF MAIS CABANGA', 1, 1, 1, 2),
(125, '4426150', 'US 307 USF MAIS DR GUILHERME J ROBALINHO OLIVEIRA CAVALCANTI', 6, 0, 1, 0),
(126, '5139155', 'US 336 USF MAIS UNIAO DAS VILAS', 3, 0, 1, 0),
(127, '5320380', 'Us 337 Usf Sitio Wanderley', 4, 0, 1, 0),
(128, '5342074', 'US 338 USF MAIS JARDIM SAO PAULO', 5, 1, 1, 6),
(129, '5356881', 'Us 339 Usf Alto do Capitao', 2, 0, 1, 0),
(130, '5392039', 'Us 341 Usf Prof Fernando Figueira', 8, 0, 1, 0),
(131, '5392136', 'US 342 USF MAIS DJALMA HOLANDA CAVALCANTE', 6, 0, 1, 0),
(132, '5601037', 'US 344 USF MAIS JIQUIA', 5, 1, 1, 3),
(133, '5601053', 'US 345 USF MAIS PLANETA DOS MACACOS I', 5, 1, 1, 2),
(134, '5653304', 'Us 346 Usf Alto da Jaqueira', 8, 0, 1, 0),
(135, '5656893', 'Us 347 Usf Parque dos Milagres', 8, 0, 1, 0),
(136, '6008984', 'Us 349 Usf Casarao do Cordeiro', 4, 0, 1, 0),
(137, '6334067', 'Us 350 Usf Corrego do Eucalipto', 7, 0, 1, 0),
(138, '6362494', 'Us 351 Usf Paz e Amor', 8, 0, 1, 0),
(139, '6362508', 'US 352 USF MAIS PROFESSOR DR HELIO MENDONCA', 7, 0, 1, 0),
(140, '6691285', 'Us 373 Usf Cidade Operaria', 8, 0, 1, 0),
(141, '6916325', 'US 378 USF JARDIM TERESOPOLIS', 4, 0, 1, 0),
(142, '7404379', 'Us 393 Usf Upinha Dia Bongi Novo Prado', 5, 2, 1, 1),
(143, '7415788', 'US 394 USF MAIS DR MOACYR ANDRE GOMES', 7, 0, 1, 0),
(144, '7524501', 'Us 395 Usf Mais Dra Fernanda Wanderley', 2, 0, 1, 0),
(145, '7563736', 'US 397 USF MAIS ACS MARIA RITA DA SILVA', 7, 0, 1, 0),
(146, '7648480', 'Us 399 Usf Upinha Dia Novo Jiquia', 5, 2, 1, 1),
(147, '7845367', 'US 400 USF MAIS DOM HELDER CAMARA', 7, 0, 1, 0),
(148, '7946651', 'Us 401 Usf Mais Governador Eduardo Campos', 2, 0, 1, 0),
(149, '7992955', 'Us 403 Usf Upinha Dia Chie', 2, 0, 1, 0),
(150, '9069569', 'US 442 USF MAIS SANTA LUZIA EMOCY KRAUSE', 4, 0, 1, 0),
(151, '9384324', 'US 404 USF MAIS SANTO AMARO III', 1, 1, 1, 2),
(152, '9890327', 'Us 314 Usf Rio da Prata', 8, 0, 1, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
