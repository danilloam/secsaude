<?php

$page = 6;

require_once __DIR__ . '/../CORE/config.php';
require_once __DIR__ . '/../security/session_guard.php';
require_once __DIR__ . '/../helpers/func.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

// 🔧 CONFIG
$sheet = "1q1pMYDn_KOWtur-zGL4VuvIsCkL6MnBMujUEOPkIyLw";
$aba   = "BD_Agenda_Configurada";
$distritoFiltro = $_SESSION['user']['distrito_id'];

// 🔗 URL
$url = "https://opensheet.elk.sh/{$sheet}/" . urlencode($aba);

// 📥 BUSCA
$json = @file_get_contents($url);
$dados = json_decode($json, true);

$agrupado = [];

foreach ($dados as $item) {

    if ($item['Distrito'] != $distritoFiltro) continue;
    if (empty($item['esp_descricao'])) continue;

    $cnes   = $item['cnes'];

    // 🔥 NORMALIZA ESPECIALIDADE (evita duplicidade)
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
$estiloextra="
<style>
	.ok { color:green; font-weight:bold; }
.nok { color:red; font-weight:bold; }
.filtros { background:#fff;padding:10px;margin-bottom:15px;border-radius:8px;}
	</style>
";
?>

<?php include "./includes/top.php"; ?>

      <!--end::Header-->
      <!--begin::Sidebar-->
     <?php include "./includes/menu.php"; ?>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">Gestão de Agenda - Conecta Recife</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Gestão Agenda</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
		

	
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
	<div "row">
	<?php foreach ($agrupado as $cnes => $unidade): ?>
			<div class="row">
			 <div class="col-12">
                <div class="card mb-4">
				<div class="card card-outline card-primary ">
                  <div class="card-header border-0">
                    <div class="card-header">
            
                    <h5 class="card-title mb-0">CNES: <?= $cnes ?> - <?= $unidade['info']['nome_popular'] ?></h5>
                   
					<div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                      </button>
                    </div>
                </div>
				</div>
                <div class="card-body">

                    <?php foreach ($unidade['equipes'] as $equipe): ?>
<div class="card mb-4">
				<div class="card card-outline card-primary collapsed-card">
					<div class="card-header border-0">
                    <div class="card-header">
            
                    <h5 class="card-title mb-0">👥 <?= $equipe['equipe_nome'] ?>
                            <span class="badge bg-secondary">
                                INE: <?= $equipe['equipe_ine'] ?>
                            </span></h5>
                   
					<div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                      </button>
                    </div>
                </div>
				</div>
                    <div class="card-body">

                        <h6 class="mb-3">
                            
                        </h6>

                        <?php foreach ($equipe['especialidades'] as $esp => $lista): ?>

                        <?php 
						$totalVagas = 0;

						foreach ($lista as $itemLista) {
							$totalVagas += (int)$itemLista['vagas'];
						}

													if ($totalVagas == 4) {
							$statusRegra = '<span class="badge bg-success">Padrão OK (4 vagas)</span>';
							$classeBox = '';
						} elseif ($totalVagas > 4) {
							$statusRegra = '<span class="badge bg-warning text-dark">Acima do padrão (' . $totalVagas . ' vagas)</span>';
							$classeBox = 'border border-warning';
						} else {
							$statusRegra = '<span class="badge bg-danger">Abaixo do padrão (' . $totalVagas . ' vagas)</span>';
							$classeBox = 'border border-danger';
						}
                        ?>

                        <div class="mb-3 p-2 rounded <?= $classeBox ?>">

                            <h6 class="text-primary d-flex justify-content-between align-items-center">
                                <span>🩺 <?= $esp ?></span>
                                <?= $statusRegra ?>
                            </h6>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">

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

                                        <td>
                                            <?= $r['inicio'] ?> - <?= $r['fim'] ?>
                                        </td>

                                        <td><?= $r['tempo'] ?> min</td>

                                        <td><?= $r['minutos'] ?></td>

                                        <td>
                                            <span class="badge <?= $r['vagas'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $r['vagas'] ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= $r['status'] ?: '<span class="badge bg-success">OK</span>' ?>
                                        </td>
                                    </tr>

                                    <?php endforeach; ?>

                                    </tbody>

                                </table>
                            </div>

                        </div>

                        <?php endforeach; ?>

                    </div>
</div></div>
                    <?php endforeach; ?>

                </div>

            </div>
			</div>
			</div>
			</div>
            <?php endforeach; ?>
	
	</div>
			 

            <!--end::Row-->
			
			</div>
	 
              
			
			</div>
			
            <!--begin::Row-->
            
  </div>

</div>
              <!-- /.Start col -->
            </div>
            <!-- /.row (main row) -->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Produzido por Danillo Almeida Marques</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2026-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">Coordena&ccedil;&atilde;o de Sa&uacute;de Digital</a>.
        </strong>
        Todos os Direitos Reservados.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    
    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>

    <!-- sortablejs -->
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
	<script>
function exportarPDF() {
    window.print();
}
		
    <!-- ChartJS -->

   

    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>

    <!-- jsvectormap -->
   
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
