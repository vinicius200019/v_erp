<?php
include_once 'conexao.php';

function registrarVenda($id_produto, $id_cliente, $quantidade, $valor_total) {
    global $conn;

    // Verifica se o produto existe e está ativo
    $check = $conn->prepare("SELECT id, estoque, ativo FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id_produto, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        return ['success' => false, 'message' => 'Produto não encontrado'];
    }
    if ($produto['ativo'] != 1) {
        return ['success' => false, 'message' => 'Produto não está disponível'];
    }
    if ($produto['estoque'] < $quantidade) {
        return ['success' => false, 'message' => 'Estoque insuficiente'];
    }

    // Transação: ou registra a venda E baixa estoque, ou nada acontece
    try {
        $conn->beginTransaction();

        // 1. Insere a venda
        $stmt = $conn->prepare("INSERT INTO vendas (id_produto, id_cliente, quantidade, valor_total) VALUES (:id_produto, :id_cliente, :quantidade, :valor_total)");
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->execute();

        // 2. SUBTRAI do estoque
        $upd = $conn->prepare("UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id");
        $upd->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
        $upd->bindParam(':id', $id_produto, PDO::PARAM_INT);
        $upd->execute();

        $conn->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Erro ao registrar venda: ' . $e->getMessage()];
    }
}

function listarVendasCliente($id_cliente) {
    global $conn;
    $sql = "SELECT v.*, p.nome as produto_nome 
            FROM vendas v 
            JOIN produtos p ON v.id_produto = p.id 
            WHERE v.id_cliente = :id 
            ORDER BY v.data_venda DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarTodasVendas() {
    global $conn;
    // JOIN duplo: pega o nome do produto E o email do cliente
    $sql = "SELECT v.*, 
                   p.nome as produto_nome, 
                   u.email as cliente_email
            FROM vendas v 
            JOIN produtos p ON v.id_produto = p.id 
            JOIN usuarios u ON v.id_cliente = u.id 
            ORDER BY v.data_venda DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>