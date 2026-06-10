<?php
include_once 'conexao.php';

function getDashboardCompleto() {
    global $conn;
    $resultado = [];

    // === KPIs de estoque ===
    $resultado['produtos_ativos'] = (int) $conn->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
    $resultado['itens_estoque'] = (int) ($conn->query("SELECT SUM(estoque) FROM produtos WHERE ativo = 1")->fetchColumn() ?? 0);
    $resultado['valor_patrimonio'] = (float) ($conn->query("SELECT SUM(estoque * preco_venda) FROM produtos WHERE ativo = 1")->fetchColumn() ?? 0);

    // === Compras do mês atual ===
    $sqlCompras = "SELECT COUNT(*) as qtd, COALESCE(SUM(valor_total), 0) as valor 
                   FROM compras 
                   WHERE MONTH(data_compra) = MONTH(CURRENT_DATE()) 
                   AND YEAR(data_compra) = YEAR(CURRENT_DATE())";
    $comp = $conn->query($sqlCompras)->fetch(PDO::FETCH_ASSOC);
    $resultado['compras_mes_qtd'] = (int) $comp['qtd'];
    $resultado['compras_mes_valor'] = (float) $comp['valor'];

    // === Vendas do mês atual (cabeçalho, não item) ===
    $sqlVendas = "SELECT COUNT(*) as qtd, COALESCE(SUM(valor_total), 0) as valor 
                  FROM vendas 
                  WHERE MONTH(data_venda) = MONTH(CURRENT_DATE()) 
                  AND YEAR(data_venda) = YEAR(CURRENT_DATE())";
    $vend = $conn->query($sqlVendas)->fetch(PDO::FETCH_ASSOC);
    $resultado['vendas_mes_qtd'] = (int) $vend['qtd'];
    $resultado['vendas_mes_valor'] = (float) $vend['valor'];

    // === Comparativo 6 meses ===
    $resultado['comparativo_6meses'] = getComparativo6Meses($conn);

    // === Top 5 produtos mais vendidos - agora usa vendas_itens ===
    $sqlTop = "SELECT p.nome, 
                      SUM(vi.quantidade) as unidades, 
                      SUM(vi.subtotal) as receita
               FROM vendas_itens vi
               JOIN produtos p ON vi.id_produto = p.id
               GROUP BY vi.id_produto, p.nome
               ORDER BY unidades DESC
               LIMIT 5";
    $resultado['top_produtos'] = $conn->query($sqlTop)->fetchAll(PDO::FETCH_ASSOC);

    // === Estoque crítico ===
    $sqlCrit = "SELECT sku, nome, estoque 
                FROM produtos 
                WHERE ativo = 1 AND estoque < 5 
                ORDER BY estoque ASC";
    $resultado['estoque_critico'] = $conn->query($sqlCrit)->fetchAll(PDO::FETCH_ASSOC);

    return $resultado;
}

function getComparativo6Meses($conn) {
    $dados = [];
    $meses_pt = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

    for ($i = 5; $i >= 0; $i--) {
        $data = date('Y-m-01', strtotime("-$i months"));
        $ano = (int) date('Y', strtotime($data));
        $mes = (int) date('m', strtotime($data));

        $sqlC = "SELECT COALESCE(SUM(valor_total), 0) FROM compras 
                 WHERE YEAR(data_compra) = :ano AND MONTH(data_compra) = :mes";
        $stmtC = $conn->prepare($sqlC);
        $stmtC->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmtC->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmtC->execute();
        $compras = (float) $stmtC->fetchColumn();

        $sqlV = "SELECT COALESCE(SUM(valor_total), 0) FROM vendas 
                 WHERE YEAR(data_venda) = :ano AND MONTH(data_venda) = :mes";
        $stmtV = $conn->prepare($sqlV);
        $stmtV->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmtV->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmtV->execute();
        $vendas = (float) $stmtV->fetchColumn();

        $dados[] = [
            'mes' => $meses_pt[$mes] . '/' . substr($ano, 2),
            'compras' => $compras,
            'vendas' => $vendas
        ];
    }

    return $dados;
}

function getStats() {
    global $conn;
    $stats = [];
    $stats['produtos_count'] = $conn->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
    $stats['estoque_total'] = $conn->query("SELECT SUM(estoque) FROM produtos WHERE ativo = 1")->fetchColumn() ?? 0;
    $stats['valor_patrimonio'] = $conn->query("SELECT SUM(estoque * preco_venda) FROM produtos WHERE ativo = 1")->fetchColumn() ?? 0;
    $stats['usuarios_count'] = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    return $stats;
}
?>