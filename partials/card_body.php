<?php
// evita erro caso variável não exista
$item = $item ?? [];
?>

<div class="card-body">

    <!-- INDICADORES PRINCIPAIS -->
    <div class="row text-center mb-3">

        <?php
        $indicadores = [
            'consulta_programada' => 'Programada',
            'consulta_agendada' => 'Agendada',
            'demanda_espontanea' => 'Demanda Espontânea',
            'urgencia' => 'Urgência',
            'visitaDomiciliar' => 'Visita Domiciliar',
            'escuta_inicial' => 'Escuta Inicial',
        ];

        foreach ($indicadores as $campo => $label): ?>
            <div class="col-6 mb-2">
                <div class="p-2 bg-light rounded">
                    <small><?= $label ?></small>
                    <h5 class="mb-0 text-dark"><?= $item[$campo] ?? 0 ?></h5>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- DIVISOR -->
    <hr>

    <!-- SEMANAS -->
    <small class="text-muted">Distribuição por semana</small>

    <div class="mt-2">

        <?php
        $cores = [
            'sem1' => '',
            'sem2' => 'bg-success',
            'sem3' => 'bg-warning',
            'sem4' => 'bg-danger',
            'sem5' => 'bg-dark',
        ];

        foreach ($cores as $sem => $cor):

            if (!isset($item[$sem])) continue;
        ?>

            <div class="d-flex justify-content-between">
                <span><?= strtoupper(str_replace('sem', 'Sem ', $sem)) ?></span>
                <strong><?= $item[$sem] ?></strong>
            </div>

            <div class="progress mb-2" style="height:6px;">
                <div class="progress-bar <?= $cor ?>" 
                     style="width: <?= $item[$sem] ?>%">
                </div>
            </div>

        <?php endforeach; ?>

    </div>

</div>