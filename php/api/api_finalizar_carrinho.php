<?php
header('Content-Type: application/json');
include_once '../db/carrinho_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id_cliente'], $dados['forma_pagamento'], $dados['itens'])) {
    $resultado = finalizarCarrinho(
        intval($dados['id_cliente']),
        $dados['forma_pagamento'],
        $dados['itens']
    );
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}