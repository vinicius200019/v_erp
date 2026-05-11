<?php
include_once 'conexao.php';

function listarCompras() {
    global $conn;
    // JOIN para pegar o nome do produto, ordenado da mais recente pra mais antiga
    $sql = "SELECT c.*, p.nome as produto_nome 
            FROM compras c 
            JOIN produtos p ON c.id_produto = p.id 
            ORDER BY c.data_compra DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function registrarCompra($id_produto, $quantidade, $valor_total) {
    global $conn;

    // Verifica se o produto existe e está ativo
    $check = $conn->prepare("SELECT id, ativo FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id_produto, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        return ['success' => false, 'message' => 'Produto não encontrado'];
    }
    if ($produto['ativo'] != 1) {
        return ['success' => false, 'message' => 'Produto está inativo'];
    }

    // Usamos transação: se algo der errado, desfaz tudo
    // Isso garante que ou a compra É registrada E o estoque sobe, ou nada acontece
    try {
        $conn->beginTransaction();

        // 1. Insere a compra
        $stmt = $conn->prepare("INSERT INTO compras (id_produto, quantidade, valor_total) VALUES (:id_produto, :quantidade, :valor_total)");
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->execute();

        // 2. Atualiza o estoque do produto (soma a quantidade comprada)
        $upd = $conn->prepare("UPDATE produtos SET estoque = estoque + :qtd WHERE id = :id");
        $upd->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
        $upd->bindParam(':id', $id_produto, PDO::PARAM_INT);
        $upd->execute();

        $conn->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Erro ao registrar compra: ' . $e->getMessage()];
    }
}
?>