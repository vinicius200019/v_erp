<?php
header('Content-Type: application/json');
include_once '../db/login_db.php';

// Recebe os dados enviados pelo fetch (JSON)
$dados = json_decode(file_get_contents("php://input"), true);

if (isset($dados['email']) && isset($dados['senha'])) {
    $user = verificarLogin($dados['email'], $dados['senha']);

    if ($user) {
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user['id'],
                "email" => $user['email']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "E-mail ou senha incorretos."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
}