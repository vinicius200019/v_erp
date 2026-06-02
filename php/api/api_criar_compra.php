<?php
header('Content-Type: application/json');
include_once '../db/compras_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id_produto'], $dados['quantidade'], $dados['valor_total'])) {
    $forma = isset($dados['forma_pagamento']) ? $dados['forma_pagamento'] : 'pix';
    $resultado = registrarCompra(
        intval($dados['id_produto']),
        intval($dados['quantidade']),
        floatval($dados['valor_total']),
        $forma
    );
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}