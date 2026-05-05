<?php
include_once 'conexao.php';

function listarCompras() {
    global $conn;
    // Faz um JOIN para pegar o nome do produto ao invés de apenas o ID
    $sql = "SELECT c.*, p.nome as produto_nome 
            FROM compras c 
            JOIN produtos p ON c.id_produto = p.id 
            ORDER BY c.data_compra DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>