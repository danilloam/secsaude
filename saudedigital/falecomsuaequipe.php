<?php 

$sheet = "19jNMkSYFpHhyEJQJPxa3MijpVwUOZwoyBvyYhNnzdLE";
$aba   = "Demandas";
$usFiltro = $_GET['us'] ?? '';
$url = "https://opensheet.elk.sh/{$sheet}/" . urlencode($aba);
$json = @file_get_contents($url);

if ($json === false) die("Erro ao acessar planilha");

$dados = json_decode($json, true);
if (!$dados) die("Erro JSON");

// ================= FILTROS
$dsFiltro = $_GET['ds'] ?? '';
$dataInicioFiltro = $_GET['data_inicio'] ?? '';
$dataFimFiltro = $_GET['data_fim'] ?? '';

// ================= FUNÇÃO DATA
function formatarData($data) {
    $d = explode(',', $data)[0] ?? '';
    return DateTime::createFromFormat('d/m/Y', trim($d));
}

// ================= PROCESSAMENTO
$agrupado = [];
$total = $atendido = $naoAtendido = 0;

foreach ($dados as $item) {

    $ds = $item['ds'] ?? '';
    $us = $item['us'] ?? '';
    $status = trim(mb_strtolower($item['situação'] ?? ''));

    // filtro DS
    if ($dsFiltro && $ds !== $dsFiltro) continue;
	// filtro Unidade
    if ($usFiltro && $us !== $usFiltro) continue;
	
    // filtro período
    $data = formatarData($item['dataInicio']);
    if ($dataInicioFiltro && $data && $data < new DateTime($dataInicioFiltro)) continue;
	if ($dataFimFiltro && $data && $data > new DateTime($dataFimFiltro)) continue;

    // contadores
    $total++;
    ($status === 'atendido') ? $atendido++ : $naoAtendido++;

    // converter tempo (virgula -> ponto)
    $tempo = floatval(str_replace(',', '.', $item['tempo_media_de_resposta'] ?? 0));

    if (!isset($agrupado[$ds][$us])) {
        $agrupado[$ds][$us] = [
            'dados' => [],
            'tempo_total' => 0,
            'qtd' => 0
        ];
    }

    $agrupado[$ds][$us]['dados'][] = $item;
    $agrupado[$ds][$us]['tempo_total'] += $tempo;
    $agrupado[$ds][$us]['qtd']++;
}
$listaUS = [];

foreach ($dados as $item) {
    $ds = $item['ds'] ?? '';
    $us = $item['us'] ?? '';

    // respeita filtro de DS
    if ($dsFiltro && $ds !== $dsFiltro) continue;

    $listaUS[] = $us;
}

$listaUS = array_unique($listaUS);
sort($listaUS);
$listaDS = array_unique(array_column($dados, 'ds'));
sort($listaDS);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Dashboard Atendimentos</title>

<style>
body { font-family: Arial; background:#f4f6f9; padding:20px; }

.card {
    display:inline-block;
    padding:15px;
    margin:5px;
    background:#fff;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    width:200px;
    text-align:center;
}

h2 { background:#2f4050;color:#fff;padding:10px; }
h3 { background:#1ab394;color:#fff;padding:8px; }

table { width:100%; border-collapse: collapse; background:#fff; margin-bottom:20px;}
th,td { border:1px solid #ddd; padding:6px; font-size:12px;}
th { background:#eee; }

.ok { color:green; font-weight:bold; }
.nok { color:red; font-weight:bold; }

.filtros { background:#fff;padding:10px;margin-bottom:15px;border-radius:8px;}
</style>

<script>
function exportarPDF() {
    window.print();
}
</script>

</head>
<body>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    
    <h1 style="margin:0;">📊 Dashboard de Atendimentos</h1>

    <a href="index.php" style="
        background:#6c757d;
        color:#fff;
        padding:8px 15px;
        text-decoration:none;
        border-radius:5px;
        font-weight:bold;
    ">
        ⬅ Voltar ao Menu
    </a>

</div>

<!-- FILTROS -->
<form class="filtros">
    DS:
    <select name="ds" onchange="this.form.us.value=''; this.form.submit()">
        <option value="">Todos</option>
        <?php foreach($listaDS as $ds): ?>
            <option value="<?= $ds ?>" <?= ($dsFiltro==$ds?'selected':'') ?>><?= $ds ?></option>
        <?php endforeach; ?>
    </select>
	US:
	<select name="us" onchange="this.form.submit()">
		<option value="">Todas</option>
		<?php foreach($listaUS as $us): ?>
			<option value="<?= $us ?>" <?= ($usFiltro==$us?'selected':'') ?>>
				<?= $us ?>
			</option>
		<?php endforeach; ?>
	</select>
    Data início:
    <input type="date" name="data_inicio" value="<?= $dataInicioFiltro ?>">

    Data fim:
    <input type="date" name="data_fim" value="<?= $dataFimFiltro ?>">

    <button type="submit">Filtrar</button>
    <button type="button" onclick="exportarPDF()">Exportar PDF</button>
</form>

<!-- CARDS -->
<div>
    <div class="card">
        <h3>Total</h3>
        <h2><?= $total ?></h2>
    </div>

    <div class="card">
        <h3>Atendidos</h3>
        <h2><?= $atendido ?></h2>
    </div>

    <div class="card">
        <h3>Não Atendidos</h3>
        <h2><?= $naoAtendido ?></h2>
    </div>
</div>

<hr>

<!-- TABELAS -->
<?php foreach ($agrupado as $ds => $unidades): ?>

    <h2><?= $ds ?></h2>

    <?php foreach ($unidades as $us => $info): 
        $media = $info['qtd'] ? ($info['tempo_total'] / $info['qtd']) : 0;
    ?>

        <h3><?= $us ?> | Média resposta: <?= number_format($media,2,',','.') ?> min</h3>

        <table>
            <tr>
                <th>Data</th>
                <th>Usuário</th>
                <th>Atendente</th>
                <th>Duração</th>
                <th>Tempo Resp.</th>
                <th>Status</th>
				<th>Chat</th>
            </tr>

            <?php foreach ($info['dados'] as $r): 
                $status = trim(mb_strtolower($r['situação']));
                $classe = ($status === 'atendido') ? 'ok' : 'nok';
            ?>
            <tr>
                <td><?= $r['dataInicio'] ?></td>
                <td><?= $r['whatsappName'] ?></td>
                <td><?= $r['atendente'] ?></td>
                <td><?= $r['duracao_do_chat'] ?></td>
                <td><?= $r['tempo_media_de_resposta'] ?></td>
                <td class="<?= $classe ?>"><?= $r['situação'] ?></td>
				<td>
				<a href="<?= $r['roomId'] ?>" target="_blank">🔗 Abrir</a>
				</td>
            </tr>
            <?php endforeach; ?>

        </table>

    <?php endforeach; ?>

<?php endforeach; ?>

</body>
</html>
