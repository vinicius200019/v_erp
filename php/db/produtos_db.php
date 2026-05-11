<?php
include_once 'conexao.php';

function listarProdutos($ativo = 1) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE ativo = :ativo ORDER BY nome ASC");
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function totalPatrimonio() {
    global $conn;
    $stmt = $conn->prepare("SELECT SUM(estoque * preco_venda) as total FROM produtos WHERE ativo = 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

function criarProduto($sku, $nome, $estoque, $preco_venda) {
    global $conn;

    // Verifica se SKU já existe
    $check = $conn->prepare("SELECT id FROM produtos WHERE sku = :sku");
    $check->bindParam(':sku', $sku);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'SKU já cadastrado'];
    }

    // O ID é gerado automaticamente pelo MySQL (AUTO_INCREMENT)
    $stmt = $conn->prepare("INSERT INTO produtos (sku, nome, estoque, preco_venda, ativo) VALUES (:sku, :nome, :estoque, :preco_venda, 1)");
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindParam(':preco_venda', $preco_venda);

    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->lastInsertId()];
    }
    return ['success' => false, 'message' => 'Erro ao cadastrar produto'];
}

function editarProduto($id, $sku, $nome, $estoque, $preco_venda) {
    global $conn;

    // Verifica se SKU já existe em outro produto
    $check = $conn->prepare("SELECT id FROM produtos WHERE sku = :sku AND id != :id");
    $check->bindParam(':sku', $sku);
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'SKU já está em uso por outro produto'];
    }

    $stmt = $conn->prepare("UPDATE produtos SET sku = :sku, nome = :nome, estoque = :estoque, preco_venda = :preco_venda WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindParam(':preco_venda', $preco_venda);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao atualizar'];
}

function alterarStatusProduto($id, $ativo) {
    global $conn;
    $stmt = $conn->prepare("UPDATE produtos SET ativo = :ativo WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao alterar status'];
}
?>