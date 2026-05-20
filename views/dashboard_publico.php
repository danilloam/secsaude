<?php
// 🔥 REMOVIDO: session_guard e qualquer validação de login.
// Apenas as dependências de conexão e funções de dados são importadas.
require_once __DIR__ . '/../CORE/config.php';
require_once __DIR__ . '/../CORE/bootstrap.php';
include_once __DIR__ . '/../helpers/func.php';

// Captura o Distrito ID diretamente da URL. Se não for informado, assume o Distrito 1 por padrão.
$distrito_id = isset($_GET['distrito_id']) ? (int)$_GET['distrito_id'] : 1;

// Definição do ano e mês vigentes
$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int)date('m');

// Captura a lista de todos os CNES pertencentes a este Distrito informados na URL
$listaCnesCompleta = (array) listarUnidadesDistrito($distrito_id);

if (empty($listaCnesCompleta)) {
    die("<div class='container mt-5 alert alert-danger text-center'><h4>Erro: Nenhum CNES ou Unidade encontrada para o Distrito ID: <strong>$distrito_id</strong>.</h4><p>Verifique o parâmetro '?distrito_id=' na URL.</p></div>");
}

// Trata qual CNES está ativo na rotação atual da TV. Se não houver na URL, pega o primeiro da lista.
$cnesAtivo = isset($_GET['cnes']) ? $_GET['cnes'] : reset($listaCnesCompleta);

/*
|--------------------------------------------------------------------------
| FUNÇÕES AUXILIARES ADICIONADAS PARA ANULAR O ERRO DE FUNÇÃO INDEFINIDA
|--------------------------------------------------------------------------
*/
function removerZerosEsquerdaArray(array $dados): array
{
    return array_map(function ($valor) {
        $valor = (string) $valor;
        $valor = trim($valor);
        $valor = ltrim($valor, '0');
        if ($valor === '') {
            $valor = '0';
        }
        return $valor;
    }, $dados);
}

function lerGoogleSheetTratado($sheetId, $aba = 'Aba1', array $cnesDistrito = []) 
{
    $url = "https://opensheet.elk.sh/{$sheetId}/" . urlencode($aba);
    $json = @file_get_contents($url);

    if (!$json) {
        return [];
    }

    $dados = json_decode($json, true);
    if (!is_array($dados)) {
        return [];
    }

    $saida = [];
    foreach ($dados as $linha) {
        $nova = [];
        foreach ($linha as $chave => $valor) {
            $chave = trim($chave);
            if ($chave === '' || $chave === 'undefined') {
                $chave = 'Observação';
            }

            if ($valor === '' || $valor === null) {
                $valor = null;
            } else {
                $valor = trim((string)$valor);
            }

            if ($chave === 'CNES') {
                $valor = ltrim((string)($valor ?? ''), '0');
                if ($valor === '') {
                    $valor = '0';
                }
            }
            $nova[$chave] = $valor;
        }

        $cnes = $nova['CNES'] ?? null;
        if ($cnes === null || $cnes === '' || $cnes === '-') {
            continue;
        }

        if (!isset($cnesDistrito[$cnes])) {
            continue;
        }
        $saida[] = $nova;
    }
    return $saida;
}

// --- 1. BUSCA DE DADOS GLOBAIS / VISITAS ---
$filtros = ['cnes' => $cnesAtivo];
$totalcadastros = contarCadastros($filtros);
$totalcadastrodesatualizado = contarCadastrosDesatualizados($filtros);
$dadosVisitas = listarVisitasACS($ano, $mes, $filtros);

$totalVisitas = 0;
foreach ($dadosVisitas as $row) {
    $totalVisitas += (int)$row['sem1'] + (int)$row['sem2'] + (int)$row['sem3'] + (int)$row['sem4'];
}
$percentualVisitas = $totalcadastros > 0 ? ($totalVisitas * 100) / $totalcadastros : 0;
$metaVisita = ($totalcadastros * 70) / 100;

// --- 2. BUSCA DE ATENDIMENTOS (MÉDICO, ENFERMAGEM, ODONTO) ---
$totalMedico = 0; $totalEnfermagem = 0; $totalAtendimentosOdonto = 0;

$filtros['tipo'] = "odonto"; $filtros['cboLista'] = "6888";
foreach (listarAtendimentos($ano, $mes, $filtros) as $r) { $totalAtendimentosOdonto += (int)$r['sem1'] + (int)$r['sem2'] + (int)$r['sem3'] + (int)$r['sem4']; }

$filtros['tipo'] = "individual"; $filtros['cboLista'] = ["6879", "6904"];
foreach (listarAtendimentos($ano, $mes, $filtros) as $r) { $totalMedico += (int)$r['sem1'] + (int)$r['sem2'] + (int)$r['sem3'] + (int)$r['sem4']; }

$filtros['tipo'] = "individual"; $filtros['cboLista'] = "6890";
foreach (listarAtendimentos($ano, $mes, $filtros) as $r) { $totalEnfermagem += (int)$r['sem1'] + (int)$r['sem2'] + (int)$r['sem3'] + (int)$r['sem4']; }

// --- 3. INTERDIÇÕES (GOOGLE SHEETS) ---
$sheetInterdicoes = '1q1pMYDn_KOWtur-zGL4VuvIsCkL6MnBMujUEOPkIyLw';
$cnesFormatado = array_flip([ltrim((string)$cnesAtivo, '0')]);
$dadosInterdicoes = lerGoogleSheetTratado($sheetInterdicoes, 'Interdições', $cnesFormatado);

// --- 4. PLANILHA FALE COM SUA EQUIPE ---
$sheetDemandas = "19jNMkSYFpHhyEJQJPxa3MijpVwUOZwoyBvyYhNnzdLE";
$urlDemandas = "https://opensheet.elk.sh/{$sheetDemandas}/Demandas";
$jsonDemandas = @file_get_contents($urlDemandas);
$demandasBrutas = $jsonDemandas ? json_decode($jsonDemandas, true) : [];
$totalDemandas = 0; $atendidasDemandas = 0; $pendentesDemandas = 0;

foreach (($demandasBrutas ?: []) as $item) {
    if (($item['cnes'] ?? '') == $cnesAtivo) {
        $totalDemandas++;
        if (normalizar($item['situação'] ?? '') === 'atendido') { $atendidasDemandas++; } else { $pendentesDemandas++; }
    }
}
$pendentesDemandas = $totalDemandas - $atendidasDemandas;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Monitoramento Público - Distrito <?= $distrito_id ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@bootstrap-themes/adminlte-blueprint@4.0.0-alpha3/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .tv-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 15px; border-bottom: 5px solid #28a745; }
        .card-kpi { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .tv-badge { font-size: 16px; padding: 10px 20px; border-radius: 30px; animation: pulseBorder 2s infinite; }
        @keyframes pulseBorder { 0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); } }
        .fs-7 { font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="tv-header shadow-sm mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-0 fw-bold"><i class="bi bi-display me-2"></i>Monitoramento de Saúde Centralizado</h2>
                <span class="badge bg-white text-primary mt-1">DISTRITO SANITÁRIO: <?= $distrito_id ?></span>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                <span class="tv-badge badge bg-success shadow-sm me-3">
                    <i class="bi bi-arrow-repeat me-2"></i>Próxima Unidade em: <strong id="countdown">30</strong>s
                </span>
                <span class="fs-4 fw-bold bg-white text-dark px-4 py-2 rounded shadow border">
                    CNES ATIVO: <span class="text-primary"><?= $cnesAtivo ?></span>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <div class="small-box bg-primary text-white card-kpi p-3 h-100">
                <div class="inner">
                    <h3 class="fw-bold"><?= $totalcadastros ?></h3>
                    <p class="mb-1">Cidadãos Vinculados</p>
                    <small class="opacity-75">Não Atualizados: <?= $totalcadastrodesatualizado ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="small-box bg-success text-white card-kpi p-3 h-100">
                <div class="inner">
                    <h3 class="fw-bold"><?= $totalVisitas ?></h3>
                    <p class="mb-1">Visitas de ACS</p>
                    <small class="opacity-75">Meta Cobertura: 70% (<?= (int)$metaVisita ?>)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="small-box bg-info text-white card-kpi p-3 h-100">
                <div class="inner">
                    <h3 class="fw-bold"><?= $totalMedico ?></h3>
                    <p class="mb-1">Atendimentos Médicos</p>
                    <small class="opacity-75">Mês de Referência</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="small-box bg-warning text-dark card-kpi p-3 h-100">
                <div class="inner">
                    <h3 class="fw-bold"><?= $totalEnfermagem ?></h3>
                    <p class="mb-1">Atend. Enfermagem</p>
                    <small class="opacity-75">Mês de Referência</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="small-box text-white card-kpi p-3 h-100" style="background-color: #20c997 !important;">
                <div class="inner">
                    <h3 class="fw-bold"><?= $totalAtendimentosOdonto ?></h3>
                    <p class="mb-1">Atend. Odontológicos</p>
                    <small class="opacity-75">Mês de Referência</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="small-box bg-danger text-white card-kpi p-3 h-100">
                <div class="inner">
                    <h3 class="fw-bold"><?= count($dadosInterdicoes) ?></h3>
                    <p class="mb-1">Interdições Prediais</p>
                    <small class="opacity-75">Alertas Ativos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-secondary mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i>Produção Mensal Consolidada (ACS)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Cobertura Geral de Visitas</small>
                            <strong class="fs-4 text-success"><?= number_format($percentualVisitas, 2, ',', '.') ?>%</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Demandas Resolvidas (WhatsApp)</small>
                            <strong class="fs-4 text-primary"><?= $totalDemandas > 0 ? number_format(($atendidasDemandas/$totalDemandas)*100, 1) : 100 ?>%</strong>
                        </div>
                    </div>
                    <div id="visitas-chart-tv" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-secondary mb-0"><i class="bi bi-layers-half me-2"></i>Status Operacional Integrado</h5>
                </div>
                <div class="card-body">
                    <div class="p-3 mb-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-dark">Demandas do Canal Digital:</span>
                            <span class="badge bg-dark"><?= $totalDemandas ?></span>
                        </div>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?= $totalDemandas > 0 ? ($atendidasDemandas/$totalDemandas)*100 : 0 ?>%"></div>
                            <div class="progress-bar bg-danger" style="width: <?= $totalDemandas > 0 ? ($pendentesDemandas/$totalDemandas)*100 : 0 ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between fs-7 text-muted">
                            <span>Concluídas: <?= $atendidasDemandas ?></span>
                            <span>Pendentes: <?= $pendentesDemandas ?></span>
                        </div>
                    </div>

                    <?php if(!empty($dadosInterdicoes)): ?>
                        <div class="alert alert-danger border-0 shadow-sm p-3 mb-0">
                            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Atenção: Restrições de Estrutura</h6>
                            <ul class="mb-0 fs-7 ps-3 mt-1">
                                <?php foreach($dadosInterdicoes as $int): ?>
                                    <li><strong><?= htmlspecialchars($int['Procedimento'] ?? 'Geral') ?></strong>: <span class="badge bg-dark"><?= htmlspecialchars($int['Situação']) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success border-0 p-3 mb-0 text-center">
                            <i class="bi bi-check-circle-fill me-1"></i> Nenhuma interdição registrada nesta unidade.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-secondary mb-0"><i class="bi bi-people-fill me-2"></i>Performance de Visitas por Microárea (ACS)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4">Profissional ACS</th>
                                    <th>Microárea</th>
                                    <th>Semana 1</th>
                                    <th>Semana 2</th>
                                    <th>Semana 3</th>
                                    <th>Semana 4</th>
                                    <th class="pe-4">Total Produção</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($dadosVisitas)): ?>
                                    <tr><td colspan="7" class="text-muted p-4">Nenhum registro de produção de ACS localizado.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($dadosVisitas as $row): 
                                        $s1 = (int)$row['sem1']; $s2 = (int)$row['sem2']; $s3 = (int)$row['sem3']; $s4 = (int)$row['sem4'];
                                        $totalLinha = $s1 + $s2 + $s3 + $s4;
                                    ?>
                                        <tr>
                                            <td class="text-start ps-4 fw-bold text-dark"><?= htmlspecialchars($row['no_profissional'] ?? 'Não Informado') ?></td>
                                            <td><span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($row['nu_micro_area'] ?? '-') ?></span></td>
                                            <td><?= $s1 ?></td>
                                            <td><?= $s2 ?></td>
                                            <td><?= $s3 ?></td>
                                            <td><?= $s4 ?></td>
                                            <td class="fw-bold text-primary pe-4"><?= $totalLinha ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script>
    const arrayCnesTV = <?php echo json_encode(array_values($listaCnesCompleta)); ?>;
    let cnesAtivoAgora = "<?php echo $cnesAtivo; ?>";
    let indiceLista = arrayCnesTV.indexOf(cnesAtivoAgora);
    
    const TEMPO_ROTACAO = 30;
    let tempoRestante = TEMPO_ROTACAO;

    const contadorTela = document.getElementById('countdown');
    setInterval(() => {
        tempoRestante--;
        if (contadorTela) contadorTela.innerText = tempoRestante;
        
        if (tempoRestante <= 0) {
            avancarProximaUnidade();
        }
    }, 1000);

    function avancarProximaUnidade() {
        if (arrayCnesTV.length <= 1) {
            tempoRestante = TEMPO_ROTACAO;
            return;
        }
        
        indiceLista = (indiceLista + 1) % arrayCnesTV.length;
        const proximoCnes = arrayCnesTV[indiceLista];
        
        const parametrosURL = new URLSearchParams(window.location.search);
        parametrosURL.set('distrito_id', '<?= $distrito_id ?>');
        parametrosURL.set('cnes', proximoCnes);
        parametrosURL.set('ano', '<?= $ano ?>');
        parametrosURL.set('mes', '<?= $mes ?>');
        
        window.location.search = parametrosURL.toString();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const opcoesGraficoTV = {
            series: [{
                name: 'Total de Visitas no Mês',
                data: [<?php 
                    $valoresGrafico = [];
                    foreach($dadosVisitas as $v) { $valoresGrafico[] = (int)$v['sem1'] + (int)$v['sem2'] + (int)$v['sem3'] + (int)$v['sem4']; }
                    echo implode(',', $valoresGrafico ?: [0,0,0,0]);
                ?>]
            }],
            chart: { type: 'bar', height: 260, toolbar: { show: false }, animations: { enabled: true } },
            plotOptions: { bar: { borderRadius: 5, horizontal: false, columnWidth: '45%' } },
            dataLabels: { enabled: true, style: { colors: ['#fff'] } },
            colors: ['#28a745'],
            xaxis: {
                categories: <?php 
                    $labelsAcs = [];
                    foreach($dadosVisitas as $v) { $labelsAcs[] = mb_strimwidth($v['no_profissional'], 0, 14, '...'); }
                    echo json_encode($labelsAcs ?: ['Sem registros']);
                ?>
            }
        };

        const chartPublico = new ApexCharts(document.querySelector("#visitas-chart-tv"), opcoesGraficoTV);
        chartPublico.render();
    });
</script>
</body>
</html>