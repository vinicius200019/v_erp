<?php
include_once 'conexao.php';

function listarProdutos($ativo = 1) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE ativo = :ativo ORDER BY nome ASC");
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function totalPatrimonio() {
    global $conn;
    $stmt = $conn->prepare("SELECT SUM(estoque * preco_venda) as total FROM produtos WHERE ativo = 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

function criarProduto($sku, $nome, $estoque, $preco_custo, $preco_venda) {
    global $conn;

    $check = $conn->prepare("SELECT id FROM produtos WHERE sku = :sku");
    $check->bindParam(':sku', $sku);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'SKU já cadastrado'];
    }

    $stmt = $conn->prepare("INSERT INTO produtos (sku, nome, estoque, preco_custo, preco_venda, ativo) VALUES (:sku, :nome, :estoque, :preco_custo, :preco_venda, 1)");
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);

    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->lastInsertId()];
    }
    return ['success' => false, 'message' => 'Erro ao cadastrar produto'];
}

function editarProduto($id, $sku, $nome, $estoque, $preco_custo, $preco_venda) {
    global $conn;

    $check = $conn->prepare("SELECT id FROM produtos WHERE sku = :sku AND id != :id");
    $check->bindParam(':sku', $sku);
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    if ($check->fetch()) {
        return ['success' => false, 'message' => 'SKU já está em uso por outro produto'];
    }

    $stmt = $conn->prepare("UPDATE produtos SET sku = :sku, nome = :nome, estoque = :estoque, preco_custo = :preco_custo, preco_venda = :preco_venda WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':estoque', $estoque, PDO::PARAM_INT);
    $stmt->bindParam(':preco_custo', $preco_custo);
    $stmt->bindParam(':preco_venda', $preco_venda);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao atualizar'];
}

function alterarStatusProduto($id, $ativo) {
    global $conn;
    $stmt = $conn->prepare("UPDATE produtos SET ativo = :ativo WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao alterar status'];
}

/**
 * Lista produtos que têm pelo menos 1 unidade danificada.
 */
function listarProdutosDanificados() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE estoque_danificado > 0 ORDER BY nome ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Marca uma quantidade de unidades como danificadas.
 * As unidades SAEM do estoque normal e entram no estoque_danificado.
 */
function marcarDanificado($id, $quantidade) {
    global $conn;

    $quantidade = intval($quantidade);
    if ($quantidade < 1) {
        return ['success' => false, 'message' => 'Informe uma quantidade válida'];
    }

    $check = $conn->prepare("SELECT estoque FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) return ['success' => false, 'message' => 'Produto não encontrado'];
    if ($produto['estoque'] < $quantidade) {
        return ['success' => false, 'message' => "Estoque insuficiente (disponível: {$produto['estoque']})"];
    }

    $stmt = $conn->prepare("UPDATE produtos SET estoque = estoque - :qtd, estoque_danificado = estoque_danificado + :qtd2 WHERE id = :id");
    $stmt->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':qtd2', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao marcar como danificado'];
}

/**
 * Devolve unidades danificadas de volta ao estoque normal.
 */
function recuperarDanificado($id, $quantidade) {
    global $conn;

    $quantidade = intval($quantidade);
    if ($quantidade < 1) {
        return ['success' => false, 'message' => 'Informe uma quantidade válida'];
    }

    $check = $conn->prepare("SELECT estoque_danificado FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) return ['success' => false, 'message' => 'Produto não encontrado'];
    if ($produto['estoque_danificado'] < $quantidade) {
        return ['success' => false, 'message' => "Quantidade danificada insuficiente (disponível: {$produto['estoque_danificado']})"];
    }

    $stmt = $conn->prepare("UPDATE produtos SET estoque = estoque + :qtd, estoque_danificado = estoque_danificado - :qtd2 WHERE id = :id");
    $stmt->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':qtd2', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Erro ao recuperar produto'];
}

/**
 * Vende uma quantidade de unidades danificadas por um valor TOTAL.
 * - Registra a venda no faturamento (vendas + vendas_itens)
 * - Dá baixa no estoque_danificado
 * - Usa um "cliente de balcão" para satisfazer a FK id_cliente.
 */
function venderProdutoDanificado($id, $quantidade, $valor_total, $forma_pagamento = 'dinheiro') {
    global $conn;

    $formas_validas = ['dinheiro', 'pix', 'cartao', 'transferencia'];
    if (!in_array($forma_pagamento, $formas_validas)) {
        return ['success' => false, 'message' => 'Forma de pagamento inválida'];
    }

    $quantidade = intval($quantidade);
    $valor_total = floatval($valor_total);
    if ($quantidade < 1) return ['success' => false, 'message' => 'Informe uma quantidade válida'];
    if ($valor_total <= 0) return ['success' => false, 'message' => 'Informe um valor de venda válido'];

    $check = $conn->prepare("SELECT id, estoque_danificado FROM produtos WHERE id = :id");
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    $produto = $check->fetch(PDO::FETCH_ASSOC);

    if (!$produto) return ['success' => false, 'message' => 'Produto não encontrado'];
    if ($produto['estoque_danificado'] < $quantidade) {
        return ['success' => false, 'message' => "Quantidade danificada insuficiente (disponível: {$produto['estoque_danificado']})"];
    }

    // Cliente de balcão (primeiro usuário com perfil 'usuario'); satisfaz a FK id_cliente
    $cli = $conn->query("SELECT id FROM usuarios WHERE perfil = 'usuario' ORDER BY id ASC LIMIT 1");
    $cliente = $cli->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        return ['success' => false, 'message' => 'Nenhum cliente cadastrado para registrar a venda'];
    }
    $id_cliente = $cliente['id'];

    $preco_unit = $valor_total / $quantidade;

    try {
        $conn->beginTransaction();

        // Cabeçalho da venda
        $stmt = $conn->prepare("INSERT INTO vendas (id_produto, id_cliente, quantidade, valor_total, forma_pagamento) VALUES (:id_produto, :id_cliente, :quantidade, :valor_total, :forma_pagamento)");
        $stmt->bindParam(':id_produto', $id, PDO::PARAM_INT);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':forma_pagamento', $forma_pagamento);
        $stmt->execute();

        $id_venda = $conn->lastInsertId();

        // Item da venda
        $stmtItem = $conn->prepare("INSERT INTO vendas_itens (id_venda, id_produto, quantidade, preco_unitario, subtotal) VALUES (:id_venda, :id_produto, :quantidade, :preco, :subtotal)");
        $stmtItem->bindParam(':id_venda', $id_venda, PDO::PARAM_INT);
        $stmtItem->bindParam(':id_produto', $id, PDO::PARAM_INT);
        $stmtItem->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmtItem->bindParam(':preco', $preco_unit);
        $stmtItem->bindParam(':subtotal', $valor_total);
        $stmtItem->execute();

        // Baixa no estoque danificado
        $upd = $conn->prepare("UPDATE produtos SET estoque_danificado = estoque_danificado - :qtd WHERE id = :id");
        $upd->bindParam(':qtd', $quantidade, PDO::PARAM_INT);
        $upd->bindParam(':id', $id, PDO::PARAM_INT);
        $upd->execute();

        $conn->commit();
        return ['success' => true, 'id_venda' => $id_venda];
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
    }
}
?>