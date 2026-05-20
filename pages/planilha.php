
<?php

$sheet = '1q1pMYDn_KOWtur-zGL4VuvIsCkL6MnBMujUEOPkIyLw';
$aba   = 'Interdições';

$dados = lerGoogleSheetTratado($sheet, $aba);

$resumo = [
    'total' => count($dados),
    'reforma' => 0,
    'sem_pec' => 0,
    'interditada' => 0
];

foreach ($dados as $item) {

    $situacao = $item['Situação'] ?? '';
    $obs      = $item['Observação'] ?? '';

    if (str_contains($situacao, 'Reforma')) {
        $resumo['reforma']++;
    }

    if (str_contains($situacao, 'PEC')) {
        $resumo['sem_pec']++;
    }

    if (
        str_contains($situacao, 'Interditada') ||
        str_contains($obs, 'Interditada')
    ) {
        $resumo['interditada']++;
    }
}

/*
|--------------------------------------------------------------------------
| FUNÇÕES
|--------------------------------------------------------------------------
*/

function lerGoogleSheetTratado($sheetId, $aba = 'Aba1')
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

            // corrige coluna undefined
            if ($chave === '' || $chave === 'undefined') {
                $chave = 'Observação';
            }

            if ($valor === '' || $valor === null) {
                $valor = null;
            } elseif (is_numeric($valor)) {
                $valor = $valor + 0;
            }

            $nova[$chave] = $valor;
        }

        $saida[] = $nova;
    }

    return $saida;
}

function badgeSituacao($situacao)
{
    if (str_contains($situacao, 'Reforma')) {
        return 'warning';
    }

    if (str_contains($situacao, 'PEC')) {
        return 'info';
    }

    if (str_contains($situacao, 'Interditada')) {
        return 'danger';
    }

    return 'secondary';
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="utf-8">

    <title>Monitoramento Operacional</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

        <ul class="navbar-nav">

            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#">
                    ☰
                </a>
            </li>

        </ul>

    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">
                Monitoramento
            </span>
        </a>

    </aside>

    <div class="content-wrapper">

        <section class="content-header">

            <div class="container-fluid">

                <h1>
                    Dashboard Operacional
                </h1>

            </div>

        </section>

        <section class="content">

            <div class="container-fluid">

                <!-- CARDS -->

                <div class="row">

                    <div class="col-lg-4">

                        <div class="small-box bg-primary">

                            <div class="inner">
                                <h3><?= $resumo['total'] ?></h3>
                                <p>Total de Unidades</p>
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-4">

                        <div class="small-box bg-warning">

                            <div class="inner">
                                <h3><?= $resumo['reforma'] ?></h3>
                                <p>Em Reforma</p>
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-4">

                        <div class="small-box bg-danger">

                            <div class="inner">
                                <h3><?= $resumo['interditada'] ?></h3>
                                <p>Interditadas</p>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- GRÁFICO -->

                <div class="row">

                   

                </div>

                <!-- TABELA -->

                <div class="row">

                    <div class="col-12">

                        <div class="card">

                            <div class="card-header">

                                <h3 class="card-title">
                                    Monitoramento Detalhado
                                </h3>

                            </div>

                            <div class="card-body">

                                <table id="tabela"
                                       class="table table-bordered table-striped">

                                    <thead>

                                    <tr>
                                        <th>CNES</th>
                                        <th>Unidade</th>
                                        <th>Situação</th>
                                        <th>Procedimento</th>
                                        <th>Código</th>
                                        <th>Observação</th>
                                    </tr>

                                    </thead>

                                    <tbody>

                                    <?php foreach ($dados as $item): ?>

                                        <tr>

                                            <td>
                                                <?= $item['CNES'] ?? '-' ?>
                                            </td>

                                            <td>
                                                <?= $item['US'] ?? '-' ?>
                                            </td>

                                            <td>

                                                <span class="badge badge-<?= badgeSituacao($item['Situação'] ?? '') ?>">

                                                    <?= $item['Situação'] ?? '-' ?>

                                                </span>

                                            </td>

                                            <td>
                                                <?= $item['Procedimento'] ?? '-' ?>
                                            </td>

                                            <td>
                                                <?= $item['Cod. Procedimento'] ?? '-' ?>
                                            </td>

                                            <td>
                                                <?= $item['Observação'] ?? '-' ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>

    $(function () {

        $('#tabela').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            }
        });

    });

   

</script>

</body>
</html>
```
