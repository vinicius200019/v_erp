<?php
header('Content-Type: application/json');
include_once '../db/usuarios_db.php';

$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['email']) && isset($dados['senha']) && isset($dados['perfil'])) {
    $resultado = criarUsuario($dados['email'], $dados['senha'], $dados['perfil']);
    echo json_encode($resultado);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}