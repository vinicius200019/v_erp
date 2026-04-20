<?php
header('Content-Type: application/json');
include_once '../db/dashboard_db.php';

$stats = getStats();

// Formatamos para o JavaScript receber exatamente o que ele espera
echo json_encode([
    "total" => $stats['estoque_total'],
    "valor" => number_format($stats['valor_patrimonio'], 2, ',', '.'),
    "length" => $stats['produtos_count']
]);