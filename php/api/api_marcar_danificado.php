<?php
header('Content-Type: application/json');
include_once '../db/produtos_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id'], $dados['quantidade'])) {
    $resultado = marcarDanificado(intval($dados['id']), intval($dados['quantidade']));
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}