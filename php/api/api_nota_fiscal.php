<?php
require_once('../tcpdf/tcpdf.php');
include_once('../db/vendas_db.php');

$id_venda = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_venda <= 0) die('ID de venda inválido');

$venda = getVendaCompleta($id_venda);
if (!$venda) die('Venda não encontrada');

// Helpers
function brl($v) {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function formaPagamentoTexto($fp) {
    $map = [
        'dinheiro' => 'Dinheiro',
        'pix' => 'PIX',
        'cartao' => 'Cartão de Débito',
        'transferencia' => 'Transferência Bancária'
    ];
    return isset($map[$fp]) ? $map[$fp] : ucfirst($fp);
}

function formatarDocumento($doc) {
    if (empty($doc)) return '-';
    $doc = preg_replace('/\D/', '', $doc);
    if (strlen($doc) === 11) {
        return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
    } elseif (strlen($doc) === 14) {
        return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
    }
    return $doc;
}

// =====================================================
// MONTA O PDF
// =====================================================
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('V-ERP');
$pdf->SetTitle('Nota Fiscal #' . str_pad($id_venda, 6, '0', STR_PAD_LEFT));
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

// Cabeçalho
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 12, '  V-ERP - Comprovante de Venda', 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

// Identificação
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Nota Nº ' . str_pad($id_venda, 6, '0', STR_PAD_LEFT), 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$dataFormatada = date('d/m/Y \à\s H:i', strtotime($venda['data_venda']));
$pdf->Cell(0, 6, 'Data da Emissão: ' . $dataFormatada, 0, 1, 'L');

$pdf->Ln(4);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(4);

// Cliente
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Dados do Cliente', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(35, 6, 'E-mail:', 0, 0, 'L');
$pdf->Cell(0, 6, $venda['cliente_email'], 0, 1, 'L');
$pdf->Cell(35, 6, 'CPF/CNPJ:', 0, 0, 'L');
$pdf->Cell(0, 6, formatarDocumento($venda['cliente_documento']), 0, 1, 'L');

$pdf->Ln(4);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(4);

// =====================================================
// ITENS DA VENDA (vários produtos)
// =====================================================
$totalItens = array_sum(array_column($venda['itens'], 'quantidade'));
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Produtos Adquiridos (' . count($venda['itens']) . ' tipo(s), ' . $totalItens . ' un.)', 0, 1, 'L');

// Cabeçalho da tabela
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(25, 7, 'SKU', 1, 0, 'C', true);
$pdf->Cell(75, 7, 'Descrição', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'Qtd', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Valor Unit.', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Subtotal', 1, 1, 'C', true);

// Linhas
$pdf->SetFont('helvetica', '', 10);
$fill = false;
foreach ($venda['itens'] as $item) {
    $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
    $pdf->Cell(25, 8, $item['sku'], 1, 0, 'C', true);
    $pdf->Cell(75, 8, $item['produto_nome'], 1, 0, 'L', true);
    $pdf->Cell(20, 8, $item['quantidade'], 1, 0, 'C', true);
    $pdf->Cell(30, 8, brl($item['preco_unitario']), 1, 0, 'R', true);
    $pdf->Cell(30, 8, brl($item['subtotal']), 1, 1, 'R', true);
    $fill = !$fill;
}

$pdf->Ln(6);

// Forma de pagamento
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'Forma de Pagamento:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, formaPagamentoTexto($venda['forma_pagamento']), 0, 1, 'L');

$pdf->Ln(4);

// TOTAL
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(120, 12, '  VALOR TOTAL PAGO', 0, 0, 'L', true);
$pdf->Cell(60, 12, brl($venda['valor_total']) . '  ', 0, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// Rodapé
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Documento gerado eletronicamente pelo sistema V-ERP.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Emitido em ' . date('d/m/Y H:i:s'), 0, 1, 'C');

$pdf->Output('nota_fiscal_' . str_pad($id_venda, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
?>