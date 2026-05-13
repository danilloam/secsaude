<?php
$page=2;
require_once __DIR__ . '/CORE/config.php';
require_once __DIR__ . '/security/session_guard.php';
require_once __DIR__ . '/func.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}
$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : date('m');

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
$totalcadastrodesatualizado = contarCadastrosDesatualizados($filtros);
$dados = listarVisitasACS($ano, $mes, $filtros);

$totalVisitas = 0;

foreach ($dados as $row) {
    $totalVisitas +=
        (int)$row['sem1'] +
        (int)$row['sem2'] +
        (int)$row['sem3'] +
        (int)$row['sem4'];
}
$totalAtendimentosOdonto = 0;

$filtros['tipo']="odonto";
$filtros['cboLista']="6888";
$dadosOdonto=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosOdonto as $rowOdonto) {
    $totalAtendimentosOdonto +=
        (int)$rowOdonto['sem1'] +
        (int)$rowOdonto['sem2'] +
        (int)$rowOdonto['sem3'] +
        (int)$rowOdonto['sem4'];
}
$filtros['tipo']="individual";
$filtros['cboLista']=["6879", "6904"];

$totalAtendimentosMedico= 0 ;
$dadosMedicos=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosMedicos as $rowMedicos) {
    $totalAtendimentosMedico +=
        (int)$rowMedicos['sem1'] +
        (int)$rowMedicos['sem2'] +
        (int)$rowMedicos['sem3'] +
        (int)$rowMedicos['sem4'];
}
$totalAtendimentosEnfermagem = 0;
$filtros['tipo']="individual";
$filtros['cboLista']="6890";
$dadosEnfermeiro=listarAtendimentos($ano, $mes,$filtros);
foreach ($dadosEnfermeiro as $rowEnfermeiro) {
    $totalAtendimentosEnfermagem +=
        (int)$rowEnfermeiro['sem1'] +
        (int)$rowEnfermeiro['sem2'] +
        (int)$rowEnfermeiro['sem3'] +
        (int)$rowEnfermeiro['sem4'];
}
$percentualVisitas = 0;

if ($totalcadastros > 0) {
    $percentualVisitas = ($totalVisitas * 100) / $totalcadastros;
}
$percentualdesatualizado = 0;
if ($totalcadastrodesatualizado > 0) {
    $percentualdesatualizado = ($totalcadastrodesatualizado * 100) / $totalcadastros;
}
$bloqueiaProximo = ($anoAtual == $anoHoje && $mesAtual >= $mesHoje);
$parametroEquipes=contarEquipes($filtros);
$percentualCadastro = 0;
if ($totalcadastros > 0 && $parametroEquipes >0) {
    $percentualCadastro = ($totalcadastros * 100) / $parametroEquipes;
}
$metaVisita=($totalcadastros*70)/100;
$dados = listarVisitasCnes($ano, $mes, $filtros['cnes'], 'unidade'); 
?>


<html lang="pt-BR">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sistema de Disitro - </title>

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
                <h3 class="mb-0">Dashboard</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
			<div class="row">
			<div class="d-flex justify-content-between align-items-center mb-3">
    
   <a href="#" 
   class="btn btn-outline-primary"
   onclick="mudarMes(mesAtual - 1, anoAtual)">
    ← Mês Anterior
</a>
<h4 id="tituloMes"><?php echo atualizarMeses($mesAtual)." / ".$anoAtual; ?></h4>
<a href="#" 
   class="btn btn-outline-primary <?php echo $bloqueiaProximo ? 'disabled' : ''; ?>"
   onclick="mudarMes(mesAtual + 1, anoAtual)">
    Próximo Mês →
</a>

</div>
			</div>
			 <div class="row">
      <div class="col-lg-4 col-4">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id="totalCadastros"><?php echo $totalcadastros . " (".number_format($percentualCadastro, 2, ',', '.') . "% )"; ?></h3>

                    <p>Cidad&atilde;os Vinculados - META: <?php echo $parametroEquipes; ?></p>
                  </div>
				  
				  <svg  class="small-box-icon"
                    fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M320 64C355.3 64 384 92.7 384 128C384 163.3 355.3 192 320 192C284.7 192 256 163.3 256 128C256 92.7 284.7 64 320 64zM416 376C416 401 403.3 423 384 435.9L384 528C384 554.5 362.5 576 336 576L304 576C277.5 576 256 554.5 256 528L256 435.9C236.7 423 224 401 224 376L224 336C224 283 267 240 320 240C373 240 416 283 416 336L416 376zM160 96C190.9 96 216 121.1 216 152C216 182.9 190.9 208 160 208C129.1 208 104 182.9 104 152C104 121.1 129.1 96 160 96zM176 336L176 368C176 400.5 188.1 430.1 208 452.7L208 528C208 529.2 208 530.5 208.1 531.7C199.6 539.3 188.4 544 176 544L144 544C117.5 544 96 522.5 96 496L96 439.4C76.9 428.4 64 407.7 64 384L64 352C64 299 107 256 160 256C172.7 256 184.8 258.5 195.9 262.9C183.3 284.3 176 309.3 176 336zM432 528L432 452.7C451.9 430.2 464 400.5 464 368L464 336C464 309.3 456.7 284.4 444.1 262.9C455.2 258.4 467.3 256 480 256C533 256 576 299 576 352L576 384C576 407.7 563.1 428.4 544 439.4L544 496C544 522.5 522.5 544 496 544L464 544C451.7 544 440.4 539.4 431.9 531.7C431.9 530.5 432 529.2 432 528zM480 96C510.9 96 536 121.1 536 152C536 182.9 510.9 208 480 208C449.1 208 424 182.9 424 152C424 121.1 449.1 96 480 96z"/></svg>
				  
                 
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Mais Informa&ccedil;&otilde;es<i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
			  <div class="col-lg-4 col-4">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3 id="totaldesatualizado"><?php echo $totalcadastrodesatualizado." (".number_format($percentualdesatualizado, 2, ',', '.') . '%)';?></h3>

                    <p id="porcentagemdesatualizados"> Cadastros Desatualizados</p>
			
               
                  </div>
				  
				  <svg  class="small-box-icon"
                    fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M320 64C355.3 64 384 92.7 384 128C384 163.3 355.3 192 320 192C284.7 192 256 163.3 256 128C256 92.7 284.7 64 320 64zM416 376C416 401 403.3 423 384 435.9L384 528C384 554.5 362.5 576 336 576L304 576C277.5 576 256 554.5 256 528L256 435.9C236.7 423 224 401 224 376L224 336C224 283 267 240 320 240C373 240 416 283 416 336L416 376zM160 96C190.9 96 216 121.1 216 152C216 182.9 190.9 208 160 208C129.1 208 104 182.9 104 152C104 121.1 129.1 96 160 96zM176 336L176 368C176 400.5 188.1 430.1 208 452.7L208 528C208 529.2 208 530.5 208.1 531.7C199.6 539.3 188.4 544 176 544L144 544C117.5 544 96 522.5 96 496L96 439.4C76.9 428.4 64 407.7 64 384L64 352C64 299 107 256 160 256C172.7 256 184.8 258.5 195.9 262.9C183.3 284.3 176 309.3 176 336zM432 528L432 452.7C451.9 430.2 464 400.5 464 368L464 336C464 309.3 456.7 284.4 444.1 262.9C455.2 258.4 467.3 256 480 256C533 256 576 299 576 352L576 384C576 407.7 563.1 428.4 544 439.4L544 496C544 522.5 522.5 544 496 544L464 544C451.7 544 440.4 539.4 431.9 531.7C431.9 530.5 432 529.2 432 528zM480 96C510.9 96 536 121.1 536 152C536 182.9 510.9 208 480 208C449.1 208 424 182.9 424 152C424 121.1 449.1 96 480 96z"/></svg>
				  
                 
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Mais Informa&ccedil;&otilde;es<i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
			  <div class="col-lg-4 col-4">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3 id="totalVisitas"><?php echo $totalVisitas." (".number_format($percentualVisitas, 2, ',', '.') ." %)"; ?></h3>

                    <p id="visitasRealizadas">Visitas Realizadas - META: 70% ( <?php echo (int)$metaVisita;?> )</p>
			
                  </div>
				  
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"
                    ></path>
                  </svg>
				  
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    More info <i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 2-->
              </div>
			  
</div>
            
            <!--end::Row-->
			
			</div>
			<div class="row">
			
			 <div class="col-12">
                <div class="card mb-4">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Histórico de Visitas dos últimos 12 meses</h3>
                      
                    </div>
                  </div>
                  <div class="card-body">
                    <!--<div class="d-flex">
                      <p class="d-flex flex-column">
                        <span class="fw-bold fs-5">$18,230.00</span>
                        <span>Sales Over Time</span>
                      </p>
                      <p class="ms-auto d-flex flex-column text-end">
                        <span class="text-success"> <i class="bi bi-arrow-up"></i> 33.1% </span>
                        <span class="text-secondary">Since Past Year</span>
                      </p>
                    </div> -->
                    <!-- /.d-flex -->

                    <div class="position-relative mb-4">
                      <div id="visitas-chart"></div>
                    </div>

                    
                  </div>
                </div>
                <!-- /.card -->

                
              </div>
			
			</div>
			
            <!--begin::Row-->
            <div class="row" id="lista-visitas">
              <!-- Start col -->
            
              <!-- /.Start col -->

              <!-- Start col -->
	<?php if(empty($_GET['cnes'])){ ?>
 <div class="col-12 connectedSortable">
                
<div class="card">
  <div class="card-header">
    <strong>Listar Unidades </strong>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <tbody>

<tr>


<?php 

$dados = listarVisitasCnes($ano, $mes, $filtros['cnes'], 'unidade'); 

 $i = 1; foreach ($dados as $row): 

$w1 = (int)$row['sem1'];
$w2 = (int)$row['sem2'];
$w3 = (int)$row['sem3'];
$w4 = (int)$row['sem4'];

$total = $w1 + $w2 + $w3 + $w4;
$total = $total > 0 ? $total : 1;

 $totalVisitas += 
        (int)$row['sem1'] +
        (int)$row['sem2'] +
        (int)$row['sem3'] +
        (int)$row['sem4'];


$p1 = ($w1 / $total) * 100;
$p2 = ($w2 / $total) * 100;
$p3 = ($w3 / $total) * 100;
$p4 = ($w4 / $total) * 100;

?>

<tr onclick="window.location='?cnes=<?php echo $row['nu_cnes']; ?>';" style="cursor: pointer;">
  <td><?= $i++; 
  
  ?>.</td>

  <td>
    <strong><?= htmlspecialchars($row['no_unidade_saude']) ?></strong><br>
  <small>
    </small>Cadastros Vinculados: <?php 
	$filtros1 = [];
	$filtros1['cnes'] = explode(',', $row['nu_cnes']);
	$qtdpormicro= contarCadastros($filtros1);
	echo  "<b>".$qtdpormicro. " pessoas</b>";
	?>
  </td>

  <td style="width:50%">
    <small>Visitas por Semana</small>

    <!-- BARRA 100% PROPORCIONAL -->
    <div class="progress" style="height: 12px; display: flex; border-radius: 6px; overflow: hidden;">

      <div class="progress-bar bg-primary" style="width: <?= $p1 ?>%"><?= $w1 ?></div>
      <div class="progress-bar bg-info"    style="width: <?= $p2 ?>%"><?= $w2 ?></div>
      <div class="progress-bar bg-warning" style="width: <?= $p3 ?>%"><?= $w3 ?></div>
      <div class="progress-bar bg-success" style="width: <?= $p4 ?>%"><?= $w4 ?></div>

    </div>

    <small>
      Total: <strong><?= $total ?></strong> visitas
    </small>
	<?php echo $qtdpormicro > 0
    ? number_format(($total / $qtdpormicro) * 100, 2) . '%'
    : '0%'; ?>
  </td>

  <td class="text-left">
    SEMANA 01 - <span class="badge text-bg-primary"><?= $w1 ?></span></br>
    SEMANA 02 - <span class="badge text-bg-info"><?= $w2 ?></span></br>
    SEMANA 03 - <span class="badge text-bg-warning"><?= $w3 ?></span></br>
    SEMANA 04 - <span class="badge text-bg-success"><?= $w4 ?></span></br>
  </td>
</tr>

<?php endforeach; ?>
 </tr>

        </tbody>
      </table>
    </div>
  </div>
</div>
			  </div>	

	<?php } else if (!empty($_GET['cnes']) && empty($_GET['equipe'])) {?>	


			   <div class="col-12 connectedSortable">
                
<div class="card">
  <div class="card-header">
    <strong> Listar Equipes</strong>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <tbody id="lista-visitas">

<tr>
<?php $dados1 = listarVisitasCnes($ano, $mes, $filtros['cnes'], 'equipe'); 

 $i = 1; foreach ($dados1 as $row1): 
$w1 = (int)$row1['sem1'];
$w2 = (int)$row1['sem2'];
$w3 = (int)$row1['sem3'];
$w4 = (int)$row1['sem4'];

$total = $w1 + $w2 + $w3 + $w4;
$total = $total > 0 ? $total : 1;

 $totalVisitas += 
        (int)$row1['sem1'] +
        (int)$row1['sem2'] +
        (int)$row1['sem3'] +
        (int)$row1['sem4'];


$p1 = ($w1 / $total) * 100;
$p2 = ($w2 / $total) * 100;
$p3 = ($w3 / $total) * 100;
$p4 = ($w4 / $total) * 100;

?>

<tr onclick="window.location='?cnes=<?php echo $row1['nu_cnes']."&equipe=".$row1['nu_ine']; ?>';" style="cursor: pointer;">
  <td><?= $i++; 
  
  ?>.</td>

  <td>
    <strong><?= htmlspecialchars($row1['no_equipe'] ?? '-') ?></strong><br>
  <small>
      
      <!-- ✅ MICROÁREA -->
     Cadastros Vinculados: 
    </small> <b><?php 
	$filtros1 = [];
	$filtros1['cnes'] = explode(',', $row1['nu_cnes']);
    $filtros1['equipe'] = $row1['nu_ine'];
	$filtros1['nomeequipe'] = $row1['no_equipe'];
	$qtdpormicro= contarCadastros($filtros1);
	echo $qtdpormicro;
	?> pessoas</b> 
  </td>

  <td style="width:50%">
    <small>Distribuição semanal</small>

    <!-- BARRA 100% PROPORCIONAL -->
    <div class="progress" style="height: 12px; display: flex; border-radius: 6px; overflow: hidden;">

      <div class="progress-bar bg-primary" style="width: <?= $p1 ?>%"><?= $w1 ?></div>
      <div class="progress-bar bg-info"    style="width: <?= $p2 ?>%"><?= $w2 ?></div>
      <div class="progress-bar bg-warning" style="width: <?= $p3 ?>%"><?= $w3 ?></div>
      <div class="progress-bar bg-success" style="width: <?= $p4 ?>%"><?= $w4 ?></div>

    </div>

    <small>
      Total: <strong><?= $total ?></strong> visitas
    </small>
	<?php echo $qtdpormicro > 0
    ? number_format(($total / $qtdpormicro) * 100, 2) . '%'
    : '0%'; ?>
  </td>

  <td class="text-left">
    SEMANA 01 - <span class="badge text-bg-primary"><?= $w1 ?></span></br>
    SEMANA 02 - <span class="badge text-bg-info"><?= $w2 ?></span></br>
    SEMANA 03 - <span class="badge text-bg-warning"><?= $w3 ?></span></br>
    SEMANA 04 - <span class="badge text-bg-success"><?= $w4 ?></span></br>
  </td>
</tr>

<?php endforeach; ?>
 </tr>

        </tbody>
      </table>
	  
    </div>
  </div>
  
</div>
	</div>
              <?php } else{ ?>
			  

			   <div class="col-12 connectedSortable">
                
<div class="card">
  <div class="card-header">
    <strong> Cadastros X Visitas ACS</strong>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <tbody id="lista-visitas">

<tr>
<?php $dados2 = listarVisitasACS($ano, $mes, $filtros); 
 $i = 1; foreach ($dados2 as $row2): 

$w1 = (int)$row2['sem1'];
$w2 = (int)$row2['sem2'];
$w3 = (int)$row2['sem3'];
$w4 = (int)$row2['sem4'];

$total = $w1 + $w2 + $w3 + $w4;
$total = $total > 0 ? $total : 1;

 $totalVisitas += 
        (int)$row2['sem1'] +
        (int)$row2['sem2'] +
        (int)$row2['sem3'] +
        (int)$row2['sem4'];


$p1 = ($w1 / $total) * 100;
$p2 = ($w2 / $total) * 100;
$p3 = ($w3 / $total) * 100;
$p4 = ($w4 / $total) * 100;

?>

<tr onclick="window.location='?cnes=<?php echo $row2['nu_cnes']."&equipe=".$row2['nu_ine']."&microarea=".$row2['nu_micro_area']; ?>';" style="cursor: pointer;">
  <td><?= $i++; 
  
  ?>.</td>

  <td>
    <strong><?= htmlspecialchars($row2['no_unidade_saude']) ?></strong><br>
  <small>
      Equipe: <b><?= htmlspecialchars($row2['no_equipe'] ?? '-') ?> </b></br>
      ACS: <b><?= htmlspecialchars($row2['no_profissional'] ?? '-')?> </b>
      <br>

      <!-- ✅ MICROÁREA -->
      Microárea:
      <strong><?= htmlspecialchars($row2['nu_micro_area'] ?? '-') ?></strong>
    </small> - QTD:<?php 
	$filtros2 = [];
	$filtros2['cnes'] = explode(',', $row2['nu_cnes']);
    $filtros2['equipe'] = $row2['nu_ine'];
    $filtros2['microarea'] = $row2['nu_micro_area'];
	$qtdpormicro= contarCadastros($filtros2);
	echo $qtdpormicro;
	?>
  </td>

  <td style="width:50%">
    <small>Distribuição semanal</small>

    <!-- BARRA 100% PROPORCIONAL -->
    <div class="progress" style="height: 12px; display: flex; border-radius: 6px; overflow: hidden;">

      <div class="progress-bar bg-primary" style="width: <?= $p1 ?>%"><?= $w1 ?></div>
      <div class="progress-bar bg-info"    style="width: <?= $p2 ?>%"><?= $w2 ?></div>
      <div class="progress-bar bg-warning" style="width: <?= $p3 ?>%"><?= $w3 ?></div>
      <div class="progress-bar bg-success" style="width: <?= $p4 ?>%"><?= $w4 ?></div>

    </div>

    <small>
      Total: <strong><?= $total ?></strong> visitas
    </small>
	<?php echo $qtdpormicro > 0
    ? number_format(($total / $qtdpormicro) * 100, 2) . '%'
    : '0%'; ?>
  </td>

  <td class="text-left">
    SEMANA 01 - <span class="badge text-bg-primary"><?= $w1 ?></span></br>
    SEMANA 02 - <span class="badge text-bg-info"><?= $w2 ?></span></br>
    SEMANA 03 - <span class="badge text-bg-warning"><?= $w3 ?></span></br>
    SEMANA 04 - <span class="badge text-bg-success"><?= $w4 ?></span></br>
  </td>
</tr>

<?php endforeach; ?>
 </tr>

        </tbody>
      </table>
	  
    </div>
  </div>
  
</div>
	</div>
			  <?php } ?>

    </div>
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
    <script>
	
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>

    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>

<script>
	let mesAtual = <?php echo $mesAtual; ?>;
	let anoAtual = <?php echo $anoAtual; ?>;
	async function atualizarDashboard(filtros = {}) {

  const params = { 
    ...filtros,
    mes: mesAtual,
    ano: anoAtual
  };

  if (params.cnes && Array.isArray(params.cnes)) {
    params.cnes = params.cnes.join(',');
  }

  Object.keys(params).forEach(k => {
    if (!params[k]) delete params[k];
  });

  const query = new URLSearchParams(params).toString();

  const response = await fetch('./views/dashboard_totais.php?' + query);
  const result = await response.json();

  if (result.status !== 'success') {
    console.error(result.message);
    return;
  }

  const d = result.data;

  // 🔥 Atualiza SOMENTE os totais
 document.getElementById('totalVisitas').innerText =`${parseInt(d.visitas)} (${Number(d.percentual_visitas).toFixed(1).replace('.', ',')}%)`;
   document.getElementById('visitasRealizadas').innerHTML =
  `Visitas Realizadas - META: 70% ( ${parseInt(d.meta_visitas)} )`
  // 🔥 atualiza TABELA (tbody)
  const list = await fetch('./views/dashboard_listagem.php?' + query);
  const html = await list.text();

  document.getElementById('lista-visitas').innerHTML = html;
}

function mudarMes(novoMes, novoAno) {

  if (novoMes < 1) {
    novoMes = 12;
    novoAno = novoAno - 1;
  }

  if (novoMes > 12) {
    novoMes = 1;
    novoAno = novoAno + 1;
  }

  const hoje = new Date();
  const mesSistema = hoje.getMonth() + 1;
  const anoSistema = hoje.getFullYear();

  // bloqueia futuro
  if (
    novoAno > anoSistema ||
    (novoAno === anoSistema && novoMes > mesSistema)
  ) {
    return;
  }

  mesAtual = novoMes;
  anoAtual = novoAno;

  const params = {
    ...Object.fromEntries(new URLSearchParams(window.location.search)),
    mes: mesAtual,
    ano: anoAtual
  };

  const novaUrl = '?' + new URLSearchParams(params).toString();
  window.history.pushState({}, '', novaUrl);

  atualizarTitulo();
  atualizarDashboard(params);
}
function atualizarTitulo() {
  const meses = [
    "Janeiro","Fevereiro","Março","Abril","Maio","Junho",
    "Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"
  ];

  document.getElementById('tituloMes').innerText =
    `${meses[mesAtual - 1]} / ${anoAtual}`;

  const hoje = new Date();
  const mesSistema = hoje.getMonth() + 1;
  const anoSistema = hoje.getFullYear();

  const botaoProximo = document.querySelectorAll('.btn-outline-primary')[1];

  if (anoAtual === anoSistema && mesAtual >= mesSistema) {
    botaoProximo.classList.add('disabled');
  } else {
    botaoProximo.classList.remove('disabled');
  }
}
const chart = new ApexCharts(
  document.querySelector("#visitas-chart"),
  {
    series: [],
    chart: {
      type: 'bar',
      height: 300,
      stacked: true
    },
    dataLabels: {
      enabled: false
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '55%',
        borderRadius: 4,
        dataLabels: {
          total: {
            enabled: true,
            formatter: val => val.toLocaleString('pt-BR'),
            style: {
              fontSize: '13px',
              fontWeight: 600
            }
          }
        }
      }
    },
    xaxis: {
      categories: []
    }
  }
);

chart.render();

// ================= CARREGAR DADOS
async function carregarDados(filtros = {}) {

  const params = { ...filtros };

  if (params.cnes && Array.isArray(params.cnes)) {
    params.cnes = params.cnes.join(',');
  }

  Object.keys(params).forEach(k => {
    if (!params[k]) delete params[k];
  });

  const query = new URLSearchParams(params).toString();

  const response = await fetch('./views/visitas.php?' + query);

  const result = await response.json();

  if (result.status !== 'success') {
    console.error(result.message);
    return;
  }

  montarSeries(result.data);
}

// ================= TRANSFORMAR DADOS
function montarSeries(data) {

  const mesesSet = new Set();
  const agrupados = {};

  data.forEach(item => {

    const mes = item.mes;
    const nome = item.nome_exibicao || 'Sem nome';
    const total = Number(item.total) || 0;

    mesesSet.add(mes);

    if (!agrupados[nome]) {
      agrupados[nome] = {};
    }

    agrupados[nome][mes] = total;
  });

  const meses = Array.from(mesesSet).sort((a,b) => a.localeCompare(b));

  const series = Object.keys(agrupados).map(nome => ({
    name: nome,
    data: meses.map(m => agrupados[nome][m] || 0)
  }));

  atualizarGrafico(meses, series);
}

// ================= ATUALIZAR GRÁFICO
function atualizarGrafico(meses, series) {
  chart.updateOptions({
    xaxis: { categories: meses },
    series
  });
}

// ================= INIT
const urlParams = Object.fromEntries(new URLSearchParams(window.location.search));

carregarDados(urlParams);
</script>
	
		
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
