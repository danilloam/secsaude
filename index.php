<?php

$page=1;
require_once __DIR__ . '/CORE/bootstrap.php';
require_once __DIR__ . '/security/session_guard.php';
include "func.php";
if (!isset($_SESSION['user']['id'])) {
    header("Location: .secsaude/security/login.php");
    exit;
}
$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int)date('m');

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

$dados = listarVisitasACS($ano, $mes, $filtros);

$totalVisitas = 0;

foreach ($dados as $row) {
    $totalVisitas +=
        (int)$row['sem1'] +
        (int)$row['sem2'] +
        (int)$row['sem3'] +
        (int)$row['sem4'];
}
$percentualVisitas = 0;

if ($totalcadastros > 0) {
    $percentualVisitas = ($totalVisitas * 100) / $totalcadastros;
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
$bloqueiaProximo = ($anoAtual == $anoHoje && $mesAtual >= $mesHoje);

$parametroEquipes=contarEquipes($filtros);
$percentualCadastro = 0;
if ($totalcadastros > 0 && $parametroEquipes >0) {
    $percentualCadastro = ($totalcadastros * 100) / $parametroEquipes;
}
$metaVisita=($totalcadastros*70)/100;

?>
<?php include "top.php"; ?>
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
              <!--begin::Col-->
              <div class="col-lg-3 col-3">
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
			  <div class="col-lg-3 col-3">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3 id="totalVisitas"><?php echo $totalVisitas." - (".number_format($percentualVisitas, 2, ',', '.') ." %)"; ?></h3>

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
			                <div class="col-lg-2 col-2">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id="totalMedico"><?php echo $totalAtendimentosMedico; ?></h3>

                    <p>Atendimentos M&eacute;dicos</p>
                  </div>
				  <svg class="small-box-icon"  fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72zM380 384.8C374.6 384.3 369 384 363.4 384L276.5 384C270.9 384 265.4 384.3 259.9 384.8L259.9 452.3C276.4 459.9 287.9 476.6 287.9 495.9C287.9 522.4 266.4 543.9 239.9 543.9C213.4 543.9 191.9 522.4 191.9 495.9C191.9 476.5 203.4 459.8 219.9 452.3L219.9 393.9C157 417 112 477.6 112 548.6C112 563.7 124.3 576 139.4 576L500.5 576C515.6 576 527.9 563.7 527.9 548.6C527.9 477.6 482.9 417.1 419.9 394L419.9 431.4C443.2 439.6 459.9 461.9 459.9 488L459.9 520C459.9 531 450.9 540 439.9 540C428.9 540 419.9 531 419.9 520L419.9 488C419.9 477 410.9 468 399.9 468C388.9 468 379.9 477 379.9 488L379.9 520C379.9 531 370.9 540 359.9 540C348.9 540 339.9 531 339.9 520L339.9 488C339.9 461.9 356.6 439.7 379.9 431.4L379.9 384.8z"/></svg>
				  
                 
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Mais Informa&ccedil;&otilde;es<i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
			              <div class="col-lg-2 col-2">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3 id="totalEnfermagem"><?php echo $totalAtendimentosEnfermagem; ?></h3>

                    <p>Atendimentos de Enfermagem</p>
                  </div>
				  
				  <svg  class="small-box-icon"
                    fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M64 112C64 85.5 85.5 64 112 64L160 64C177.7 64 192 78.3 192 96C192 113.7 177.7 128 160 128L128 128L128 256C128 309 171 352 224 352C277 352 320 309 320 256L320 128L288 128C270.3 128 256 113.7 256 96C256 78.3 270.3 64 288 64L336 64C362.5 64 384 85.5 384 112L384 256C384 333.4 329 398 256 412.8L256 432C256 493.9 306.1 544 368 544C429.9 544 480 493.9 480 432L480 346.5C442.7 333.3 416 297.8 416 256C416 203 459 160 512 160C565 160 608 203 608 256C608 297.8 581.3 333.4 544 346.5L544 432C544 529.2 465.2 608 368 608C270.8 608 192 529.2 192 432L192 412.8C119 398 64 333.4 64 256L64 112zM512 288C529.7 288 544 273.7 544 256C544 238.3 529.7 224 512 224C494.3 224 480 238.3 480 256C480 273.7 494.3 288 512 288z"/></svg>
				  
                 
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Mais Informa&ccedil;&otilde;es<i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
			              <div class="col-lg-2 col-2">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-info">
                  <div class="inner">
                    <h3 id="totalOdonto"><?php echo $totalAtendimentosOdonto; ?></h3>

                    <p>Atendimentos Odontológicos</p>
                  </div>
				  
				  <svg  class="small-box-icon"
                    fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M241 69.7L320 96L399 69.7C410.3 65.9 422 64 433.9 64C494.7 64 544 113.3 544 174.1L544 242.6C544 272 534.5 300.7 516.8 324.2L515.7 325.7C502.8 342.9 494.4 363.1 491.4 384.4L469.7 535.9C466.4 558.9 446.7 576 423.5 576C400.7 576 381.2 559.5 377.5 537L357.3 415.6C354.3 397.4 338.5 384 320 384C301.5 384 285.8 397.4 282.7 415.6L262.5 537C258.7 559.5 239.3 576 216.5 576C193.3 576 173.6 558.9 170.3 535.9L148.6 384.5C145.6 363.2 137.2 343 124.3 325.8L123.2 324.3C105.5 300.7 96 272.1 96 242.7L96 174.2C96 113.3 145.3 64 206.1 64C218 64 229.7 65.9 241 69.7z"/></svg>
				  
                 
                  <a
                    href="#"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                  >
                    Mais Informa&ccedil;&otilde;es<i class="bi bi-link-45deg"></i>
                  </a>
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <!--end::Col-->
              
              <!--end::Col-->
              
              <!--end::Col-->
            </div>
            <!--end::Row-->
			<div class="row">
			
			 <div class="col-12">
                <div class="card mb-4">
                  <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                      <h3 class="card-title">Histórico de Atendimentos</h3>
                      
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
					<div id="loading-atendimentos" class="text-center py-3">
					  <div class="spinner-border text-primary" role="status"></div>
					  <div>Carregando gráfico...</div>
					</div>
                    <div class="position-relative mb-4">
                      <div id="atendimentos-chart"></div>
                    </div>

                    
                  </div>
                </div>
                <!-- /.card -->

                
              </div>
			
			</div>
			<div class="row connectedSortable"><div>
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
					<div id="loading-visitas" class="text-center py-3">
					  <div class="spinner-border text-primary" role="status"></div>
					  <div>Carregando gráfico...</div>
					</div>
                    <div class="position-relative mb-4">
                      <div id="visitas-chart"></div>
                    </div>

                    
                  </div>
                </div>
                <!-- /.card -->

                
              </div>
			
			</div>
			
            <!--begin::Row-->
            <div class="row">
              <!-- Start col -->
            <!-- BOTÃO FLUTUANTE DO CHAT -->
<div id="chatFloating">

  <!-- BOTÃO -->
  <button id="chatToggle">
    💬 Chat
  </button>

  <!-- CAIXA DO CHAT -->
  <div id="chatWindow">

    <div id="chatHeader">
      Atendimento
      <span id="chatClose">✖</span>
    </div>

    <div id="chatContent">

      <!-- AQUI ENTRA SEU CHAT -->
      <div class="row">

        <!-- USUÁRIOS -->
        <div class="col-md-3">
          <div class="card">
            <div class="card-header"><strong>Usuários</strong></div>

            <div class="card-body p-0">
              <div id="users" style="height:500px; overflow:auto;"></div>
            </div>
          </div>
        </div>

        <!-- CHAT -->
        <div class="col-md-9">
          <div class="card direct-chat direct-chat-primary">

            <div class="card-header">
              <h3 id="chatTitle">Chat</h3>
            </div>

            <div class="card-body">
              <div id="chatBox" style="height:420px; overflow:auto; padding:10px;"></div>
            </div>

            <div class="card-footer">

              <form id="formChat">
                <div class="input-group">

                  <input type="text"
                         id="msg"
                         class="form-control"
                         placeholder="Mensagem...">

                  <input type="file"
                         id="file"
                         accept="image/*,audio/*">

                  <button class="btn btn-primary">
                    Enviar
                  </button>

                </div>
              </form>

            </div>

          </div>
        </div>

      </div>

    </div>
  </div>
</div>
              <!-- /.Start col -->

              <!-- Start col -->

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
	function showLoadingVisitas() {
  document.getElementById('loading-visitas').style.display = 'block';
  document.getElementById('visitas-chart').style.display = 'none';
}

function hideLoadingVisitas() {
  document.getElementById('loading-visitas').style.display = 'none';
  document.getElementById('visitas-chart').style.display = 'block';
}

function showLoadingAtendimentos() {
  document.getElementById('loading-atendimentos').style.display = 'block';
  document.getElementById('atendimentos-chart').style.display = 'none';
}

function hideLoadingAtendimentos() {
  document.getElementById('loading-atendimentos').style.display = 'none';
  document.getElementById('atendimentos-chart').style.display = 'block';
}
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
 
  document.getElementById('totalMedico').innerText = d.medico;
  document.getElementById('totalEnfermagem').innerText = d.enfermagem;
  document.getElementById('totalOdonto').innerText = d.odonto;

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
	</script>
<script>
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

  showLoadingVisitas();

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
  hideLoadingVisitas();
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
	<script>
const chart1 = new ApexCharts(
  document.querySelector("#atendimentos-chart"),
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

chart1.render();

// ================= CARREGAR DADOS
async function carregarDados1(filtros = {}) {

  showLoadingAtendimentos();

  const params1 = { ...filtros };

  if (params1.cnes && Array.isArray(params1.cnes)) {
    params1.cnes = params1.cnes.join(',');
  }

  Object.keys(params1).forEach(k => {
    if (!params1[k]) delete params1[k];
  });

  const query = new URLSearchParams(params1).toString();

  const response1 = await fetch('./views/atendimentos.php?' + query);
  const result1 = await response1.json();

  if (result1.status !== 'success') {
    console.error(result1.message);
    return;
  }

  montarSeries1(result1.data);
  hideLoadingAtendimentos();
}

// ================= TRANSFORMAR DADOS
function montarSeries1(data) {

  const mesesSet = new Set();
  const tiposSet = new Set();

  const mapa = {};

  data.forEach(item => {

    const mes = item.mes;
    const tipo = item.tipo || 'Outros';
    const total = Number(item.total) || 0;

    mesesSet.add(mes);
    tiposSet.add(tipo);

    if (!mapa[mes]) mapa[mes] = {};
    if (!mapa[mes][tipo]) mapa[mes][tipo] = 0;

    // 🔥 soma todas unidades automaticamente
    mapa[mes][tipo] += total;
  });

  const meses = Array.from(mesesSet).sort();
  const tipos = Array.from(tiposSet);

  const series = tipos.map(tipo => ({
    name: tipo,
    data: meses.map(mes => {
      return mapa?.[mes]?.[tipo] || 0;
    })
  }));

  atualizarGrafico1(meses, series);
}
// ================= ATUALIZAR GRÁFICO
function atualizarGrafico1(meses1, series1) {
  chart1.updateOptions({
    xaxis: { categories: meses1 },
    series: series1
  });
}

// ================= INIT
const urlParams1 = Object.fromEntries(new URLSearchParams(window.location.search));

carregarDados1(urlParams1);
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
<script>
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach((tooltipTriggerEl) => {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    </script>
    <!-- jsvectormap -->
   <script>

/* ABRIR CHAT */
document.getElementById('chatToggle').addEventListener('click', function(){

  const chat = document.getElementById('chatWindow');

  if(chat.style.display === 'block'){
    chat.style.display = 'none';
  }else{
    chat.style.display = 'block';
  }

});

/* FECHAR CHAT */
document.getElementById('chatClose').addEventListener('click', function(){

  document.getElementById('chatWindow').style.display = 'none';

});

</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="./dist/js/direct-chat.min.js"></script>
	<script>
let user = null;
let offset = 0;
let loading = false;
let lastCount = 0;

/**
 * =====================
 * USUÁRIOS POR DISTRITO
 * =====================
 */
async function loadUsers() {

  const res = await fetch('users.php');
  const data = await res.json();

  const el = document.getElementById('users');
  if (!el) return;

  el.innerHTML = '';

  data.forEach(u => {

    el.innerHTML += `
      <div onclick="selectUser(${u.id}, '${u.nome}')"
           style="padding:10px; cursor:pointer; border-bottom:1px solid #eee;">
        ${u.online == 1 ? '🟢' : '⚪'} ${u.nome}
      </div>
    `;
  });
}

/**
 * =====================
 * SELECIONAR USUÁRIO
 * =====================
 */
function selectUser(id, nome) {

  user = id;
  offset = 0;

  document.getElementById('chatTitle').innerText = nome;

  loadMessages(true);
}

/**
 * =====================
 * MENSAGENS
 * =====================
 */
async function loadMessages(reset = false) {

  if (!user) return;

  const res = await fetch(`chat_get.php?user=${user}&offset=${offset}`);
  const data = await res.json();

  const box = document.getElementById('chatBox');
  if (!box) return;

  if (reset) box.innerHTML = '';

  data.forEach(m => {

    const mine = m.from_id == <?= (int)$_SESSION['user']['id'] ?>;

    let content = '';

    if (m.type === 'text') content = m.mensagem;

    if (m.type === 'image') {
      content = `<img src="${m.file_path}" style="max-width:200px;">`;
    }

    if (m.type === 'audio') {
      content = `<audio controls src="${m.file_path}"></audio>`;
    }

    const check = m.read_at ? '✓✓' : '✓';

    box.innerHTML += `
      <div style="text-align:${mine ? 'right' : 'left'}; margin:5px;">
        <div style="display:inline-block; padding:8px; border-radius:10px;
                    background:${mine ? '#d1ecf1' : '#eee'}">

          ${content}
          <div style="font-size:10px;">${check}</div>

        </div>
      </div>
    `;
  });

  box.scrollTop = box.scrollHeight;

  offset += 20;
}

/**
 * =====================
 * SCROLL INFINITO
 * =====================
 */
document.getElementById('chatBox')?.addEventListener('scroll', async function () {

  if (this.scrollTop === 0 && !loading) {
    loading = true;
    await loadMessages(false);
    loading = false;
  }
});

/**
 * =====================
 * ENVIAR MENSAGEM
 * =====================
 */
document.getElementById('formChat')?.addEventListener('submit', async e => {

  e.preventDefault();

  if (!user) return;

  const form = new FormData();

  form.append('mensagem', document.getElementById('msg').value);
  form.append('para', user);

  const file = document.getElementById('file').files[0];
  if (file) form.append('file', file);

  await fetch('chat_send.php', {
    method: 'POST',
    body: form
  });

  document.getElementById('msg').value = '';
  document.getElementById('file').value = '';

  loadMessages(true);
});

/**
 * =====================
 * NOTIFICAÇÕES
 * =====================
 */
function notify(msg) {

  if (Notification.permission === "granted") {
    new Notification("Nova mensagem", { body: msg });
  }
}

/**
 * =====================
 * CHECAR NOVAS MENSAGENS
 * =====================
 */
async function checkNew() {

  if (!user) return;

  const res = await fetch(`chat_get.php?user=${user}`);
  const data = await res.json();

  if (data.length > lastCount) {
    notify("Nova mensagem recebida");
    loadMessages(true);
  }

  lastCount = data.length;
}

/**
 * =====================
 * INIT
 * =====================
 */
if ('Notification' in window) {
  Notification.requestPermission();
}

loadUsers();
setInterval(loadUsers, 5000);
setInterval(checkNew, 3000);
</script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
