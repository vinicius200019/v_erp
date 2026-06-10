<?php
include_once 'conexao.php';

function finalizarCarrinho($id_cliente, $forma_pagamento, $itens) {
    global $conn;

    // Validações básicas
    if (empty($itens)) {
        return ['success' => false, 'message' => 'Carrinho vazio'];
    }

    $formas_validas = ['dinheiro', 'pix', 'cartao', 'transferencia'];
    if (!in_array($forma_pagamento, $formas_validas)) {
        return ['success' => false, 'message' => 'Forma de pagamento inválida'];
    }

    // Verifica estoque de todos os produtos ANTES de começar a transação
    foreach ($itens as $item) {
        $stmt = $conn->prepare("SELECT id, nome, estoque, ativo FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $item['id_produto'], PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            return ['success' => false, 'message' => "Produto ID {$item['id_produto']} não encontrado"];
        }
        if ($produto['ativo'] != 1) {
            return ['success' => false, 'message' => "Produto '{$produto['nome']}' não está disponível"];
        }
        if ($produto['estoque'] < $item['quantidade']) {
            return ['success' => false, 'message' => "Estoque insuficiente para '{$produto['nome']}' (disponível: {$produto['estoque']})"];
        }
    }

    // Calcula valor total da venda
    $valor_total = 0;
    foreach ($itens as $item) {
        $valor_total += $item['quantidade'] * $item['preco_unitario'];
    }

    // Transação: ou tudo acontece, ou nada acontece
    try {
        $conn->beginTransaction();

        // 1. Cria o cabeçalho da venda
        // Pega o primeiro item pra preencher id_produto/quantidade (compatibilidade temporária)
        $primeiro = $itens[0];
        $sqlVenda = "INSERT INTO vendas (id_produto, id_cliente, quantidade, valor_total, forma_pagamento) 
                     VALUES (:id_produto, :id_cliente, :quantidade, :valor_total, :forma_pagamento)";
        $stmt = $conn->prepare($sqlVenda);
        $stmt->bindParam(':id_produto', $primeiro['id_produto'], PDO::PARAM_INT);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $primeiro['quantidade'], PDO::PARAM_INT);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':forma_pagamento', $forma_pagamento);
        $stmt->execute();

        $id_venda = $conn->lastInsertId();

        // 2. Insere cada item em vendas_itens E baixa o estoque
        $sqlItem = "INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario, subtotal) 
                    VALUES (:id_venda, :id_produto, :quantidade, :preco_unitario, :subtotal)";
        $sqlBaixa = "UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id";

        foreach ($itens as $item) {
            $subtotal = $item['quantidade'] * $item['preco_unitario'];

            $stmtItem = $conn->prepare($sqlItem);
            $stmtItem->bindParam(':id_venda', $id_venda, PDO::PARAM_INT);
            $stmtItem->bindParam(':id_produto', $item['id_produto'], PDO::PARAM_INT);
            $stmtItem->bindParam(':quantidade', $item['quantidade'], PDO::PARAM_INT);
            $stmtItem->bindParam(':preco_unitario', $item['preco_unitario']);
            $stmtItem->bindParam(':subtotal', $subtotal);
            $stmtItem->execute();

            $stmtBaixa = $conn->prepare($sqlBaixa);
            $stmtBaixa->bindParam(':qtd', $item['quantidade'], PDO::PARAM_INT);
            $stmtBaixa->bindParam(':id', $item['id_produto'], PDO::PARAM_INT);
            $stmtBaixa->execute();
        }

        $conn->commit();
        return ['success' => true, 'id_venda' => $id_venda];

    } catch (PDOException $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Erro ao processar venda: ' . $e->getMessage()];
    }
}
?>