<?php
header('Content-Type: application/json');
include_once '../db/compras_db.php';

$compras = listarCompras();
echo json_encode($compras);