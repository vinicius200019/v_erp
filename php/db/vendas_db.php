<?php
include_once 'conexao.php';

/**
 * Função antiga - mantida pra compatibilidade
 * (agora a venda real é registrada via finalizarCarrinho)
 */
function registrarVenda($id_produto, $id_cliente, $quantidade, $valor_total, $forma_pagamento = 'pix') {
    global $conn;

    $formas_validas = ['dinheiro', 'pix', 'cartao', 'transferencia'];
    if (!in_array($forma_pagamento, $formas_validas)) {
        return ['success' => false, 'message' => 'Forma de pagamento inválida'];
    }

    $check = $conn->prepare("SELECT id, estoque, ativo FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id_produto, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) return ['success' => false, 'message' => 'Produto não encontrado'];
    if ($produto['ativo'] != 1) return ['success' => false, 'message' => 'Produto não está disponível'];
    if ($produto['estoque'] < $quantidade) return ['success' => false, 'message' => 'Estoque insuficiente'];

    try {
        $conn->beginTransaction();
        $preco_unit = $valor_total / $quantidade;

        $stmt = $conn->prepare("INSERT INTO vendas (id_produto, id_cliente, quantidade, valor_total, forma_pagamento) VALUES (:id_produto, :id_cliente, :quantidade, :valor_total, :forma_pagamento)");
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':forma_pagamento', $forma_pagamento);
        $stmt->execute();

        $id_venda = $conn->lastInsertId();

        $stmtItem = $conn->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario, subtotal) VALUES (:id_venda, :id_produto, :quantidade, :preco, :subtotal)");
        $stmtItem->bindParam(':id_venda', $id_venda, PDO::PARAM_INT);
        $stmtItem->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmtItem->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmtItem->bindParam(':preco', $preco_unit);
        $stmtItem->bindParam(':subtotal', $valor_total);
        $stmtItem->execute();

        $upd = $conn->prepare("UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id");
        $upd->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
        $upd->bindParam(':id', $id_produto, PDO::PARAM_INT);
        $upd->execute();

        $conn->commit();
        return ['success' => true, 'id_venda' => $id_venda];
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
    }
}

/**
 * Lista vendas de UM cliente específico, COM os itens de cada venda.
 */
function listarVendasCliente($id_cliente) {
    global $conn;

    // Pega cabeçalhos das vendas
    $sql = "SELECT v.id, v.valor_total, v.forma_pagamento, v.data_venda
            FROM vendas v
            WHERE v.id_cliente = :id
            ORDER BY v.data_venda DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pra cada venda, busca os itens
    foreach ($vendas as &$v) {
        $v['itens'] = getItensVenda($v['id']);
    }

    return $vendas;
}

/**
 * Lista TODAS as vendas (admin), com itens.
 */
function listarTodasVendas() {
    global $conn;

    $sql = "SELECT v.id, v.valor_total, v.forma_pagamento, v.data_venda,
                   u.email as cliente_email
            FROM vendas v
            JOIN usuarios u ON v.id_cliente = u.id
            ORDER BY v.data_venda DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vendas as &$v) {
        $v['itens'] = getItensVenda($v['id']);
    }

    return $vendas;
}

/**
 * Busca os itens de uma venda específica.
 */
function getItensVenda($id_venda) {
    global $conn;
    $sql = "SELECT vi.id, vi.quantidade, vi.preco_unitario, vi.subtotal,
                   p.id as id_produto, p.nome as produto_nome, p.sku
            FROM vendas_itens vi
            JOIN produtos p ON vi.id_produto = p.id
            WHERE vi.id_venda = :id
            ORDER BY vi.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_venda, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca uma venda específica com cabeçalho completo + itens.
 * Usado pela nota fiscal.
 */
function getVendaCompleta($id_venda) {
    global $conn;
    $sql = "SELECT v.id, v.valor_total, v.forma_pagamento, v.data_venda,
                   u.email as cliente_email, u.cpf_cnpj as cliente_documento
            FROM vendas v
            JOIN usuarios u ON v.id_cliente = u.id
            WHERE v.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_venda, PDO::PARAM_INT);
    $stmt->execute();
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venda) return null;

    $venda['itens'] = getItensVenda($id_venda);
    return $venda;
}
?>