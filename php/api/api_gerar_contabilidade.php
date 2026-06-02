<?php
require_once('../tcpdf/tcpdf.php');
require_once('../contabilidade/calculos.php');

function brl($v) {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function fmtData($d) {
    if ($d === '-') return '-';
    return date('d/m/Y', strtotime($d));
}

// =====================================================
// CRIA O PDF
// =====================================================
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('V-ERP');
$pdf->SetAuthor('V-ERP - Sistema de Gestão');
$pdf->SetTitle('Relatório Contábil');
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// =====================================================
// CAPA
// =====================================================
$pdf->AddPage();
$pdf->Ln(35);
$pdf->SetFont('helvetica', 'B', 28);
$pdf->Cell(0, 15, 'RELATÓRIO CONTÁBIL', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 16);
$pdf->Cell(0, 10, 'V-ERP - Sistema de Gestão Empresarial', 0, 1, 'C');
$pdf->Ln(20);

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, 'Este relatório contém:', 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(0, 8, '1. Plano de Contas', 0, 1, 'C');
$pdf->Cell(0, 8, '2. Lançamentos Contábeis (Razonete)', 0, 1, 'C');
$pdf->Cell(0, 8, '3. Controle de Estoque - Custo Médio', 0, 1, 'C');
$pdf->Cell(0, 8, '4. Demonstração do Resultado do Exercício (DRE)', 0, 1, 'C');
$pdf->Cell(0, 8, '5. Balanço Patrimonial', 0, 1, 'C');

$pdf->Ln(20);
$pdf->SetFont('helvetica', 'I', 11);
$pdf->Cell(0, 8, 'Capital Social Inicial: ' . brl(CAPITAL_SOCIAL), 0, 1, 'C');
$pdf->Cell(0, 8, '(50% em Caixa + 50% em Banco)', 0, 1, 'C');
$pdf->Cell(0, 8, 'Tributação: Simples Nacional (' . (ALIQUOTA_IMPOSTO * 100) . '% sobre vendas)', 0, 1, 'C');

$pdf->Ln(15);
$pdf->Cell(0, 8, 'Gerado em ' . date('d/m/Y H:i:s'), 0, 1, 'C');

// =====================================================
// SEÇÃO 1 - PLANO DE CONTAS
// =====================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '1. Plano de Contas', 0, 1, 'L');
$pdf->SetDrawColor(37, 99, 235);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Estrutura hierárquica das contas contábeis utilizadas pelo sistema, baseada na Lei 11.638/07. Cada conta possui um código único e nível de hierarquia.', 0, 'L');
$pdf->Ln(4);

// Cabeçalho
$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 8, 'Código', 1, 0, 'C', true);
$pdf->Cell(140, 8, 'Conta', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$plano = getPlanoContas();
foreach ($plano as $c) {
    // Indentação visual por nível
    $indent = str_repeat('   ', $c['nivel'] - 1);

    // Define cor/negrito conforme nível
    if ($c['nivel'] === 1) {
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('helvetica', 'B', 10);
    } elseif ($c['nivel'] === 2) {
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 9);
    } elseif ($c['nivel'] === 3) {
        $pdf->SetFillColor(250, 250, 250);
        $pdf->SetFont('helvetica', '', 9);
    } else {
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 9);
    }

    $pdf->Cell(40, 6, $c['cod'], 1, 0, 'L', true);
    $pdf->Cell(140, 6, $indent . $c['nome'], 1, 1, 'L', true);
}

// =====================================================
// SEÇÃO 2 - RAZONETE
// =====================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '2. Lançamentos Contábeis (Razonete)', 0, 1, 'L');
$pdf->SetDrawColor(37, 99, 235);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Cada operação econômica gera lançamento de débito e crédito. Vendas geram 3 lançamentos: recebimento, imposto sobre venda (Simples 10%) e baixa do estoque (CMV).', 0, 'L');
$pdf->Ln(4);

$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(22, 8, 'Data', 1, 0, 'C', true);
$pdf->Cell(70, 8, 'Histórico', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Débito', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Crédito', 1, 0, 'C', true);
$pdf->Cell(18, 8, 'Valor', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

$lancamentos = gerarLancamentos();
$fill = false;
foreach ($lancamentos as $l) {
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 247 : 255, $fill ? 250 : 255);
    $pdf->Cell(22, 6, fmtData($l['data']), 1, 0, 'C', true);
    $pdf->Cell(70, 6, $l['historico'], 1, 0, 'L', true);
    $pdf->Cell(35, 6, $l['debito'], 1, 0, 'C', true);
    $pdf->Cell(35, 6, $l['credito'], 1, 0, 'C', true);
    $pdf->Cell(18, 6, brl($l['valor']), 1, 1, 'R', true);
    $fill = !$fill;
}

$totalLanc = 0;
foreach ($lancamentos as $l) $totalLanc += $l['valor'];
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(162, 8, 'TOTAL MOVIMENTADO', 1, 0, 'R', true);
$pdf->Cell(18, 8, brl($totalLanc), 1, 1, 'R', true);

// =====================================================
// SEÇÃO 3 - CUSTO MÉDIO
// =====================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '3. Controle de Estoque - Custo Médio', 0, 1, 'L');
$pdf->SetDrawColor(37, 99, 235);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Método de avaliação onde o custo unitário é recalculado a cada nova compra (média ponderada). As saídas (vendas) sempre baixam pelo custo médio atual, sem alterá-lo.', 0, 'L');
$pdf->Ln(4);

$produtos = calcularCustoMedio();

if (empty($produtos)) {
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->Cell(0, 10, 'Nenhuma movimentação de estoque registrada.', 0, 1, 'C');
} else {
    foreach ($produtos as $prod) {
        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(30, 58, 138);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 9, '  ' . $prod['sku'] . ' - ' . $prod['nome'], 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(20, 7, 'Data', 1, 0, 'C', true);
        $pdf->Cell(15, 7, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(48, 7, 'ENTRADA', 1, 0, 'C', true);
        $pdf->Cell(48, 7, 'SAÍDA', 1, 0, 'C', true);
        $pdf->Cell(49, 7, 'SALDO', 1, 1, 'C', true);

        $pdf->Cell(20, 6, '', 1, 0, 'C', true);
        $pdf->Cell(15, 6, '', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'Qtd', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Unit.', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Total', 1, 0, 'C', true);
        $pdf->Cell(12, 6, 'Qtd', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Unit.', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Total', 1, 0, 'C', true);
        $pdf->Cell(13, 6, 'Qtd', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Unit.', 1, 0, 'C', true);
        $pdf->Cell(18, 6, 'Total', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 7);
        $fill = false;
        foreach ($prod['movimentos'] as $m) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 247 : 255, $fill ? 250 : 255);
            $eh_entrada = $m['tipo'] === 'entrada';

            $pdf->Cell(20, 6, fmtData($m['data']), 1, 0, 'C', true);
            $pdf->Cell(15, 6, $eh_entrada ? 'Entrada' : 'Saída', 1, 0, 'C', true);

            $pdf->Cell(12, 6, $eh_entrada ? $m['qtd'] : '-', 1, 0, 'C', true);
            $pdf->Cell(18, 6, $eh_entrada ? brl($m['unit']) : '-', 1, 0, 'R', true);
            $pdf->Cell(18, 6, $eh_entrada ? brl($m['total']) : '-', 1, 0, 'R', true);

            $pdf->Cell(12, 6, !$eh_entrada ? $m['qtd'] : '-', 1, 0, 'C', true);
            $pdf->Cell(18, 6, !$eh_entrada ? brl($m['unit']) : '-', 1, 0, 'R', true);
            $pdf->Cell(18, 6, !$eh_entrada ? brl($m['total']) : '-', 1, 0, 'R', true);

            $pdf->Cell(13, 6, $m['saldo_qtd'], 1, 0, 'C', true);
            $pdf->Cell(18, 6, brl($m['saldo_unit']), 1, 0, 'R', true);
            $pdf->Cell(18, 6, brl($m['saldo_total']), 1, 1, 'R', true);

            $fill = !$fill;
        }
        $pdf->Ln(5);
    }
}

// =====================================================
// SEÇÃO 4 - DRE
// =====================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '4. Demonstração do Resultado do Exercício (DRE)', 0, 1, 'L');
$pdf->SetDrawColor(37, 99, 235);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Demonstração contábil que apresenta de forma resumida as receitas, custos, despesas e o resultado (lucro ou prejuízo) do período.', 0, 'L');
$pdf->Ln(8);

$dre = calcularDRE();

// Tabela da DRE
$pdf->SetFillColor(30, 58, 138);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(130, 10, '  CONTA', 1, 0, 'L', true);
$pdf->Cell(50, 10, 'VALOR', 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);

$pdf->SetFillColor(240, 253, 244);
$pdf->Cell(130, 8, '(+) Receita Bruta de Vendas', 1, 0, 'L', true);
$pdf->Cell(50, 8, brl($dre['receita_bruta']), 1, 1, 'R', true);

$pdf->SetFillColor(254, 242, 242);
$pdf->Cell(130, 8, '(-) Impostos sobre Vendas (Simples Nacional ' . $dre['aliquota_imposto'] . '%)', 1, 0, 'L', true);
$pdf->Cell(50, 8, '(' . brl($dre['impostos']) . ')', 1, 1, 'R', true);

$pdf->SetFillColor(219, 234, 254);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(130, 9, '= RECEITA LÍQUIDA', 1, 0, 'L', true);
$pdf->Cell(50, 9, brl($dre['receita_liquida']), 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(254, 242, 242);
$pdf->Cell(130, 8, '(-) Custo das Mercadorias Vendidas (CMV)', 1, 0, 'L', true);
$pdf->Cell(50, 8, '(' . brl($dre['cmv']) . ')', 1, 1, 'R', true);

$pdf->SetFillColor(219, 234, 254);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(130, 9, '= LUCRO BRUTO', 1, 0, 'L', true);
$pdf->Cell(50, 9, brl($dre['lucro_bruto']), 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(254, 242, 242);
$pdf->Cell(130, 8, '(-) Despesas Operacionais', 1, 0, 'L', true);
$pdf->Cell(50, 8, '(' . brl($dre['despesas_operacionais']) . ')', 1, 1, 'R', true);

$pdf->SetFillColor(219, 234, 254);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(130, 9, '= LUCRO OPERACIONAL', 1, 0, 'L', true);
$pdf->Cell(50, 9, brl($dre['lucro_operacional']), 1, 1, 'R', true);

// Resultado final destacado
$corResultado = $dre['lucro_liquido'] >= 0 ? [22, 163, 74] : [220, 38, 38];
$pdf->SetFillColor($corResultado[0], $corResultado[1], $corResultado[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(130, 12, ($dre['lucro_liquido'] >= 0 ? '  = LUCRO LÍQUIDO DO EXERCÍCIO' : '  = PREJUÍZO LÍQUIDO DO EXERCÍCIO'), 1, 0, 'L', true);
$pdf->Cell(50, 12, brl($dre['lucro_liquido']), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);

// Análises
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Análise dos Indicadores:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

$margem_bruta = $dre['receita_liquida'] > 0 ? ($dre['lucro_bruto'] / $dre['receita_liquida']) * 100 : 0;
$margem_liquida = $dre['receita_bruta'] > 0 ? ($dre['lucro_liquido'] / $dre['receita_bruta']) * 100 : 0;

$pdf->Cell(0, 6, '• Margem Bruta: ' . number_format($margem_bruta, 2, ',', '.') . '% (Lucro Bruto / Receita Líquida)', 0, 1, 'L');
$pdf->Cell(0, 6, '• Margem Líquida: ' . number_format($margem_liquida, 2, ',', '.') . '% (Lucro Líquido / Receita Bruta)', 0, 1, 'L');
$pdf->Cell(0, 6, '• Carga Tributária: ' . $dre['aliquota_imposto'] . '% sobre as vendas brutas', 0, 1, 'L');

// =====================================================
// SEÇÃO 5 - BALANÇO PATRIMONIAL
// =====================================================
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '5. Balanço Patrimonial', 0, 1, 'L');
$pdf->SetDrawColor(37, 99, 235);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 6, 'Posição patrimonial atual da empresa. A equação fundamental da contabilidade exige que Ativo = Passivo + Patrimônio Líquido.', 0, 'L');
$pdf->Ln(6);

$balanco = calcularBalanco();
$yInicio = $pdf->GetY();

// COLUNA ESQUERDA - ATIVO
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(85, 9, 'ATIVO', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(220, 252, 231);
$pdf->Cell(85, 7, 'ATIVO CIRCULANTE', 1, 1, 'L', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 7, '  Caixa', 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['caixa']), 1, 1, 'R');
$pdf->Cell(50, 7, '  Banco', 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['banco']), 1, 1, 'R');
$pdf->Cell(50, 7, '  Estoque', 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['estoque']), 1, 1, 'R');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(50, 9, 'TOTAL DO ATIVO', 1, 0, 'L', true);
$pdf->Cell(35, 9, brl($balanco['total_ativo']), 1, 1, 'R', true);

$yFimEsq = $pdf->GetY();

// COLUNA DIREITA - PASSIVO + PL
$pdf->SetXY(110, $yInicio);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(220, 38, 38);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(85, 9, 'PASSIVO + PATRIMÔNIO LÍQUIDO', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetX(110);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(254, 226, 226);
$pdf->Cell(85, 7, 'PASSIVO CIRCULANTE', 1, 1, 'L', true);

$pdf->SetX(110);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 7, '  Impostos a Pagar', 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['impostos_a_pagar']), 1, 1, 'R');

$pdf->SetX(110);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(254, 226, 226);
$pdf->Cell(85, 7, 'PATRIMÔNIO LÍQUIDO', 1, 1, 'L', true);

$pdf->SetX(110);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 7, '  Capital Social', 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['capital_social']), 1, 1, 'R');

$pdf->SetX(110);
$lucroLabel = $balanco['lucro_acumulado'] >= 0 ? '  Lucros Acumulados' : '  Prejuízos Acumulados';
$pdf->Cell(50, 7, $lucroLabel, 1, 0, 'L');
$pdf->Cell(35, 7, brl($balanco['lucro_acumulado']), 1, 1, 'R');

$pdf->SetX(110);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(220, 38, 38);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(50, 9, 'TOTAL PASSIVO + PL', 1, 0, 'L', true);
$pdf->Cell(35, 9, brl($balanco['total_passivo_pl']), 1, 1, 'R', true);

$pdf->SetY(max($yFimEsq, $pdf->GetY()) + 10);
$pdf->SetTextColor(0, 0, 0);

// Validação contábil
$diferenca = $balanco['total_ativo'] - $balanco['total_passivo_pl'];
$confere = abs($diferenca) < 0.01;

$pdf->SetFont('helvetica', 'B', 11);
if ($confere) {
    $pdf->SetFillColor(220, 252, 231);
    $pdf->SetTextColor(21, 128, 61);
    $pdf->Cell(0, 10, '  Balanço Patrimonial conferido: Ativo = Passivo + PL', 0, 1, 'L', true);
} else {
    $pdf->SetFillColor(254, 226, 226);
    $pdf->SetTextColor(185, 28, 28);
    $pdf->Cell(0, 10, '  ATENÇÃO: Diferença de ' . brl($diferenca) . ' entre Ativo e Passivo+PL', 0, 1, 'L', true);
}

$pdf->SetTextColor(0, 0, 0);

$pdf->Output('relatorio_contabil.pdf', 'I');
?>