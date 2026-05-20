<?php
declare(strict_types=1);

// Evitar que o script caia por timeout em bases grandes
set_time_limit(0);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../CORE/bootstrap.php';
require_once __DIR__ . '/../helpers/func.php';

echo "=== INICIANDO GERAÇÃO DIÁRIA DE CACHE ===\n";

// Forçar o ambiente a se comportar como produção temporariamente para permitir a escrita
$baseCacheDir = __DIR__ . '/../cache/distritos/';

if (!is_dir($baseCacheDir)) {
    mkdir($baseCacheDir, 0755, true);
}

// Buscar todos os distritos ativos do seu banco MySQL
$pdoMysql = mysqlConnect();
$stmtDistritos = $pdoMysql->query("SELECT DISTINCT distrito_id FROM us_distrito WHERE ativo = '1'");
$distritos = $stmtDistritos->fetchAll(PDO::FETCH_COLUMN);

foreach ($distritos as $distritoId) {
    if (empty($distritoId)) continue;
    
    echo "Processando Distrito ID: {$distritoId}...\n";
    
    // Obter as unidades (CNES) deste distrito
    $unidades = listarUnidadesDistrito((int)$distritoId);
    if (empty($unidades)) {
        echo "   -> Nenhuma unidade encontrada. Pulando.\n";
        continue;
    }

    $filtrosBase = [
        'cnes' => $unidades,
        'tipo' => ['medico', 'enfermeiro', 'odonto']
    ];

    try {
        echo "   -> Gerando cache de Atendimentos (12 meses)...\n";
        $atendimentosDados = listarAtendimentosUltimos12Meses($filtrosBase);

        echo "   -> Gerando cache de Escuta Inicial (12 meses)...\n";
        $escutaDados = listarEscutaInicialUltimos12Meses($filtrosBase);

        echo "   -> Gerando cache de Visitas (12 meses)...\n";
        $visitasDados = listarVisitasUltimos12Meses($filtrosBase);

        // Monta a estrutura unificada por distrito
        $cacheData = [
            'gerado_em'     => date('Y-m-d H:i:s'),
            'atendimentos'  => is_array($atendimentosDados) ? $atendimentosDados : [],
            'escutainicial' => is_array($escutaDados) ? $escutaDados : [],
            'visitas'       => is_array($visitasDados) ? $visitasDados : []
        ];

        // Salva o JSON único do distrito
        $arquivoDestino = $baseCacheDir . "distrito_{$distritoId}_full.json";
        file_put_contents($arquivoDestino, json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        echo "   ✅ Cache do Distrito {$distritoId} salvo com sucesso!\n";

    } catch (Throwable $e) {
        echo "   ❌ ERRO ao processar Distrito {$distritoId}: " . $e->getMessage() . "\n";
    }
}

echo "=== PROCESSO CONCLUÍDO ===\n";