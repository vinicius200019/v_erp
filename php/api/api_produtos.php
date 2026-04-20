<?php
header('Content-Type: application/json');
include_once '../db/produtos_db.php';

$produtos = listarProdutos();
echo json_encode($produtos);