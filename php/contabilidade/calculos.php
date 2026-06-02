<?php
include_once __DIR__ . '/../db/conexao.php';

// ============================================================
// CONSTANTES
// ============================================================
define('CAIXA_INICIAL', 50000.00);
define('BANCO_INICIAL', 50000.00);
define('CAPITAL_SOCIAL', 100000.00);
define('ALIQUOTA_IMPOSTO', 0.10); // 10% Simples Nacional

function contaPagamento($forma_pagamento) {
    return ($forma_pagamento === 'dinheiro') ? 'Caixa' : 'Banco';
}

/**
 * Linha do tempo unificada de operações.
 */
function getLinhaDoTempo() {
    global $conn;
    $ops = [];

    $sqlC = "SELECT c.id, c.id_produto, c.quantidade, c.valor_total, 
                    c.forma_pagamento, c.data_compra as data, 
                    p.nome as produto_nome, p.sku
             FROM compras c
             JOIN produtos p ON c.id_produto = p.id";
    foreach ($conn->query($sqlC) as $r) {
        $r['tipo'] = 'compra';
        $ops[] = $r;
    }

    $sqlV = "SELECT v.id as id_venda, v.forma_pagamento, v.data_venda as data, v.id_cliente,
                    vi.id_produto, vi.quantidade, vi.preco_unitario, vi.subtotal,
                    p.nome as produto_nome, p.sku
             FROM vendas v
             JOIN vendas_itens vi ON vi.id_venda = v.id
             JOIN produtos p ON vi.id_produto = p.id";
    foreach ($conn->query($sqlV) as $r) {
        $r['tipo'] = 'venda';
        $r['valor_total'] = $r['subtotal'];
        $ops[] = $r;
    }

    usort($ops, function($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

    return $ops;
}

/**
 * Calcula custo médio histórico de cada produto.
 */
function calcularCustoMedio() {
    $operacoes = getLinhaDoTempo();
    $produtos = [];

    foreach ($operacoes as $op) {
        $id = $op['id_produto'];

        if (!isset($produtos[$id])) {
            $produtos[$id] = [
                'nome' => $op['produto_nome'],
                'sku' => $op['sku'],
                'saldo_qtd' => 0,
                'saldo_total' => 0,
                'saldo_unit' => 0,
                'movimentos' => []
            ];
        }

        $p = &$produtos[$id];

        if ($op['tipo'] === 'compra') {
            $qtd = (int) $op['quantidade'];
            $total = (float) $op['valor_total'];
            $unit = $qtd > 0 ? $total / $qtd : 0;

            $p['saldo_qtd'] += $qtd;
            $p['saldo_total'] += $total;
            $p['saldo_unit'] = $p['saldo_qtd'] > 0 ? $p['saldo_total'] / $p['saldo_qtd'] : 0;

            $p['movimentos'][] = [
                'data' => $op['data'],
                'tipo' => 'entrada',
                'qtd' => $qtd,
                'unit' => $unit,
                'total' => $total,
                'saldo_qtd' => $p['saldo_qtd'],
                'saldo_unit' => $p['saldo_unit'],
                'saldo_total' => $p['saldo_total']
            ];
        } else {
            $qtd = (int) $op['quantidade'];
            $unit_saida = $p['saldo_unit'];
            $total_saida = $qtd * $unit_saida;

            $p['saldo_qtd'] -= $qtd;
            $p['saldo_total'] -= $total_saida;

            $p['movimentos'][] = [
                'data' => $op['data'],
                'tipo' => 'saida',
                'qtd' => $qtd,
                'unit' => $unit_saida,
                'total' => $total_saida,
                'saldo_qtd' => $p['saldo_qtd'],
                'saldo_unit' => $p['saldo_unit'],
                'saldo_total' => $p['saldo_total']
            ];
        }
    }

    return $produtos;
}

/**
 * Gera lançamentos contábeis (razonete), AGORA com imposto.
 */
function gerarLancamentos() {
    global $conn;
    $lancamentos = [];

    // Capital inicial
    $lancamentos[] = [
        'data' => '-',
        'historico' => 'Integralização de Capital Social',
        'debito' => 'Caixa',
        'credito' => 'Capital Social',
        'valor' => CAIXA_INICIAL
    ];
    $lancamentos[] = [
        'data' => '-',
        'historico' => 'Integralização de Capital Social',
        'debito' => 'Banco',
        'credito' => 'Capital Social',
        'valor' => BANCO_INICIAL
    ];

    $estado = [];
    $eventos = [];

    foreach ($conn->query("SELECT c.id, c.id_produto, c.quantidade, c.valor_total, c.forma_pagamento, c.data_compra as data, p.nome as produto_nome FROM compras c JOIN produtos p ON c.id_produto = p.id") as $r) {
        $r['tipo'] = 'compra';
        $eventos[] = $r;
    }

    foreach ($conn->query("SELECT v.id, v.valor_total, v.forma_pagamento, v.data_venda as data FROM vendas v") as $r) {
        $r['tipo'] = 'venda';
        $eventos[] = $r;
    }

    usort($eventos, function($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

    foreach ($eventos as $ev) {
        if ($ev['tipo'] === 'compra') {
            $idProd = $ev['id_produto'];
            if (!isset($estado[$idProd])) {
                $estado[$idProd] = ['qtd' => 0, 'total' => 0, 'unit' => 0];
            }

            $qtd = (int) $ev['quantidade'];
            $total = (float) $ev['valor_total'];

            $estado[$idProd]['qtd'] += $qtd;
            $estado[$idProd]['total'] += $total;
            $estado[$idProd]['unit'] = $estado[$idProd]['qtd'] > 0 
                ? $estado[$idProd]['total'] / $estado[$idProd]['qtd'] 
                : 0;

            $contaPg = contaPagamento($ev['forma_pagamento']);
            $lancamentos[] = [
                'data' => $ev['data'],
                'historico' => 'Compra de ' . $qtd . ' un. de ' . $ev['produto_nome'],
                'debito' => 'Estoque',
                'credito' => $contaPg,
                'valor' => $total
            ];
        } else {
            $contaPg = contaPagamento($ev['forma_pagamento']);
            $valor_venda = (float) $ev['valor_total'];
            $imposto = $valor_venda * ALIQUOTA_IMPOSTO;

            // Recebimento da venda (valor bruto entra em caixa/banco)
            $lancamentos[] = [
                'data' => $ev['data'],
                'historico' => 'Recebimento de venda #' . $ev['id'],
                'debito' => $contaPg,
                'credito' => 'Receita de Vendas',
                'valor' => $valor_venda
            ];

            // Imposto sobre venda (Simples Nacional 10%)
            $lancamentos[] = [
                'data' => $ev['data'],
                'historico' => 'Imposto sobre venda #' . $ev['id'] . ' (Simples Nacional 10%)',
                'debito' => 'Impostos sobre Vendas',
                'credito' => 'Impostos a Pagar',
                'valor' => $imposto
            ];

            // Baixa do estoque por item
            $stmtItens = $conn->prepare("SELECT vi.id_produto, vi.quantidade, p.nome as produto_nome FROM vendas_itens vi JOIN produtos p ON vi.id_produto = p.id WHERE vi.id_venda = :id");
            $stmtItens->bindParam(':id', $ev['id'], PDO::PARAM_INT);
            $stmtItens->execute();
            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

            foreach ($itens as $item) {
                $idProd = $item['id_produto'];
                if (!isset($estado[$idProd])) {
                    $estado[$idProd] = ['qtd' => 0, 'total' => 0, 'unit' => 0];
                }

                $qtd = (int) $item['quantidade'];
                $custo_atual = $estado[$idProd]['unit'];
                $cmv = $qtd * $custo_atual;

                $estado[$idProd]['qtd'] -= $qtd;
                $estado[$idProd]['total'] -= $cmv;

                $lancamentos[] = [
                    'data' => $ev['data'],
                    'historico' => 'Baixa de estoque - ' . $qtd . ' un. ' . $item['produto_nome'],
                    'debito' => 'CMV',
                    'credito' => 'Estoque',
                    'valor' => $cmv
                ];
            }
        }
    }

    return $lancamentos;
}

/**
 * Balanço Patrimonial (agora com "Impostos a Pagar" no passivo).
 */
function calcularBalanco() {
    global $conn;

    $caixa = CAIXA_INICIAL;
    $banco = BANCO_INICIAL;
    $estado = [];
    $receita_total = 0;
    $cmv_total = 0;
    $impostos_total = 0;

    $eventos = [];
    foreach ($conn->query("SELECT id_produto, quantidade, valor_total, forma_pagamento, data_compra as data FROM compras") as $r) {
        $r['tipo'] = 'compra';
        $eventos[] = $r;
    }
    foreach ($conn->query("SELECT id, valor_total, forma_pagamento, data_venda as data FROM vendas") as $r) {
        $r['tipo'] = 'venda';
        $eventos[] = $r;
    }
    usort($eventos, function($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

    foreach ($eventos as $ev) {
        if ($ev['tipo'] === 'compra') {
            $idProd = $ev['id_produto'];
            $total = (float) $ev['valor_total'];
            $qtd = (int) $ev['quantidade'];

            if (!isset($estado[$idProd])) {
                $estado[$idProd] = ['qtd' => 0, 'total' => 0, 'unit' => 0];
            }
            $estado[$idProd]['qtd'] += $qtd;
            $estado[$idProd]['total'] += $total;
            $estado[$idProd]['unit'] = $estado[$idProd]['qtd'] > 0 
                ? $estado[$idProd]['total'] / $estado[$idProd]['qtd'] 
                : 0;

            if ($ev['forma_pagamento'] === 'dinheiro') $caixa -= $total;
            else $banco -= $total;
        } else {
            $valor_venda = (float) $ev['valor_total'];
            $receita_total += $valor_venda;
            $impostos_total += $valor_venda * ALIQUOTA_IMPOSTO;

            if ($ev['forma_pagamento'] === 'dinheiro') $caixa += $valor_venda;
            else $banco += $valor_venda;

            $stmtItens = $conn->prepare("SELECT id_produto, quantidade FROM vendas_itens WHERE id_venda = :id");
            $stmtItens->bindParam(':id', $ev['id'], PDO::PARAM_INT);
            $stmtItens->execute();
            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

            foreach ($itens as $item) {
                $idProd = $item['id_produto'];
                $qtd = (int) $item['quantidade'];

                if (!isset($estado[$idProd])) {
                    $estado[$idProd] = ['qtd' => 0, 'total' => 0, 'unit' => 0];
                }
                $custo = $estado[$idProd]['unit'];
                $cmv = $qtd * $custo;

                $estado[$idProd]['qtd'] -= $qtd;
                $estado[$idProd]['total'] -= $cmv;
                $cmv_total += $cmv;
            }
        }
    }

    $valor_estoque = 0;
    foreach ($estado as $e) {
        $valor_estoque += $e['total'];
    }

    // Lucro = Receita - Impostos - CMV (despesas operacionais = 0)
    $lucro_acumulado = $receita_total - $impostos_total - $cmv_total;

    return [
        'caixa' => $caixa,
        'banco' => $banco,
        'estoque' => $valor_estoque,
        'total_ativo' => $caixa + $banco + $valor_estoque,
        'impostos_a_pagar' => $impostos_total,
        'total_passivo' => $impostos_total,
        'capital_social' => CAPITAL_SOCIAL,
        'lucro_acumulado' => $lucro_acumulado,
        'total_pl' => CAPITAL_SOCIAL + $lucro_acumulado,
        'total_passivo_pl' => $impostos_total + CAPITAL_SOCIAL + $lucro_acumulado,
        'receita_total' => $receita_total,
        'cmv_total' => $cmv_total,
        'impostos_total' => $impostos_total
    ];
}

/**
 * Calcula a Demonstração do Resultado do Exercício (DRE).
 */
function calcularDRE() {
    $balanco = calcularBalanco();

    $receita_bruta = $balanco['receita_total'];
    $impostos = $balanco['impostos_total'];
    $receita_liquida = $receita_bruta - $impostos;
    $cmv = $balanco['cmv_total'];
    $lucro_bruto = $receita_liquida - $cmv;
    $despesas_operacionais = 0; // não temos despesas registradas
    $lucro_operacional = $lucro_bruto - $despesas_operacionais;
    $lucro_liquido = $lucro_operacional;

    return [
        'receita_bruta' => $receita_bruta,
        'impostos' => $impostos,
        'aliquota_imposto' => ALIQUOTA_IMPOSTO * 100,
        'receita_liquida' => $receita_liquida,
        'cmv' => $cmv,
        'lucro_bruto' => $lucro_bruto,
        'despesas_operacionais' => $despesas_operacionais,
        'lucro_operacional' => $lucro_operacional,
        'lucro_liquido' => $lucro_liquido
    ];
}

/**
 * Plano de Contas hierárquico (baseado no Anexo 1 do PDF do professor).
 */
function getPlanoContas() {
    return [
        ['cod' => '1', 'nivel' => 1, 'nome' => 'ATIVO'],
        ['cod' => '1.1', 'nivel' => 2, 'nome' => 'CIRCULANTE'],
        ['cod' => '1.1.1', 'nivel' => 3, 'nome' => 'Disponível'],
        ['cod' => '1.1.1.1', 'nivel' => 4, 'nome' => 'Caixa'],
        ['cod' => '1.1.1.2', 'nivel' => 4, 'nome' => 'Bancos Conta Movimento'],
        ['cod' => '1.1.3', 'nivel' => 3, 'nome' => 'Clientes'],
        ['cod' => '1.1.3.1', 'nivel' => 4, 'nome' => 'Duplicatas a Receber de Clientes'],
        ['cod' => '1.1.5', 'nivel' => 3, 'nome' => 'Estoques'],
        ['cod' => '1.1.5.1', 'nivel' => 4, 'nome' => 'Mercadorias para Revenda'],
        ['cod' => '1.2', 'nivel' => 2, 'nome' => 'NÃO CIRCULANTE'],
        ['cod' => '1.2.3', 'nivel' => 3, 'nome' => 'Imobilizado'],
        ['cod' => '1.2.3.5', 'nivel' => 4, 'nome' => 'Móveis e Utensílios'],
        ['cod' => '1.2.3.6', 'nivel' => 4, 'nome' => 'Veículos'],

        ['cod' => '2', 'nivel' => 1, 'nome' => 'PASSIVO'],
        ['cod' => '2.1', 'nivel' => 2, 'nome' => 'CIRCULANTE'],
        ['cod' => '2.1.1', 'nivel' => 3, 'nome' => 'Fornecedores'],
        ['cod' => '2.1.2', 'nivel' => 3, 'nome' => 'Contas a Pagar'],
        ['cod' => '2.1.5', 'nivel' => 3, 'nome' => 'Impostos a Pagar'],
        ['cod' => '2.1.5.1', 'nivel' => 4, 'nome' => 'Simples Nacional a Recolher'],
        ['cod' => '2.3', 'nivel' => 2, 'nome' => 'PATRIMÔNIO LÍQUIDO'],
        ['cod' => '2.3.1', 'nivel' => 3, 'nome' => 'Capital Social'],
        ['cod' => '2.3.4', 'nivel' => 3, 'nome' => 'Lucros ou Prejuízos Acumulados'],

        ['cod' => '3', 'nivel' => 1, 'nome' => 'CONTAS DE RESULTADO'],
        ['cod' => '3.1', 'nivel' => 2, 'nome' => 'RECEITA BRUTA DE VENDAS'],
        ['cod' => '3.1.1', 'nivel' => 3, 'nome' => 'Receita de Vendas de Mercadorias'],
        ['cod' => '3.3', 'nivel' => 2, 'nome' => 'IMPOSTOS SOBRE VENDAS'],
        ['cod' => '3.3.1', 'nivel' => 3, 'nome' => 'Simples Nacional'],
        ['cod' => '3.4', 'nivel' => 2, 'nome' => 'CUSTO DA MERCADORIA VENDIDA'],
        ['cod' => '3.4.1', 'nivel' => 3, 'nome' => 'CMV - Custo das Mercadorias Vendidas'],
        ['cod' => '3.5', 'nivel' => 2, 'nome' => 'DESPESAS OPERACIONAIS'],
        ['cod' => '3.5.1', 'nivel' => 3, 'nome' => 'Despesas Gerais e Administrativas'],
    ];
}
?>