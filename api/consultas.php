<?php

require_once __DIR__ . '/../helpers/func.php';

header('Content-Type: application/json; charset=utf-8');

// 🔒 evita erro quebrar JSON
ini_set('display_errors', 0);

// 📥 dados
$dados = getConsultasCacheFull();

// 🔍 filtros via GET
$cnes   = $_GET['cnes']   ?? null;
$equipe = $_GET['equipe'] ?? null;
$data   = $_GET['data']   ?? null;

$filtrado = array_values(array_filter($dados, function($item) use ($cnes, $equipe, $data) {

    if ($cnes && $item['cnes'] != $cnes) return false;
    if ($equipe && $item['equipe'] != $equipe) return false;
    if ($data && $item['data'] != $data) return false;

    return true;
}));

echo json_encode($filtrado, JSON_UNESCAPED_UNICODE);