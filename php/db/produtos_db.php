<?php
include_once 'conexao.php';

function listarProdutos() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE ativo = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function totalPatrimonio() {
    global $conn;
    $stmt = $conn->prepare("SELECT SUM(estoque * preco_venda) as total FROM produtos");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}
?>