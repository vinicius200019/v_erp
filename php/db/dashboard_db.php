<?php
include_once 'conexao.php';

function getStats() {
    global $conn;
    
    $stats = [];
    
    // Total de produtos distintos
    $stats['produtos_count'] = $conn->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    
    // Soma total de itens físicos no estoque
    $stats['estoque_total'] = $conn->query("SELECT SUM(estoque) FROM produtos")->fetchColumn() ?? 0;
    
    // Valor total do inventário
    $stats['valor_patrimonio'] = $conn->query("SELECT SUM(estoque * preco_venda) FROM produtos")->fetchColumn() ?? 0;
    
    // Total de usuários
    $stats['usuarios_count'] = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

    return $stats;
}
?>