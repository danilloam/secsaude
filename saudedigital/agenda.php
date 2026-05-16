<?php

// 🔧 CONFIG
$sheet = "1q1pMYDn_KOWtur-zGL4VuvIsCkL6MnBMujUEOPkIyLw";
$aba   = "BD_Agenda_Configurada";

// 🎯 FILTROS
$distritoFiltro = $_GET['distrito'] ?? '';
$unidadeFiltro  = $_GET['unidade'] ?? '';

// 🔗 URL
$url = "https://opensheet.elk.sh/{$sheet}/" . urlencode($aba);

// 📥 BUSCA
$json = @file_get_contents($url);
$dados = json_decode($json, true);

$agrupado = [];
$listaUnidades = [];

foreach ($dados as $item) {

   
   // 🔹 LISTA DE UNIDADES (respeitando filtro de distrito)
if (empty($distritoFiltro) || $item['Distrito'] == $distritoFiltro) {
    $listaUnidades[$item['cnes']] = $item['nome_popular'];
}

    // 🔍 FILTROS
    if (!empty($distritoFiltro) && $item['Distrito'] != $distritoFiltro) continue;
    if (!empty($unidadeFiltro) && $item['cnes'] != $unidadeFiltro) continue;
    if (empty($item['esp_descricao'])) continue;

    $cnes   = $item['cnes'];

    $esp = trim(mb_strtoupper($item['esp_descricao']));
    $equipe = $item['equipe_nome'] ?: 'SEM EQUIPE';

    if (!isset($agrupado[$cnes])) {
        $agrupado[$cnes] = [
            'info' => [
                'nome_oficial' => $item['nome_oficial'],
                'nome_popular' => $item['nome_popular'],
            ],
            'equipes' => []
        ];
    }

    if (!isset($agrupado[$cnes]['equipes'][$equipe])) {
        $agrupado[$cnes]['equipes'][$equipe] = [
            'equipe_ine' => $item['equipe_ine'],
            'equipe_nome' => $equipe,
            'especialidades' => []
        ];
    }

    if (!isset($agrupado[$cnes]['equipes'][$equipe]['especialidades'][$esp])) {
        $agrupado[$cnes]['equipes'][$equipe]['especialidades'][$esp] = [];
    }

    $agrupado[$cnes]['equipes'][$equipe]['especialidades'][$esp][] = [
        'dia' => $item['dia_sem_descricao'],
        'inicio' => $item['hora_inicio_turno'],
        'fim' => $item['hora_fim_turno'],
        'tempo' => (int)$item['Tempo Consulta'],
        'minutos' => (int)$item['Disponível em Minutos'],
        'vagas' => (int)$item['Qtd Atendimentos Disponíveis'],
        'status' => $item['Slot > Disponível']
    ];
}

// 🔽 Ordena unidades
asort($listaUnidades);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Agenda por Unidade</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand bg-primary navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">🏥 Gestão de Agenda</span>
        </div>
    </nav>

    <main class="app-main p-3">
        <div class="container-fluid">

            <h3 class="mb-3">📊 Agenda por Unidade</h3>

            <!-- 🔍 FILTRO -->
            <form method="GET" class="row g-2 mb-4">

                <div class="col-md-3">
                    <label>Distrito</label>
                    <select name="distrito" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= $distritoFiltro == $i ? 'selected' : '' ?>>
                                Distrito <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label>Unidade</label>
                    <select name="unidade" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($listaUnidades as $cnes => $nome): ?>
                            <option value="<?= $cnes ?>" <?= $unidadeFiltro == $cnes ? 'selected' : '' ?>>
                                <?= $nome ?> (<?= $cnes ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filtrar</button>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <a href="?" class="btn btn-secondary w-100">Limpar</a>
                </div>

            </form>

            <?php foreach ($agrupado as $cnes => $unidade): ?>

            <div class="card card-primary card-outline mb-4">

                <div class="card-header">
                    <h5 class="card-title mb-0">CNES: <?= $cnes ?> -  <?= $unidade['info']['nome_popular'] ?></h5>
                    
                </div>

                <div class="card-body">

                    <?php foreach ($unidade['equipes'] as $equipe): ?>

                    <div class="mb-4 p-3 border rounded bg-light">

                        <h6 class="mb-3">
                            👥 <?= $equipe['equipe_nome'] ?>
                            <span class="badge bg-secondary">
                                INE: <?= $equipe['equipe_ine'] ?>
                            </span>
                        </h6>

                        <?php foreach ($equipe['especialidades'] as $esp => $lista): ?>

                        <?php 
                        $totalVagas = array_sum(array_column($lista, 'vagas'));

                        if ($totalVagas == 4) {
                            $statusRegra = '<span class="badge bg-success">Padrão OK (4 vagas)</span>';
                            $classeBox = '';
                        } elseif ($totalVagas > 4) {
                            $statusRegra = '<span class="badge bg-warning text-dark">Acima do padrão (' . $totalVagas . ')</span>';
                            $classeBox = 'border border-warning';
                        } else {
                            $statusRegra = '<span class="badge bg-danger">Abaixo do padrão (' . $totalVagas . ')</span>';
                            $classeBox = 'border border-danger';
                        }
                        ?>

                        <div class="mb-3 p-2 rounded <?= $classeBox ?>">

                            <h6 class="text-primary d-flex justify-content-between">
                                <span>🩺 <?= $esp ?></span>
                                <?= $statusRegra ?>
                            </h6>

                            <table class="table table-sm table-bordered table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Dia</th>
                                        <th>Horário</th>
                                        <th>Tempo</th>
                                        <th>Minutos</th>
                                        <th>Vagas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                <?php foreach ($lista as $r): ?>
                                <tr>
                                    <td><?= $r['dia'] ?: '-' ?></td>
                                    <td><?= $r['inicio'] ?> - <?= $r['fim'] ?></td>
                                    <td><?= $r['tempo'] ?> min</td>
                                    <td><?= $r['minutos'] ?></td>
                                    <td>
                                        <span class="badge <?= $r['vagas'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $r['vagas'] ?>
                                        </span>
                                    </td>
                                    <td><?= $r['status'] ?: '<span class="badge bg-success">OK</span>' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                        </div>

                        <?php endforeach; ?>

                    </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <?php endforeach; ?>

        </div>
    </main>

</div>

</body>
</html>