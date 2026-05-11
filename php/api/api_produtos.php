<?php
header('Content-Type: application/json');
include_once '../db/produtos_db.php';

// Aceita ?ativo=0 ou ?ativo=1 (padrão: 1 = ativos)
$ativo = isset($_GET['ativo']) ? intval($_GET['ativo']) : 1;
$produtos = listarProdutos($ativo);
echo json_encode($produtos);