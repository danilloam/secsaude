<?php
require_once __DIR__ . '/../CORE/bootstrap.php';
require_once __DIR__ . '/../func.php';

header('Content-Type: application/json; charset=utf-8');

// 🔒 evita quebra de JSON por warnings/notices
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 📁 diretório de cache
$cacheDir = __DIR__ . '/../CORE/cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

// 🔑 cache único por combinação de filtros
$cacheKey  = md5(json_encode($_GET));
$cacheFile = $cacheDir . "dados_{$cacheKey}.json";
$cacheTime = 86400; // 1 dia

// =========================
// 1. TENTA CACHE
// =========================
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// =========================
// 2. MONTA FILTROS
// =========================
$filtros = [];

if (!empty($_GET['cnes'])) {
    $filtros['cnes'] = array_filter(explode(',', $_GET['cnes']));
} else {
    $unidadesDistrito = listarUnidadesDistrito($_SESSION['user']['distrito_id']);
    $filtros['cnes'] = $unidadesDistrito;
}

if (!empty($_GET['equipe'])) {
    $filtros['equipe'] = $_GET['equipe'];
}

if (!empty($_GET['acs'])) {
    $filtros['acs'] = $_GET['acs'];
}

if (!empty($_GET['microarea'])) {
    $filtros['microarea'] = $_GET['microarea'];
}

// =========================
// 3. EXECUÇÃO PRINCIPAL
// =========================
try {

    error_log('FILTROS: ' . print_r($filtros, true));

    $dados = listarVisitasUltimos12Meses($filtros);

    if (!is_array($dados)) {
        $dados = [];
    }

    $response = [
        'status' => 'success',
        'data' => $dados
    ];

    $json = json_encode($response, JSON_UNESCAPED_UNICODE);

    // =========================
    // 4. SALVA CACHE
    // =========================
    file_put_contents($cacheFile, $json);

    echo $json;

} catch (Throwable $e) {

    http_response_code(500);

    error_log('ERRO API: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno no servidor'
    ], JSON_UNESCAPED_UNICODE);
}