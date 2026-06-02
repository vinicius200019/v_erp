<?php
header('Content-Type: application/json');
include_once '../db/vendas_db.php';

if (isset($_GET['id_cliente'])) {
    $compras = listarVendasCliente(intval($_GET['id_cliente']));
    echo json_encode($compras);
} else {
    echo json_encode(['error' => 'ID do cliente não informado']);
}