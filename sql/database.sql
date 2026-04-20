-- Criar Tabela de Usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) DEFAULT 'admin'
);

-- Criar Tabela de Produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sku VARCHAR(50) NOT NULL,
    estoque INT DEFAULT 0,
    preco_venda DECIMAL(10,2) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- Criar Tabela de Compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT,
    quantidade INT,
    valor_total DECIMAL(10,2),
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produto) REFERENCES produtos(id)
);

-- INSERIR DADOS DE TESTE
INSERT INTO usuarios (email, senha, perfil) VALUES ('vini@erp.com', '123456', 'admin');

INSERT INTO produtos (nome, sku, estoque, preco_venda, ativo) 
VALUES ('Camisa Corinthians 2024', 'SCCP-001', 10, 199.90, 1);