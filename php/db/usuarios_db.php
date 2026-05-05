<?php
include_once 'conexao.php';

function listarEquipe() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, email, perfil FROM usuarios");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function criarUsuario($email, $senha, $perfil) {
    global $conn;

    // Verifica se o email já está cadastrado
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $check->bindParam(':email', $email);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'E-mail já cadastrado'];
    }

    // Insere o novo usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (email, senha, perfil) VALUES (:email, :senha, :perfil)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':perfil', $perfil);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao salvar no banco'];
}
 
function excluirUsuario($id) {
    global $conn;

    // Verifica se o usuário existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE id = :id");
    $check->bindParam(':id', $id);
    $check->execute();
    if (!$check->fetch()) {
        return ['success' => false, 'message' => 'Usuário não encontrado'];
    }

    // Não permite excluir se for o último admin do sistema
    $countAdmin = $conn->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'")->fetchColumn();
    $userCheck = $conn->prepare("SELECT perfil FROM usuarios WHERE id = :id");
    $userCheck->bindParam(':id', $id);
    $userCheck->execute();
    $usuario = $userCheck->fetch(PDO::FETCH_ASSOC);

    if ($usuario['perfil'] === 'admin' && $countAdmin <= 1) {
        return ['success' => false, 'message' => 'Não é possível excluir o último admin'];
    }

    // Executa a exclusão
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao excluir do banco'];
}
?>