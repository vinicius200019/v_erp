<?php
include_once 'conexao.php';

function verificarLogin($email, $senha) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, email, senha, perfil FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $senha === $user['senha']) {
        return $user;
    }
    return false;
}

function cadastrarCliente($email, $cpf_cnpj, $senha) {
    global $conn;

    // Verifica email duplicado
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $check->bindParam(':email', $email);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'E-mail já cadastrado'];
    }

    // Verifica CPF/CNPJ duplicado
    $check2 = $conn->prepare("SELECT id FROM usuarios WHERE cpf_cnpj = :cpf");
    $check2->bindParam(':cpf', $cpf_cnpj);
    $check2->execute();
    if ($check2->fetch()) {
        return ['success' => false, 'message' => 'CPF/CNPJ já cadastrado'];
    }

    // Cliente é cadastrado com perfil 'usuario'
    $stmt = $conn->prepare("INSERT INTO usuarios (email, cpf_cnpj, senha, perfil) VALUES (:email, :cpf, :senha, 'usuario')");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':cpf', $cpf_cnpj);
    $stmt->bindParam(':senha', $senha);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao cadastrar'];
}
?>