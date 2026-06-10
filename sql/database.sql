-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09-Jun-2026 às 21:19
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `v_erp`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` varchar(20) DEFAULT 'pix',
  `data_compra` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `compras`
--

INSERT INTO `compras` (`id`, `id_produto`, `quantidade`, `valor_total`, `forma_pagamento`, `data_compra`) VALUES
(1, 1, 30, 2400.00, 'pix', '2026-05-06 15:19:58'),
(2, 2, 25, 7250.00, 'pix', '2026-05-06 15:27:33'),
(3, 1, 5, 652.50, 'pix', '2026-06-02 16:02:28'),
(4, 1, 5, 652.50, 'dinheiro', '2026-06-02 17:04:12');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `preco_custo` decimal(10,2) DEFAULT 0.00,
  `estoque` int(11) DEFAULT 0,
  `estoque_danificado` int(11) DEFAULT 0,
  `preco_venda` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `sku`, `preco_custo`, `estoque`, `estoque_danificado`, `preco_venda`, `ativo`) VALUES
(1, 'Camisa Corinthians 2024', 'SCCP-001', 130.50, 22, 0, 199.90, 1),
(2, 'Camisa Palmeiras 2024', 'SEP - 002', 130.60, 25, 0, 199.00, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf_cnpj` varchar(18) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` varchar(20) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `cpf_cnpj`, `senha`, `perfil`) VALUES
(1, 'vini@erp.com', NULL, '123456', 'admin'),
(2, 'vinicius20050515@gmail.com', NULL, '1234567', 'admin'),
(4, 'usuario@emp.com', NULL, '123456', 'usuario'),
(5, 'seu@email.com', '02250909610', '1234', 'usuario'),
(6, 'cliente@teste.com', '12345678900', '123', 'usuario');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(20) DEFAULT 'pix',
  `data_venda` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vendas`
--

INSERT INTO `vendas` (`id`, `id_produto`, `id_cliente`, `quantidade`, `valor_total`, `forma_pagamento`, `data_venda`) VALUES
(1, 1, 6, 5, 999.50, 'pix', '2026-05-06 19:51:03'),
(2, 1, 6, 4, 799.60, 'cartao', '2026-06-02 10:19:22'),
(3, 2, 6, 4, 796.00, 'dinheiro', '2026-06-02 10:20:21'),
(4, 1, 6, 1, 199.90, 'pix', '2026-06-02 12:19:40'),
(5, 1, 6, 3, 599.70, 'pix', '2026-06-02 12:20:25'),
(6, 1, 6, 1, 199.90, 'pix', '2026-06-02 12:21:47'),
(7, 1, 6, 4, 1595.60, 'pix', '2026-06-02 14:29:25'),
(8, 1, 6, 4, 1595.60, 'pix', '2026-06-02 14:49:56'),
(9, 1, 6, 3, 1196.70, 'transferencia', '2026-06-02 15:41:04'),
(10, 1, 4, 1, 130.23, 'dinheiro', '2026-06-06 15:25:37'),
(11, 1, 4, 2, 90.00, 'dinheiro', '2026-06-09 16:16:53');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas_itens`
--

CREATE TABLE `vendas_itens` (
  `id` int(11) NOT NULL,
  `id_venda` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vendas_itens`
--

INSERT INTO `vendas_itens` (`id`, `id_venda`, `id_produto`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 1, 5, 199.90, 999.50),
(2, 2, 1, 4, 199.90, 799.60),
(3, 3, 2, 4, 199.00, 796.00),
(4, 4, 1, 1, 199.90, 199.90),
(5, 5, 1, 3, 199.90, 599.70),
(6, 6, 1, 1, 199.90, 199.90),
(8, 7, 1, 4, 199.90, 799.60),
(9, 7, 2, 4, 199.00, 796.00),
(10, 8, 1, 4, 199.90, 799.60),
(11, 8, 2, 4, 199.00, 796.00),
(12, 9, 1, 3, 199.90, 599.70),
(13, 9, 2, 3, 199.00, 597.00),
(14, 10, 1, 1, 130.23, 130.23),
(15, 11, 1, 2, 45.00, 90.00);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cpf` (`cpf_cnpj`);

--
-- Índices para tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices para tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venda` (`id_venda`),
  ADD KEY `id_produto` (`id_produto`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`),
  ADD CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `vendas_itens`
--
ALTER TABLE `vendas_itens`
  ADD CONSTRAINT `vendas_itens_ibfk_1` FOREIGN KEY (`id_venda`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendas_itens_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
