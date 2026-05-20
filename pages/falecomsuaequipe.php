<?php

$page = 5;

require_once __DIR__ . '/../CORE/config.php';
require_once __DIR__ . '/../security/session_guard.php';
require_once __DIR__ . '/../helpers/func.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}
$sheet = "1if2BXY3NJwSRgGfx7RUg4rfzaXOBARYgZV_U-Ic7t_Y";
$aba   = "falecomsuaequipe";


$url = "https://opensheet.elk.sh/{$sheet}/" . urlencode($aba);


$arquivo = __DIR__ . '/../cache/falecomsuaequipe.json';

if (file_exists($arquivo)) {
    // 🔥 lê o cache
    $json = file_get_contents($arquivo);
} else {
    // 🔥 fallback: busca online
    $json = file_get_contents($url);
}


if ($json === false) die("Erro ao acessar planilha");

$dados = json_decode($json, true);
if (!$dados) die("Erro JSON");

// ================= FILTROS
$usFiltro = $_GET['us'] ?? '';
$dsFiltro = "DS-".numeroParaRomano($_SESSION['user']['distrito_id']);
$dataInicioFiltro = $_GET['data_inicio'] ?? '';
$dataFimFiltro = $_GET['data_fim'] ?? '';
$statusFiltro = normalizar($_GET['status'] ?? '');

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
    $status = normalizar($item['situação'] ?? '');

    // filtro DS
    if ($dsFiltro && $ds !== $dsFiltro) continue;
	// filtro Unidade
    if ($usFiltro && $us !== $usFiltro) continue;
	// filtro STATUS
	if ($statusFiltro && $status !== $statusFiltro) continue;
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
	$status = normalizar($item['situação'] ?? '');

	if ($statusFiltro && $status !== $statusFiltro) continue;
    // respeita filtro de DS
    if ($dsFiltro && $ds !== $dsFiltro) continue;

    $listaUS[] = $us;
}

$listaUS = array_unique($listaUS);
sort($listaUS);
$listaDS = array_unique(array_column($dados, 'ds'));
sort($listaDS);
$estiloextra="<style>
	.ok { color:green; font-weight:bold; }
.nok { color:red; font-weight:bold; }
.filtros { background:#fff;padding:10px;margin-bottom:15px;border-radius:8px;}

.linha-ok {
    background-color: #d4edda;
}

.linha-nok td {
    background-color: #f8d7da !important;
}
	</style>";
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
      <div class="col-lg-4 col-4">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3 id="totalCadastros"><?= $total ?></h3>

                    <p>Total</p>
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
                    <h3 id="totaldesatualizado"><?= $atendido ?></h3>

                    <p id="porcentagemdesatualizados">Atendidos</p>
			
               
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
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3 id="totalVisitas"><?= $naoAtendido ?></h3>

                    <p id="visitasRealizadas">Pendentes</p>
			
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
          <div class="row">
			 <form class="filtros">
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
	<select name="status" onchange="this.form.submit()">
		<option value="">Todos</option>
		<option value="atendido" <?= ($statusFiltro=='atendido'?'selected':'') ?>>Atendido</option>
		<option value="nao atendido" <?= ($statusFiltro=='em aberto'?'selected':'') ?>>Não Atendido</option>
	</select>
    <button type="submit">Filtrar</button>
    <button type="button" onclick="exportarPDF()">Exportar PDF</button>
</form>
</div>  
            <!--end::Row-->
			
			</div>
		
 <?php foreach ($agrupado as $ds => $unidades): ?>	
 
			   <?php foreach ($unidades as $us => $info): 
        $media = $info['qtd'] ? ($info['tempo_total'] / $info['qtd']) : 0;
		
    ?>
		<div class="row">
			 <div class="col-12">
                <div class="card mb-4">
				<div class="card card-outline card-primary ">
                  <div class="card-header border-0">
                    <div class="card-header">
                      <h3 class="card-title"><?= $us ?> | Média resposta: <?= $media; ?> min</h3>
                      <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                      </button>
                    </div>

                    </div>
                  </div>
                  <div class="card-body">
				 


        <table class="table table-bordered table-striped">
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
                $status = normalizar($r['situação'] ?? '');
                // define classe da linha
				$status = normalizar($r['situação'] ?? '');

				if ($status !== 'atendido') {
					 $classeLinha = 'linha-nok'; // vermelho
					$classe = 'nok';
				} else {
					$classeLinha = 'linha-ok'; // verde
					$classe = 'ok';
				}
            ?>
            <tr class="<?= $classeLinha ?>">
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
</div>
</div>
                </div>			  
                </div>
                 </div>  
    <?php endforeach; ?>

<?php endforeach; ?>
	
    
               
                <!-- /.card -->

                
              
			
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
	</script>	

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
