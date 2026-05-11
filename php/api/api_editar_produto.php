<?php
header('Content-Type: application/json');
include_once '../db/produtos_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id'], $dados['sku'], $dados['nome'], $dados['estoque'], $dados['preco_venda'])) {
    $resultado = editarProduto(
        intval($dados['id']),
        $dados['sku'],
        $dados['nome'],
        intval($dados['estoque']),
        floatval($dados['preco_venda'])
    );
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}