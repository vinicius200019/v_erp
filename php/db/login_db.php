<?php
include_once 'conexao.php';

function verificarLogin($email, $senha) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, email, senha FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Em um sistema real, use password_verify. Aqui estamos simplificando.
    if ($user && $senha === $user['senha']) {
        return $user;
    }
    return false;
}
?>