<?php
include_once 'conexao.php';

function listarEquipe() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, email, perfil FROM usuarios");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>