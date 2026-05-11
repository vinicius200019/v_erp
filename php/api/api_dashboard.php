<?php
header('Content-Type: application/json');
include_once '../db/dashboard_db.php';

// Se vier com parâmetro ?simple=1, retorna formato antigo (pro index.html)
if (isset($_GET['simple'])) {
    $stats = getStats();
    echo json_encode([
        "total" => $stats['estoque_total'],
        "valor" => number_format($stats['valor_patrimonio'], 2, ',', '.'),
        "length" => $stats['produtos_count']
    ]);
    exit;
}

// Padrão: retorna o dashboard completo
echo json_encode(getDashboardCompleto());