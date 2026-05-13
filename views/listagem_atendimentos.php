<?php


require_once __DIR__ . '/../CORE/bootstrap.php';
require_once '../func.php';


$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('m');

$cnes   = $_GET['cnes'] ?? null;
$equipe = $_GET['equipe'] ?? null;
$tipo   = $_GET['tipo'] ?? 'individual';
$cboLista = isset($_GET['cboLista']) ? explode(',', $_GET['cboLista']) : [];

$filtros = [];
if (!empty($cnes)) {
    $filtros['cnes'] = $cnes;
}

if (!empty($equipe)) {
    $filtros['equipe'] = $equipe;
}

// ========================================
// 🔵 NIVEL 1 - UNIDADE
// ========================================
if (empty($cnes)) {

    $filtros['tipo'] = "individual";
    $filtros['cboLista'] = ["6879", "6904", "6890"];

    $dadosFinal = consolidarAtendimentos($ano, $mes, "unidade", $filtros);

    foreach ($dadosFinal as $item): ?>

        <div class="col-md-6 col-lg-4 mb-4"
             onclick="abrirNivel('<?= $item['nu_cnes'] ?>', null, null, null)">

            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <strong><?= $item['no_unidade_saude'] ?></strong><br>
                    <small>CNES: <?= $item['nu_cnes'] ?></small>
                </div>

                <?php include '../partials/card_body.php'; ?>

            </div>
        </div>

    <?php endforeach;
}


// ========================================
// 🟢 NIVEL 2 - EQUIPE
// ========================================
else if (!empty($cnes) && empty($equipe)) {

    $filtros['tipo'] = "individual";
    $filtros['cboLista'] = ["6879", "6904", "6890"];

    $dadosFinal = consolidarAtendimentos($ano, $mes, "equipe", $filtros);

    // ODONTO (sem perder o anterior)
    $filtrosOdonto = $filtros;
    $filtrosOdonto['tipo'] = "odonto";
    $filtrosOdonto['cboLista'] = ["6888"];

    $dadosOdonto = consolidarAtendimentos($ano, $mes, "equipe", $filtrosOdonto);

    $dadosFinal = array_merge($dadosFinal, $dadosOdonto);

    foreach ($dadosFinal as $item): ?>

        <div class="col-md-6 col-lg-4 mb-4"
             onclick="abrirNivel(
                '<?= $item['nu_cnes'] ?>',
                '<?= $item['nu_ine'] ?>',
                '<?= $item['tipo'] ?? 'individual' ?>',
                '<?= is_array($item['cboLista']) ? implode(',', $item['cboLista']) : '' ?>'
             )">

            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <strong>Unidade: <?= $item['nu_cnes'] ?> - <?= $item['no_unidade_saude'] ?></strong><br>
                    <small>Equipe: <?= $item['no_equipe'] ?></small>
                </div>

                <?php include '../partials/card_body.php'; ?>

            </div>
        </div>

    <?php endforeach;
}


// ========================================
// 🔴 NIVEL 3 - PROFISSIONAL
// ========================================
else {

    $filtros['tipo'] = $tipo;
    $filtros['cboLista'] = $cboLista;

    $dadosFinal = consolidarAtendimentos($ano, $mes, "profissional", $filtros);

    foreach ($dadosFinal as $item): ?>

        <div class="col-md-6 col-lg-4 mb-4">

            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <strong>Unidade: <?= $item['nu_cnes'] ?> - <?= $item['no_unidade_saude'] ?></strong><br>
                    <small>Profissional: <?= $item['profissional'] ?></small>
                </div>

                <?php include '../partials/card_body.php'; ?>

            </div>
        </div>

    <?php endforeach;
}