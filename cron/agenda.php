<?php

// ================= CONFIG
$sheet = "1if2BXY3NJwSRgGfx7RUg4rfzaXOBARYgZV_U-Ic7t_Y";
$aba   = "configuracao";

$url = "https://opensheet.elk.sh/{$sheet}/" . urlencode($aba);

// caminho do cache
$cacheDir  = __DIR__ . '/../cache';
$cacheFile = $cacheDir . "/{$aba}.json";

// ================= LOG
echo "==== CRON CACHE INICIADO ====\n";
echo "Data: " . date('d/m/Y H:i:s') . "\n";

// ================= GARANTE PASTA
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
    echo "Pasta de cache criada\n";
}

// ================= BUSCA DADOS
$json = @file_get_contents($url);

if ($json === false) {
    echo "❌ ERRO ao acessar API\n";

    if (file_exists($cacheFile)) {
        echo "⚠️ Mantendo cache antigo\n";
        exit;
    } else {
        echo "❌ Sem cache disponível\n";
        exit;
    }
}

// ================= VALIDA JSON
$dados = json_decode($json, true);

if (!$dados) {
    echo "❌ JSON inválido\n";
    exit;
}

// ================= SALVA CACHE
if (file_put_contents($cacheFile, $json)) {
    echo "✅ Cache atualizado com sucesso\n";
    echo "Total registros: " . count($dados) . "\n";
} else {
    echo "❌ Erro ao salvar cache\n";
}

echo "==== FINALIZADO ====\n";