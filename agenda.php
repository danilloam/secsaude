<?php

$page = 6;

require_once __DIR__ . '/CORE/config.php';
require_once __DIR__ . '/security/session_guard.php';
require_once __DIR__ . '/func.php';

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
?>




<html lang="pt-BR">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sistema de Distrito - </title>

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./dist/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />

    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
	  <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<style>
	.ok { color:green; font-weight:bold; }
.nok { color:red; font-weight:bold; }
.filtros { background:#fff;padding:10px;margin-bottom:15px;border-radius:8px;}
	</style>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Contact</a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Navbar Search-->
            <!-- <li class="nav-item">
              <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="bi bi-search"></i>
              </a>
            </li> -->
            <!--end::Navbar Search-->

            <!--begin::Messages Dropdown Menu-->
          <!--  <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-chat-text"></i>
                <span class="navbar-badge badge text-bg-danger">3</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <a href="#" class="dropdown-item">
                  
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./dist/assets/img/user1-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        Brad Diesel
                        <span class="float-end fs-7 text-danger"
                          ><i class="bi bi-star-fill"></i
                        ></span>
                      </h3>
                      <p class="fs-7">Call me whenever you can...</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
               
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./dist/assets/img/user8-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        John Pierce
                        <span class="float-end fs-7 text-secondary">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </h3>
                      <p class="fs-7">I got your message bro</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
           
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./dist/assets/img/user3-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        Nora Silvester
                        <span class="float-end fs-7 text-warning">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </h3>
                      <p class="fs-7">The subject goes here</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
               
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
              </div>
            </li> -->
            <!--end::Messages Dropdown Menu-->

            <!--begin::Notifications Dropdown Menu-->
           <!-- <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell-fill"></i>
                <span class="navbar-badge badge text-bg-warning">15</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-envelope me-2"></i> 4 new messages
                  <span class="float-end text-secondary fs-7">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-people-fill me-2"></i> 8 friend requests
                  <span class="float-end text-secondary fs-7">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                  <span class="float-end text-secondary fs-7">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
              </div>
            </li> -->
            <!--end::Notifications Dropdown Menu-->

            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="./dist/assets/img/user2-160x160.jpg"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline"><?php echo nomeSobrenome($_SESSION['user']['nome']." ".$_SESSION['user']['sobrenome']); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="./dist/assets/img/user2-160x160.jpg"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    <?php echo mb_strimwidth($_SESSION['user']['nome']." ".$_SESSION['user']['sobrenome'], 0, 32, '', 'UTF-8'); ?>
                    <small><?php echo dataCriacao($_SESSION['user']['created_at']);?></small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
               
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-outline-secondary">Perfil</a>
                  <a href="logout.php" class="btn btn-outline-danger float-end">Sair</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
     <?php include "menu.php"; ?>
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
