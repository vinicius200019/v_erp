<?php
header('Content-Type: application/json');
include_once '../db/sidebar_db.php';

// Busca a estrutura de menu definida no banco ou no arquivo DB
$menu = getMenu();

// Retorna os links para o JavaScript
echo json_encode($menu);