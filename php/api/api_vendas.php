<?php
header('Content-Type: application/json');
include_once '../db/vendas_db.php';

$vendas = listarTodasVendas();
echo json_encode($vendas);