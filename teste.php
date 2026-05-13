<?php 
require 'config.php';
require 'session_guard.php';
include 'func.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}
$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int)date('m');

$mesHoje = (int)date('m');
$anoHoje = (int)date('Y');

// impede acessar mês futuro no ano atual
if ($ano == $anoHoje && $mes > $mesHoje) {
    $mes = $mesHoje;
}

// Ajustar mês anterior e próximo
$mesAtual = $mes;
$anoAtual = $ano;

// mês anterior
$mesAnterior = $mesAtual - 1;
$anoAnterior = $anoAtual;

if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
}

// próximo mês
$mesProximo = $mesAtual + 1;
$anoProximo = $anoAtual;

if ($anoProximo > $anoHoje || ($anoProximo == $anoHoje && $mesProximo > $mesHoje)) {
    $mesProximo = $mesHoje;
    $anoProximo = $anoHoje;
}


$filtros = [];


// ================= CNES (1 ou mais)

   if (!empty($_SESSION['user']['unidade_id'])) {

	$filtros['cnes'] = UnidadeporId($_SESSION['user']['unidade_id']);

}else{
	$unidadesDistrito = listarUnidadesDistrito($_SESSION['user']['distrito_id']);
 $filtros['cnes'] = $unidadesDistrito;

}

		
if (!empty($_GET['cnes'])) {
    $filtros['cnes'] = $_GET['cnes'];
	
}

// ================= EQUIPE
if (!empty($_GET['equipe'])) {
    $filtros['equipe'] = $_GET['equipe'];
}

// ================= ACS
if (!empty($_GET['acs'])) {
    $filtros['acs'] = $_GET['acs'];
}

// ================= MICROÁREA
if (!empty($_GET['microarea'])) {
    $filtros['microarea'] = $_GET['microarea'];
}

$totalcadastros = contarCadastros($filtros);

$dados = listarVisitasACS($ano, $mes, $filtros);

$totalVisitas = 0;

foreach ($dados as $row) {
    $totalVisitas +=
        (int)$row['semana_01'] +
        (int)$row['semana_02'] +
        (int)$row['semana_03'] +
        (int)$row['semana_04'];
}
$percentualVisitas = 0;

if ($totalcadastros > 0) {
    $percentualVisitas = ($totalVisitas * 100) / $totalcadastros;
}



$totalAtendimentosOdonto = 0;

$filtros['tipo']="odonto";
$filtros['cboLista']="6888";
$dadosOdonto=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosOdonto as $rowOdonto) {
    $totalAtendimentosOdonto +=
        (int)$rowOdonto['semana_01'] +
        (int)$rowOdonto['semana_02'] +
        (int)$rowOdonto['semana_03'] +
        (int)$rowOdonto['semana_04'];
}
$filtros['tipo']="individual";
$filtros['cboLista']=["6879", "6904"];

$totalAtendimentosMedico= 0 ;
$dadosMedicos=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosMedicos as $rowMedicos) {
    $totalAtendimentosMedico +=
        (int)$rowMedicos['semana_01'] +
        (int)$rowMedicos['semana_02'] +
        (int)$rowMedicos['semana_03'] +
        (int)$rowMedicos['semana_04'];
}
$totalAtendimentosEnfermagem = 0;
$filtros['tipo']="individual";
$filtros['cboLista']="6890";
$dadosEnfermeiro=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosEnfermeiro as $rowEnfermeiro) {
    $totalAtendimentosEnfermagem +=
        (int)$rowEnfermeiro['semana_01'] +
        (int)$rowEnfermeiro['semana_02'] +
        (int)$rowEnfermeiro['semana_03'] +
        (int)$rowEnfermeiro['semana_04'];
}
var_dump($dadosOdonto);
$bloqueiaProximo = ($anoAtual == $anoHoje && $mesAtual >= $mesHoje);

$parametroEquipes=contarEquipes($filtros);
$percentualCadastro = 0;
if ($totalcadastros > 0 && $parametroEquipes >0) {
    $percentualCadastro = ($totalcadastros * 100) / $parametroEquipes;
}
$metaVisita=($totalcadastros*70)/100;
//var_dump(contarQuantidadeEquipes(['cnes' => '0001252']));
