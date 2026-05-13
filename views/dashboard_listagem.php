<?php
require_once __DIR__ . '/../CORE/bootstrap.php';
include_once '../func.php';

/* =========================
   PARÂMETROS
========================= */
$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('m');

$filtros = [];

/* =========================
   CONTEXTO DO USUÁRIO
========================= */
if (!empty($_SESSION['user']['unidade_id'])) {
    $filtros['cnes'] = UnidadeporId($_SESSION['user']['unidade_id']);
} else {
    $filtros['cnes'] = listarUnidadesDistrito($_SESSION['user']['distrito_id']);
}

/* =========================
   FILTROS EXTRAS
========================= */
if (!empty($_GET['cnes'])) $filtros['cnes'] = $_GET['cnes'];
if (!empty($_GET['equipe'])) $filtros['equipe'] = $_GET['equipe'];
if (!empty($_GET['acs'])) $filtros['acs'] = $_GET['acs'];
if (!empty($_GET['microarea'])) $filtros['microarea'] = $_GET['microarea'];

/* =========================
   HELPERS
========================= */


/* =========================
   RENDER LINHA
========================= */
function renderRow($i, $nome, $link, $info, $row) {

    $d = calcVisitas($row);

    ?>
    <tr onclick="window.location='<?= $link ?>';" style="cursor:pointer;">
        <td><?= $i ?>.</td>

        <td>
            <strong><?= htmlspecialchars($nome) ?></strong><br>
            <small><?= $info ?></small>
        </td>

        <td style="width:50%">
            <small>Visitas por semana</small>

						<?php
			$cores = ['bg-primary', 'bg-info', 'bg-warning', 'bg-success', 'bg-danger'];
			?>

			<div class="progress" style="height:12px; display:flex; overflow:hidden;">
				<?php foreach ($d as $key => $valor): ?>
    <?php
    if (preg_match('/^w(\d+)$/', $key, $match)) {
        $sem = (int)$match[1];
        $p = $d["p{$sem}"] ?? 0;
        $cor = $cores[$sem - 1] ?? 'bg-secondary';
    ?>
        <div class="progress-bar <?= $cor ?>" style="width:<?= $p ?>%">
            <?= $valor ?>
        </div>
    <?php } ?>
<?php endforeach; ?>
			</div>

            <small>Total: <strong><?= $d['total'] ?></strong></small>
        </td>

        <td class="text-left">
		<?php foreach ($d as $key => $valor): ?>
    <?php
    if (preg_match('/^w(\d+)$/', $key, $match)) {
        $sem = str_pad($match[1], 2, '0', STR_PAD_LEFT);
		 $cor = $cores[$sem - 1] ?? 'bg-secondary';
    ?>
        SEM <?= $sem ?> -
        <span class="badge text-<?= $cor ?>"><?= $valor ?></span><br>
    <?php } ?>
<?php endforeach; ?>
		
		
		
        </td>
    </tr>
    <?php
}

/* =========================
   CONTROLE DE VISÃO
========================= */
$cnesSet = empty($_GET['cnes']);
$equipeSet = !empty($_GET['cnes']) && empty($_GET['equipe']);
$acsSet = !empty($_GET['cnes']) && !empty($_GET['equipe']);
?>

<div class="col-12 connectedSortable">
<div class="card">
<div class="card-header">
<strong>
<?php
if ($cnesSet) echo "Listar Unidades";
elseif ($equipeSet) echo "Listar Equipes";
else echo "Cadastros x Visitas ACS";
?>
</strong>
</div>

<div class="card-body p-0">
<div class="table-responsive">

<table class="table table-sm align-middle mb-0">
<tbody>

<?php

/* =========================
   1. UNIDADES
========================= */
if ($cnesSet) {

    $dados = listarVisitasCnes($ano, $mes, $filtros['cnes'], 'unidade');

    $i = 1;
    foreach ($dados as $row) {

        $qtd = contarCadastros([
            'cnes' => explode(',', $row['nu_cnes'])
        ]);

        renderRow(
            $i++,
            $row['no_unidade_saude'],
            "?cnes={$row['nu_cnes']}",
            "Cadastros: <b>{$qtd} pessoas</b>",
            $row
        );
    }
}

/* =========================
   2. EQUIPES
========================= */
elseif ($equipeSet) {

    $dados = listarVisitasCnes($ano, $mes, $filtros['cnes'], 'equipe');

    $i = 1;
    foreach ($dados as $row) {

        $qtd = contarCadastros([
            'cnes' => explode(',', $row['nu_cnes']),
            'equipe' => $row['nu_ine'],
            'nomeequipe' => $row['no_equipe']
        ]);

        renderRow(
            $i++,
            $row['no_equipe'] ?? '-',
            "?cnes={$row['nu_cnes']}&equipe={$row['nu_ine']}",
            "Cadastros: <b>{$qtd} pessoas</b>",
            $row
        );
    }
}

/* =========================
   3. ACS / MICROÁREA
========================= */
else {

    $dados = listarVisitasACS($ano, $mes, $filtros);

    $i = 1;
    foreach ($dados as $row) {

        $qtd = contarCadastros([
            'cnes' => explode(',', $row['nu_cnes']),
            'equipe' => $row['nu_ine'],
            'microarea' => $row['nu_micro_area']
        ]);

        renderRow(
            $i++,
            $row['no_unidade_saude'],
            "?cnes={$row['nu_cnes']}&equipe={$row['nu_ine']}&microarea={$row['nu_micro_area']}",
            "Equipe: <b>{$row['no_equipe']}</b><br>
             ACS: <b>{$row['no_profissional']}</b><br>
             Microárea: <b>{$row['nu_micro_area']}</b> |
             QTD: <b>{$qtd}</b>",
            $row
        );
    }
}

?>

</tbody>
</table>

</div>
</div>
</div>
</div>