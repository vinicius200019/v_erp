<?php
header('Content-Type: application/json');
include_once '../db/usuarios_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['id'])) {
    $resultado = excluirUsuario(intval($dados['id']));
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'ID não informado']);
}