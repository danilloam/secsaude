<?php
require 'vendor/autoload.php';
require 'config.php';

use Dompdf\Dompdf;

$pdo = pgConnect();

// ================= PARÂMETROS
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$unidade = $_GET['unidade'] ?? '';
$equipe = $_GET['equipe'] ?? '';
$acao = $_GET['acao'] ?? ''; // pdf | lote

// ================= FUNÇÃO MES
function nomeMes($mes) {
    return strtoupper([
        1=>'JANEIRO',2=>'FEVEREIRO',3=>'MARÇO',4=>'ABRIL',
        5=>'MAIO',6=>'JUNHO',7=>'JULHO',8=>'AGOSTO',
        9=>'SETEMBRO',10=>'OUTUBRO',11=>'NOVEMBRO',12=>'DEZEMBRO'
    ][(int)$mes]);
}

// ================= BUSCA SERVIDORES
$sql = "SELECT * FROM servidores WHERE ativo = true";
$params = [];

if ($unidade) {
    $sql .= " AND lotacao = :unidade";
    $params[':unidade'] = $unidade;
}

if ($equipe) {
    $sql .= " AND equipe = :equipe";
    $params[':equipe'] = $equipe;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$servidores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= FUNÇÃO HTML
function gerarHTML($s, $mes, $ano) {

    ob_start();
    ?>

<style>
@page { margin: 10mm; }
body { font-family: Arial; font-size: 11px; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #000; padding: 2px; height: 6mm; }
.sem-borda td { border: none; }
.titulo { text-align:center; font-weight:bold; font-size:14px; }
</style>

<div class="titulo">
PREFEITURA DO RECIFE<br>
SECRETARIA DE SAÚDE<br>
DISTRITO SANITÁRIO I<br><br>
FOLHA DE PONTO<br>
MÊS: <?= nomeMes($mes) ?> / <?= $ano ?>
</div>

<br>

<table class="sem-borda">
<tr>
<td><b>Matrícula:</b> <?= $s['matricula'] ?></td>
<td><b>Servidor:</b> <?= $s['nome'] ?></td>
<td><b>Carga Horária:</b> <?= $s['carga_horaria'] ?></td>
</tr>

<tr>
<td><b>Cargo:</b> <?= $s['cargo'] ?></td>
<td><b>Vínculo:</b> <?= $s['vinculo'] ?></td>
<td></td>
</tr>

<tr>
<td><b>Lotação:</b> <?= $s['lotacao'] ?></td>
<td><b>Equipe:</b> <?= $s['equipe'] ?></td>
<td></td>
</tr>

<tr>
<td colspan="3"><b>Chefia:</b> <?= $s['chefia'] ?></td>
</tr>
</table>

<br>

<table>
<tr>
<th>DIA</th>
<th>ENTRADA</th>
<th>INTERVALO</th>
<th>SAÍDA</th>
<th>ASSINATURA</th>
<th>OCORRÊNCIAS</th>
</tr>

<?php
$diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

for ($i=1;$i<=31;$i++) {

    if ($i > $diasMes) {
        echo "<tr><td>$i</td><td colspan='5'></td></tr>";
        continue;
    }

    $data = "$ano-$mes-$i";
    $semana = date('w', strtotime($data));

    $obs = '';
    if ($semana == 0) $obs = 'DOMINGO';
    if ($semana == 6) $obs = 'SÁBADO';
    if ($i == 1 && $mes == 5) $obs = 'FERIADO - DIA DO TRABALHADOR';

    echo "<tr>
        <td>$i</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>$obs</td>
    </tr>";
}
?>

</table>

<br><br>

<table class="sem-borda">
<tr>
<td style="text-align:center;">_________________________<br>Servidor</td>
<td style="text-align:center;">_________________________<br>Chefia</td>
</tr>
</table>

<?php
    return ob_get_clean();
}

// ================= AÇÃO PDF INDIVIDUAL
if ($acao == 'pdf' && isset($_GET['id'])) {

    foreach ($servidores as $s) {
        if ($s['id'] == $_GET['id']) {

            $html = gerarHTML($s, $mes, $ano);

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();

            $dompdf->stream("folha.pdf", ["Attachment"=>false]);
            exit;
        }
    }
}

// ================= AÇÃO LOTE
if ($acao == 'lote') {

    ini_set('memory_limit','512M');

    $zip = new ZipArchive();
    $zipFile = "folhas.zip";

    $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($servidores as $s) {

        $html = gerarHTML($s, $mes, $ano);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $pdf = $dompdf->output();

        $nome = preg_replace('/[^A-Za-z0-9]/','_', $s['nome']);
        $arquivo = "temp_$nome.pdf";

        file_put_contents($arquivo, $pdf);
        $zip->addFile($arquivo, "$nome.pdf");
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=folhas.zip');
    readfile($zipFile);
    exit;
}
?>

<!-- ================= TELA ================= -->

<form method="GET">

<select name="unidade">
<option value="">Todas Unidades</option>
<?php
$u = $pdo->query("SELECT DISTINCT lotacao FROM servidores")->fetchAll();
foreach ($u as $x) {
    echo "<option value='{$x['lotacao']}'>" . $x['lotacao'] . "</option>";
}
?>
</select>

<select name="equipe">
<option value="">Todas Equipes</option>
<option>I</option>
<option>II</option>
<option>III</option>
<option>IV</option>
</select>

<input type="number" name="mes" value="<?= $mes ?>">
<input type="number" name="ano" value="<?= $ano ?>">

<button>Filtrar</button>

<a href="?acao=lote&mes=<?= $mes ?>&ano=<?= $ano ?>&unidade=<?= $unidade ?>&equipe=<?= $equipe ?>">
    GERAR TODAS
</a>

</form>

<hr>

<?php foreach ($servidores as $s): ?>
<div style="border:1px solid #ccc; margin:10px; padding:10px;">
    <?= $s['nome'] ?> - <?= $s['lotacao'] ?>

    <a href="?acao=pdf&id=<?= $s['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>">
        Gerar PDF
    </a>
</div>
<?php endforeach; ?>