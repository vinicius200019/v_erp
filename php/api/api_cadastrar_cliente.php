<?php
header('Content-Type: application/json');
include_once '../db/login_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['email'], $dados['cpf_cnpj'], $dados['senha'])) {
    $resultado = cadastrarCliente($dados['email'], $dados['cpf_cnpj'], $dados['senha']);
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}