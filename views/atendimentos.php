<?php
require_once __DIR__ . '/../CORE/bootstrap.php';
require_once __DIR__ . '/../helpers/func.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$distritoId = $_SESSION['user']['distrito_id'] ?? null;

// ==========================================
// 1. TENTATIVA DE LEITURA RÁPIDA VIA CACHE
// ==========================================
// Só usamos o cache geral se NÃO houver filtros específicos na URL (como equipe ou microárea)
if (empty($_GET['cnes']) && empty($_GET['equipe']) && empty($_GET['microarea']) && empty($_GET['acs']) && $distritoId) {
    
    $arquivoCache = __DIR__ . "/../cache/distritos/distrito_{$distritoId}_full.json";
    
    if (file_exists($arquivoCache)) {
        $cacheCompleto = json_decode(file_get_contents($arquivoCache), true);
        
        if (isset($cacheCompleto['atendimentos'])) {
            echo json_encode([
                'status' => 'success',
                'source' => 'cache',
                'data'   => $cacheCompleto['atendimentos']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

// ==========================================
// 2. FALLBACK OPERACIONAL (BANCO DE DADOS)
// ==========================================
$filtros = [];

if (!empty($_GET['cnes'])) {
    $filtros['cnes'] = array_filter(explode(',', $_GET['cnes']));
} else {
    $filtros['cnes'] = listarUnidadesDistrito((int)$distritoId);
}

if (!empty($_GET['equipe'])) $filtros['equipe'] = $_GET['equipe'];
if (!empty($_GET['acs']))    $filtros['acs'] = $_GET['acs'];
if (!empty($_GET['microarea'])) $filtros['microarea'] = $_GET['microarea'];

$filtros['tipo'] = ['medico', 'enfermeiro', 'odonto'];

try {
    $dados = listarAtendimentosUltimos12Meses($filtros);

    echo json_encode([
        'status' => 'success',
        'source' => 'database',
        'data'   => is_array($dados) ? $dados : []
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    error_log('ERRO ATENDIMENTOS: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno no servidor'
    ], JSON_UNESCAPED_UNICODE);
}