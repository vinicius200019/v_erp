<?php
header('Content-Type: application/json');
include_once '../db/vendas_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id_produto'], $dados['id_cliente'], $dados['quantidade'], $dados['valor_total'])) {
    $resultado = registrarVenda(
        intval($dados['id_produto']),
        intval($dados['id_cliente']),
        intval($dados['quantidade']),
        floatval($dados['valor_total'])
    );
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}