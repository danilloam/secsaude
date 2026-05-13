<?php
require_once __DIR__ . '/../CORE/bootstrap.php';
include_once '../func.php';

$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('m');

$filtros = [];

// mesmos filtros que você já usa
if (!empty($_SESSION['user']['unidade_id'])) {
    $filtros['cnes'] = UnidadeporId($_SESSION['user']['unidade_id']);
} else {
    $filtros['cnes'] = listarUnidadesDistrito($_SESSION['user']['distrito_id']);
}

if (!empty($_GET['cnes'])) $filtros['cnes'] = $_GET['cnes'];
if (!empty($_GET['equipe'])) $filtros['equipe'] = $_GET['equipe'];
if (!empty($_GET['acs'])) $filtros['acs'] = $_GET['acs'];
if (!empty($_GET['microarea'])) $filtros['microarea'] = $_GET['microarea'];

// ================= TOTAIS

$totalcadastros = contarCadastros($filtros);
$metaVisita=($totalcadastros*70)/100;
// visitas
$dados = listarVisitasACS($ano, $mes, $filtros);
$totalVisitas = array_sum(array_map(function($row){
    return $row['sem1'] + $row['sem2'] + $row['sem3'] + $row['sem4'];
}, $dados));
$percentualVisitas = 0;

if ($totalcadastros > 0) {
    $percentualVisitas = ($totalVisitas * 100) / $totalcadastros;
}

// odonto
$fOdonto = $filtros;
$fOdonto['tipo'] = "odonto";
$fOdonto['cboLista'] = "6888";

$dadosOdonto = listarAtendimentos($ano, $mes, $fOdonto);
$totalOdonto = array_sum(array_map(fn($r) =>
    $r['sem1']+$r['sem2']+$r['sem3']+$r['sem4']
, $dadosOdonto));

// médico
$fMed = $filtros;
$fMed['tipo'] = "individual";
$fMed['cboLista'] = ["6879","6904"];

$dadosMed = listarAtendimentos($ano, $mes, $fMed);
$totalMed = array_sum(array_map(fn($r) =>
    $r['sem1']+$r['sem2']+$r['sem3']+$r['sem4']
, $dadosMed));

// enfermagem
$fEnf = $filtros;
$fEnf['tipo'] = "individual";
$fEnf['cboLista'] = "6890";

$dadosEnf = listarAtendimentos($ano, $mes, $fEnf);
$totalEnf = array_sum(array_map(fn($r) =>
    $r['sem1']+$r['sem2']+$r['sem3']+$r['sem4']
, $dadosEnf));

echo json_encode([
    "status" => "success",
    "data" => [
        "cadastros" => $totalcadastros,
        "visitas" => $totalVisitas,
        "medico" => $totalMed,
        "enfermagem" => $totalEnf,
        "odonto" => $totalOdonto,
		"percentual_visitas" => $percentualVisitas,
		"meta_visitas" =>$metaVisita
    ]
]);