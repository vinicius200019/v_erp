<?php
header('Content-Type: application/json');
include_once '../db/usuarios_db.php';

$usuarios = listarEquipe();
echo json_encode($usuarios);