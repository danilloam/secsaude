<?php
require_once __DIR__ . '/CORE/bootstrap.php';

function nomeSobrenome($nome){
    $nome = trim($nome);

    if ($nome === '') return '';

    $partes = explode(" ", $nome);

    return $partes[0] . " " . end($partes);
}
function atualizarMeses($mes) {
    switch ($mes) {
        case 1: return "Janeiro";
        case 2: return "Fevereiro";
        case 3: return "Março";
        case 4: return "Abril";
        case 5: return "Maio";
        case 6: return "Junho";
        case 7: return "Julho";
        case 8: return "Agosto";
        case 9: return "Setembro";
        case 10: return "Outubro";
        case 11: return "Novembro";
        case 12: return "Dezembro";
        default: return "Mês inválido";
    }
}
function dataCriacao($data){
$timestamp = strtotime($data);
 if (!$timestamp) return '';
 
$meses = [
    1 => "Jan", 2 => "Fev", 3 => "Mar", 4 => "Abr",
    5 => "Mai", 6 => "Jun", 7 => "Jul", 8 => "Ago",
    9 => "Set", 10 => "Out", 11 => "Nov", 12 => "Dez"
];

$mes = $meses[(int)date("n", $timestamp)];
$ano = date("Y", $timestamp);

return "Registrado desde {$mes}. {$ano}";
	
}
function gerarPeriodo($ano, $mes) {
    $inicioDate = sprintf('%04d-%02d-01', $ano, $mes);
    $inicioTs = strtotime($inicioDate);
    $fimTs = strtotime(date("Y-m-t", $inicioTs));

    $semanas = [];
    $contador = 1;

    $atual = $inicioTs;

    while ($atual <= $fimTs) {
        $semInicio = $atual;
        $semFim = strtotime("+6 days", $semInicio);

        if ($semFim > $fimTs) {
            $semFim = $fimTs;
        }

        $semanas["sem{$contador}_ini"] = (int) date("Ymd", $semInicio);
        $semanas["sem{$contador}_fim"] = (int) date("Ymd", $semFim);

        $atual = strtotime("+7 days", $semInicio);
        $contador++;
    }

    return array_merge([
        'inicio' => (int) date("Ymd", $inicioTs),
        'fim' => (int) date("Ymd", $fimTs),
    ], $semanas);
}
function montarSemanasSQL($semanas,$tipo) {
    $sqlPartes = [];
$tipo=$tipo;
    $i = 1;
    while (isset($semanas["sem{$i}_ini"])) {
        $ini = ":sem{$i}_ini";
        $fim = ":sem{$i}_fim";

        $sqlPartes[] = "
            SUM(
                CASE 
                    WHEN $tipo.co_dim_tempo BETWEEN {$ini} AND {$fim} 
                    THEN 1 ELSE 0 
                END
            ) AS sem{$i}
        ";

        $i++;
    }

    return implode(",\n", $sqlPartes);
}
function UnidadeporId($unidadeid){
	$pdo = mysqlConnect();
	$stmt = $pdo->prepare("SELECT cnes_us 
            FROM us_distrito 
            WHERE ativo = '1' and id = ?");
    $stmt->execute([$unidadeid]);
    return $stmt->fetchColumn();

}
function listarUnidadesDistrito($distrito_id){
	$pdo = mysqlConnect();
	$stmt = $pdo->prepare("SELECT cnes_us 
            FROM us_distrito 
            WHERE ativo = '1' and distrito_id = ? ");
    $stmt->execute([$distrito_id]);
     return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
function referenciaPorUnidade($distrito_id){
	$pdo = mysqlConnect();
	$stmt = $pdo->prepare("
	SELECT us.cnes_us, conf.valor 
	FROM us_distrito us 
	inner join configuracoes conf 
	ON us.tipo = conf.id 
	WHERE ativo = '1' and distrito_id = ?;");
    $stmt->execute([$distrito_id]);
     return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
function listarVisitasCnes($ano, $mes, $filtros = [], $agruparPor = 'unidade')
{
    $pdo = pgConnect();

    $periodo = gerarPeriodo($ano, $mes);
		foreach ($periodo as $k => $v) {
    $params[":$k"] = $v;
	}
	$sqlSemanas = montarSemanasSQL($periodo,'visitas');
	
    $whereExtra = "";
switch ($agruparPor) {
	case "unidade":
         $selectExtra = ""; // não mostra equipe
        $groupBy = "unidades.nu_cnes, unidades.no_unidade_saude";
        break;
    case "equipe":
        $selectExtra = "equipes.no_equipe,equipes.nu_ine,";
        $groupBy = "unidades.nu_cnes, unidades.no_unidade_saude, equipes.no_equipe,equipes.nu_ine";
        break;
    case "micro":
		$selectExtra = "equipes.no_equipe,equipes.nu_ine,visitas.nu_micro_area,";
        $groupBy = "unidades.nu_cnes, unidades.no_unidade_saude, equipes.no_equipe,equipes.nu_ine,visitas.nu_micro_area";
        break;
    default:
         $selectExtra = ""; // não mostra equipe
        $groupBy = "unidades.nu_cnes, unidades.no_unidade_saude";
        break;
}
    // filtro CNES
    if (!empty($filtros)) {
        $params[':cnes'] = '{' . implode(',', (array)$filtros) . '}';
        $whereExtra .= " AND unidades.nu_cnes = ANY(:cnes) ";
    }
	if (!empty($filtros['equipe'])) {
    $params[':equipe'] = '{' . implode(',', (array)$filtros['equipe']) . '}';
    $whereExtra .= " AND equipes.nu_ine = ANY(:equipe) ";
	}

// ================= MICROÁREA
	if (!empty($filtros['microarea'])) {
    $params[':microarea'] = '{' . implode(',', (array)$filtros['microarea']) . '}';
    $whereExtra .= " AND visitas.nu_micro_area = ANY(:microarea) ";
	}
	

    $sql = "
        SELECT 
            unidades.nu_cnes,
            unidades.no_unidade_saude,
            $selectExtra

            {$sqlSemanas}

        FROM tb_fat_visita_domiciliar visitas

        LEFT JOIN tb_dim_equipe equipes 
            ON equipes.co_seq_dim_equipe = visitas.co_dim_equipe

        LEFT JOIN tb_dim_unidade_saude unidades 
            ON visitas.co_dim_unidade_saude = unidades.co_seq_dim_unidade_saude

        WHERE visitas.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra
        GROUP BY $groupBy
        ORDER BY sem4 DESC
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarVisitasACS($ano, $mes, $filtros = []){

    $pdo = pgConnect();

    $whereExtra = "";
    $params = [];

    // ================= PERÍODO
    $periodo = gerarPeriodo($ano, $mes);
// adiciona TODOS os parâmetros do período (inicio, fim e semanas)
	foreach ($periodo as $k => $v) {
    $params[":$k"] = $v;
	}
    // adiciona TODOS os parâmetros (inicio, fim e semanas)


    $sqlSemanas = montarSemanasSQL($periodo,'visitas');

    // ================= CNES
    if (!empty($filtros['cnes'])) {

        $cnesArray = (array)$filtros['cnes'];
        $placeholders = [];

        foreach ($cnesArray as $i => $cnes) {
            $key = ":cnes$i";
            $placeholders[] = $key;
            $params[$key] = $cnes;
        }

        $whereExtra .= " AND unidades.nu_cnes IN (" . implode(',', $placeholders) . ") ";
    }

    // ================= EQUIPE
    if (!empty($filtros['equipe'])) {
        $params[':equipe'] = '{' . implode(',', (array)$filtros['equipe']) . '}';
        $whereExtra .= " AND equipes.nu_ine = ANY(:equipe) ";
    }

    // ================= ACS
    if (!empty($filtros['acs'])) {
        $whereExtra .= " AND profs.co_seq_dim_profissional = :acs ";
        $params[':acs'] = $filtros['acs'];
    }

    // ================= MICROÁREA
    if (!empty($filtros['microarea'])) {
        $params[':microarea'] = '{' . implode(',', (array)$filtros['microarea']) . '}';
        $whereExtra .= " AND visitas.nu_micro_area = ANY(:microarea) ";
    }

    // ================= SQL
    $sql = "
        SELECT 
            unidades.nu_cnes,
            unidades.no_unidade_saude,
            equipes.nu_ine,
            equipes.no_equipe,
            profs.no_profissional,
            visitas.nu_micro_area,

            {$sqlSemanas}

        FROM tb_fat_visita_domiciliar visitas

        LEFT JOIN tb_dim_equipe equipes 
            ON equipes.co_seq_dim_equipe = visitas.co_dim_equipe

        LEFT JOIN tb_dim_profissional profs 
            ON visitas.co_dim_profissional = profs.co_seq_dim_profissional

        LEFT JOIN tb_dim_unidade_saude unidades 
            ON visitas.co_dim_unidade_saude = unidades.co_seq_dim_unidade_saude

        WHERE visitas.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra

        GROUP BY 
            unidades.nu_cnes,
            unidades.no_unidade_saude,
            equipes.nu_ine,
            equipes.no_equipe,
            profs.no_profissional,
            visitas.nu_micro_area

        ORDER BY sem4 DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarEquipes($filtros = []){
    $pdo = mysqlConnect();

    $where = ["us.ativo = '1'"];
    $params = [];

    // CNES
    $cnesList = [];

    if (!empty($filtros['cnes'])) {
        if (is_array($filtros['cnes'])) {
            $cnesList = $filtros['cnes'];

            $placeholders = implode(',', array_fill(0, count($cnesList), '?'));
            $where[] = "us.cnes_us IN ($placeholders)";
            $params = array_merge($params, $cnesList);
        } else {
            $cnesList = [$filtros['cnes']];
            $where[] = "us.cnes_us = ?";
            $params[] = $filtros['cnes'];
        }
    }

    // Equipe
    $temEquipe = !empty($filtros['equipe']);

    $sql = "SELECT 
                SUM(us.qtd_ine * conf.valor) AS total
            FROM us_distrito us
            INNER JOIN configuracoes conf ON us.tipo = conf.id
            WHERE " . implode(' AND ', $where);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = (float) ($row['total'] ?? 0);

    // Buscar qtd_ine real do CNES
    $qtdIne = 1;

    if (!empty($cnesList)) {
        $placeholders = implode(',', array_fill(0, count($cnesList), '?'));

        $sqlIne = "SELECT MAX(qtd_ine) AS qtd_ine
                   FROM us_distrito
                   WHERE cnes_us IN ($placeholders)";

        $stmtIne = $pdo->prepare($sqlIne);
        $stmtIne->execute($cnesList);
        $qtdIne = (float) ($stmtIne->fetchColumn() ?: 1);
    }

    // Base de cálculo (equipe ou não)
    $resultado = $temEquipe
        ? ($qtdIne > 0 ? ($total / $qtdIne) : $total)
        : $total;

    // Microárea tem prioridade máxima
    if (!empty($filtros['microarea'])) {
        return $resultado / 5;
    }

    return $resultado;
}
function contarCadastros($filtros = []){
    $pdo = pgConnect();

    $mapa = [
        'cnes'  => 'nu_cnes_vinc_equipe',
        'equipe'   => 'nu_ine_vinc_equipe',
		'nomeequipe'   => 'no_equipe_vinc_equipe',
        'microarea' => 'nu_micro_area_domicilio'
    ];

    $where = [];
    $params = [];

    foreach ($filtros as $tipo => $valor) {

        if (!isset($mapa[$tipo])) {
            throw new InvalidArgumentException("Filtro inválido: $tipo");
        }

        $coluna = $mapa[$tipo];

        // ================= ARRAY (IN)
        if (is_array($valor)) {

            $placeholders = [];

            foreach ($valor as $i => $v) {
                $param = ":{$tipo}_{$i}";
                $placeholders[] = $param;
                $params[$param] = $v;
            }

            $where[] = "$coluna IN (" . implode(',', $placeholders) . ")";

        } else {
            $param = ":$tipo";
            $where[] = "$coluna = $param";
            $params[$param] = $valor;
        }
    }
 $where[] = "no_equipe_vinc_equipe ILIKE '%ESF%'";
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT COUNT(*) 
        FROM public.tb_acomp_cidadaos_vinculados
        $whereSql 
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->execute();

    return (int) $stmt->fetchColumn();
}
function contarCadastrosDesatualizados($filtros = []){
    $pdo = pgConnect();

    $mapa = [
        'cnes'  => 'nu_cnes_vinc_equipe',
        'equipe'   => 'nu_ine_vinc_equipe',
		'nomeequipe'   => 'no_equipe_vinc_equipe',
        'microarea' => 'nu_micro_area_domicilio'
    ];

    $where = [];
    $params = [];

    foreach ($filtros as $tipo => $valor) {

        if (!isset($mapa[$tipo])) {
            throw new InvalidArgumentException("Filtro inválido: $tipo");
        }

        $coluna = $mapa[$tipo];

        // ================= ARRAY (IN)
        if (is_array($valor)) {

            $placeholders = [];

            foreach ($valor as $i => $v) {
                $param = ":{$tipo}_{$i}";
                $placeholders[] = $param;
                $params[$param] = $v;
            }

            $where[] = "$coluna IN (" . implode(',', $placeholders) . ")";

        } else {
            $param = ":$tipo";
            $where[] = "$coluna = $param";
            $params[$param] = $valor;
        }
    }
 $where[] = "no_equipe_vinc_equipe ILIKE '%ESF%'";

$where[] =  "dt_ultima_atualizacao_cidadao <= CURRENT_DATE - INTERVAL '2 years'";
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT COUNT(*) 
        FROM public.tb_acomp_cidadaos_vinculados
        $whereSql 
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->execute();

    return (int) $stmt->fetchColumn();
}
function listarAtendimentos($ano, $mes, $filtros = [], $agruparPor = 'unidade'){
    $whereExtra = "";
    $periodo = gerarPeriodo($ano, $mes);
	$sqlSemanas = montarSemanasSQL($periodo,'ati');
	$params = [];

// adiciona TODOS os parâmetros do período (inicio, fim e semanas)
	foreach ($periodo as $k => $v) {
    $params[":$k"] = $v;
	}
	$pdo = pgConnect();
    // ================= TIPO (TABELA)
	$tipo = $filtros['tipo'] ?? 'individual';
    switch ($tipo) {
        case 'odonto':
            $tabela = "tb_fat_atendimento_odonto";
            break;

        case 'individual':
        default:
            $tabela = "tb_fat_atendimento_individual";
            break;
    }

    // ================= AGRUPAMENTO
    switch ($agruparPor) {
        case "equipe":
            $selectExtra = "equ.no_equipe, equ.nu_ine";
            $groupBy = "uns.nu_cnes, uns.no_unidade_saude, equ.no_equipe, equ.nu_ine";
            break;
		case "profissional":
			$selectExtra = "prof.no_profissional AS profissional, prof.nu_cns AS cns_profissional,";
			$groupBy = "prof.no_profissional, prof.nu_cns, uns.nu_cnes, uns.no_unidade_saude";
        break;
        default:
            $selectExtra = "";
            $groupBy = "uns.nu_cnes, uns.no_unidade_saude";
            break;
	
    }

    // ================= FILTROS DINÂMICOS
    

    // CNES
    if (!empty($filtros['cnes'])) {
    $cnesArray = (array)$filtros['cnes'];

    $placeholders = [];
    foreach ($cnesArray as $i => $cnes) {
        $key = ":cnes$i";
        $placeholders[] = $key;
        $params[$key] = $cnes;
    }

    $whereExtra .= " AND uns.nu_cnes IN (" . implode(',', $placeholders) . ") ";
}

    // equipe
    if (!empty($filtros['equipe'])) {
        $params[':equipe'] = '{' . implode(',', (array)$filtros['equipe']) . '}';
        $whereExtra .= " AND equ.nu_ine = ANY(:equipe) ";
    }

    // CBO DINÂMICO (⭐ principal melhoria)
    if (!empty($filtros['cboLista'])) {
        $params[':cbo'] = '{' . implode(',', (array)$filtros['cboLista']) . '}';
        $whereExtra .= " AND ati.co_dim_cbo_1 = ANY(:cbo) ";
    }
if (!empty($filtros['equipe'])) {
    $agruparPor = 'profissional';
}
    // ================= SQL
    $sql = "
        SELECT 
            uns.nu_cnes,
            uns.no_unidade_saude,
            " . ($selectExtra ? ", $selectExtra" : "") . "

			{$sqlSemanas}

        FROM $tabela ati

        LEFT JOIN tb_dim_unidade_saude uns 
            ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1

        LEFT JOIN tb_dim_equipe equ 
            ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1 

        LEFT JOIN tb_dim_cbo cbo 
            ON cbo.co_seq_dim_cbo = ati.co_dim_cbo_1

        LEFT JOIN tb_dim_profissional pro 
            ON pro.co_seq_dim_profissional = ati.co_dim_profissional_1

        LEFT JOIN tb_dim_turno tur 
            ON tur.co_seq_dim_turno = ati.co_dim_turno

        LEFT JOIN tb_dim_tempo tdt 
            ON tdt.co_seq_dim_tempo = ati.co_dim_tempo

        LEFT JOIN tb_dim_faixa_etaria tdfe 
            ON tdfe.co_seq_dim_faixa_etaria = ati.co_dim_faixa_etaria

        WHERE ati.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra and ati.co_dim_tipo_atendimento <> 5

        GROUP BY $groupBy
        ORDER BY sem4 DESC
    ";

    $stmt = $pdo->prepare($sql);
	$stmt->execute($params);
    

 

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarAtendimentosCnes($ano, $mes, $filtros = [], $agruparPor = 'unidade')
{
    $pdo = pgConnect();

    // ================= PERÍODO
    $periodo = gerarPeriodo($ano, $mes);
    $sqlSemanas = montarSemanasSQL($periodo, 'ati');

    $params = [];
    foreach ($periodo as $k => $v) {
        $params[":$k"] = $v;
    }

    // ================= NORMALIZAÇÃO DE FILTROS
    $filtros = normalizarFiltros($filtros);

    // ================= TABELA (TIPO)
    $tabela = match ($filtros['tipo'] ?? 'individual') {
        'odonto' => 'tb_fat_atendimento_odonto',
        default  => 'tb_fat_atendimento_individual',
    };

    // ================= AGRUPAMENTO
    [$selectExtra, $groupBy] = montarAgrupamento($agruparPor);
	$selectExtra = rtrim($selectExtra, ', ');
	$groupBy     = rtrim($groupBy, ', ');
    // ================= FILTROS DINÂMICOS
    $whereExtra = '';

    $whereExtra .= montarFiltroIN('uns.nu_cnes', $filtros['cnes'], $params, 'cnes');
    $whereExtra .= montarFiltroIN('equ.nu_ine', $filtros['equipe'], $params, 'equipe');
    $whereExtra .= montarFiltroIN('ati.co_dim_cbo_1', $filtros['cboLista'], $params, 'cbo');
	$whereExtra .= montarFiltroIN('prof.no_profissional',$filtros['profissional'] ?? [],$params,'profissional');
	$whereExtra .= montarFiltroIN('ati.co_dim_local_atendimento', $filtros['tipoAtendimento'], $params, 'tipoAtendimento');
    // ================= SQL
    $sql = "
        SELECT 
            uns.nu_cnes,
            uns.no_unidade_saude,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta agendada programada / Cuidado continuado' THEN 1 ELSE 0 END), 0) AS consulta_programada,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta agendada' THEN 1 ELSE 0 END), 0) AS consulta_agendada,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Escuta inicial / Orientação' THEN 1 ELSE 0 END), 0) AS escuta_inicial,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta no dia' THEN 1 ELSE 0 END), 0) AS demanda_espontanea,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Atendimento de urgência' THEN 1 ELSE 0 END), 0) AS urgencia
            " . ($selectExtra ? ", $selectExtra" : "") . ",
            {$sqlSemanas}

        FROM $tabela ati

        LEFT JOIN tb_dim_unidade_saude uns 
            ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1

        LEFT JOIN tb_dim_equipe equ 
            ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1 

        LEFT JOIN tb_dim_cbo cbo 
            ON cbo.co_seq_dim_cbo = ati.co_dim_cbo_1

        LEFT JOIN tb_dim_turno tur 
            ON tur.co_seq_dim_turno = ati.co_dim_turno

        LEFT JOIN tb_dim_tempo tdt 
            ON tdt.co_seq_dim_tempo = ati.co_dim_tempo

        LEFT JOIN tb_dim_faixa_etaria tdfe 
            ON tdfe.co_seq_dim_faixa_etaria = ati.co_dim_faixa_etaria
		
		LEFT JOIN tb_dim_tipo_atendimento  tdta
            ON ati.co_dim_tipo_atendimento = tdta.co_seq_dim_tipo_atendimento
		LEFT JOIN tb_dim_profissional prof
            	ON ati.co_dim_profissional_1 = prof.co_seq_dim_profissional
        WHERE ati.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra and ati.co_dim_tipo_atendimento <> 5

        GROUP BY $groupBy
        ORDER BY sem4 DESC
    ";


    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarEscutaIncialCnes($ano, $mes, $filtros = [], $agruparPor = 'unidade')
{
    $pdo = pgConnect();

    // ================= PERÍODO
    $periodo = gerarPeriodo($ano, $mes);
    $sqlSemanas = montarSemanasSQL($periodo, 'ati');

    $params = [];
    foreach ($periodo as $k => $v) {
        $params[":$k"] = $v;
    }

    // ================= NORMALIZAÇÃO DE FILTROS
    $filtros = normalizarFiltros($filtros);

    // ================= TABELA (TIPO)
    $tabela = match ($filtros['tipo'] ?? 'individual') {
        'odonto' => 'tb_fat_atendimento_odonto',
        default  => 'tb_fat_atendimento_individual',
    };

    // ================= AGRUPAMENTO
    [$selectExtra, $groupBy] = montarAgrupamento($agruparPor);
	$selectExtra = rtrim($selectExtra, ', ');
	$groupBy     = rtrim($groupBy, ', ');
    // ================= FILTROS DINÂMICOS
    $whereExtra = '';

    $whereExtra .= montarFiltroIN('uns.nu_cnes', $filtros['cnes'], $params, 'cnes');
    $whereExtra .= montarFiltroIN('equ.nu_ine', $filtros['equipe'], $params, 'equipe');
    $whereExtra .= montarFiltroIN('ati.co_dim_cbo_1', $filtros['cboLista'], $params, 'cbo');
	$whereExtra .= montarFiltroIN('prof.no_profissional',$filtros['profissional'] ?? [],$params,'profissional');
	$whereExtra .= montarFiltroIN('ati.co_dim_local_atendimento', $filtros['tipoAtendimento'], $params, 'tipoAtendimento');
    // ================= SQL
    $sql = "
        SELECT 
            uns.nu_cnes,
            uns.no_unidade_saude,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta agendada programada / Cuidado continuado' THEN 1 ELSE 0 END), 0) AS consulta_programada,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta agendada' THEN 1 ELSE 0 END), 0) AS consulta_agendada,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Escuta inicial / Orientação' THEN 1 ELSE 0 END), 0) AS escuta_inicial,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Consulta no dia' THEN 1 ELSE 0 END), 0) AS demanda_espontanea,
			COALESCE(SUM(CASE WHEN tdta.ds_tipo_atendimento = 'Atendimento de urgência' THEN 1 ELSE 0 END), 0) AS urgencia
             " . ($selectExtra ? ", $selectExtra" : "") . ",
            {$sqlSemanas}

        FROM $tabela ati

        LEFT JOIN tb_dim_unidade_saude uns 
            ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1

        LEFT JOIN tb_dim_equipe equ 
            ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1 

        LEFT JOIN tb_dim_cbo cbo 
            ON cbo.co_seq_dim_cbo = ati.co_dim_cbo_1


        LEFT JOIN tb_dim_turno tur 
            ON tur.co_seq_dim_turno = ati.co_dim_turno

        LEFT JOIN tb_dim_tempo tdt 
            ON tdt.co_seq_dim_tempo = ati.co_dim_tempo

        LEFT JOIN tb_dim_faixa_etaria tdfe 
            ON tdfe.co_seq_dim_faixa_etaria = ati.co_dim_faixa_etaria
		
		LEFT JOIN tb_dim_tipo_atendimento  tdta
            ON ati.co_dim_tipo_atendimento = tdta.co_seq_dim_tipo_atendimento
		LEFT JOIN tb_dim_profissional prof
            	ON ati.co_dim_profissional_1 = prof.co_seq_dim_profissional
        WHERE ati.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra and ati.co_dim_tipo_atendimento = 5

        GROUP BY $groupBy
        ORDER BY sem4 DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarEscutaInicial($ano, $mes, $filtros = [], $agruparPor = 'unidade'){
	$whereExtra = "";
        $periodo = gerarPeriodo($ano, $mes);
	$sqlSemanas = montarSemanasSQL($periodo);
	
	$pdo = pgConnect();


    // ================= TIPO (TABELA)
	$tipo = $filtros['tipo'] ?? 'individual';
    switch ($tipo) {
        case 'odonto':
            $tabela = "tb_fat_atendimento_odonto";
            break;

        case 'individual':
        default:
            $tabela = "tb_fat_atendimento_individual";
            break;
    }

    // ================= AGRUPAMENTO
    switch ($agruparPor) {
        case "equipe":
            $selectExtra = "equ.no_equipe, equ.nu_ine";
            $groupBy = "uns.nu_cnes, uns.no_unidade_saude, equ.no_equipe, equ.nu_ine";
            break;

        default:
            $selectExtra = "";
            $groupBy = "uns.nu_cnes, uns.no_unidade_saude";
            break;
    }

    // ================= FILTROS DINÂMICOS
    
	$whereExtra = "and ati.co_dim_tipo_atendimento = 5";
    // CNES
    if (!empty($filtros['cnes'])) {
    $cnesArray = (array)$filtros['cnes'];

    $placeholders = [];
    foreach ($cnesArray as $i => $cnes) {
        $key = ":cnes$i";
        $placeholders[] = $key;
        $params[$key] = $cnes;
    }

    $whereExtra .= " AND uns.nu_cnes IN (" . implode(',', $placeholders) . ") ";
}

    // equipe
    if (!empty($filtros['equipe'])) {
        $params[':equipe'] = '{' . implode(',', (array)$filtros['equipe']) . '}';
        $whereExtra .= " AND equ.nu_ine = ANY(:equipe) ";
    }

    // CBO DINÂMICO (⭐ principal melhoria)
    if (!empty($filtros['cboLista'])) {
        $params[':cbo'] = '{' . implode(',', (array)$filtros['cboLista']) . '}';
        $whereExtra .= " AND ati.co_dim_cbo_1 = ANY(:cbo) ";
    }

    // ================= SQL
    $sql = "
        SELECT 
            uns.nu_cnes,
            uns.no_unidade_saude,
            " . ($selectExtra ? ", $selectExtra" : "") . "

			{$sqlSemanas}
        FROM $tabela ati

        LEFT JOIN tb_dim_unidade_saude uns 
            ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1

        LEFT JOIN tb_dim_equipe equ 
            ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1 

        LEFT JOIN tb_dim_cbo cbo 
            ON cbo.co_seq_dim_cbo = ati.co_dim_cbo_1

        LEFT JOIN tb_dim_profissional pro 
            ON pro.co_seq_dim_profissional = ati.co_dim_profissional_1

        LEFT JOIN tb_dim_turno tur 
            ON tur.co_seq_dim_turno = ati.co_dim_turno

        LEFT JOIN tb_dim_tempo tdt 
            ON tdt.co_seq_dim_tempo = ati.co_dim_tempo

        LEFT JOIN tb_dim_faixa_etaria tdfe 
            ON tdfe.co_seq_dim_faixa_etaria = ati.co_dim_faixa_etaria

        WHERE ati.co_dim_tempo BETWEEN :inicio AND :fim
        $whereExtra

        GROUP BY $groupBy
        ORDER BY sem4 DESC
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarVisitasUltimos12Meses($filtros = []): array{
	$whereExtra = "";
    $params = [];


    // ================= QUANTIDADE DE CNES
    $cnesSelecionados = $filtros['cnes'] ?? [];
    $qtdCnes = is_array($cnesSelecionados) ? count($cnesSelecionados) : 0;

    // ================= NÍVEL REAL (drill-down)
    $nivel = 'cnes';

    if (!empty($filtros['microarea'])) {
        $nivel = 'microarea';
    } elseif (!empty($filtros['equipe'])) {
        $nivel = 'microarea'; // já desceu nível
    } elseif ($qtdCnes === 1) {
        $nivel = 'equipe';
    }

    // ================= DIMENSÃO
    if ($nivel === 'microarea') {
        $campoAgrupamento = "visitas.nu_micro_area";
        $campoNome = "'Microárea ' || visitas.nu_micro_area";
    } 
    elseif ($nivel === 'equipe') {
        $campoAgrupamento = "equipes.nu_ine";
        $campoNome = "equipes.no_equipe";
    } 
    else {
        $campoAgrupamento = "unidades.nu_cnes";
        $campoNome = "unidades.no_unidade_saude";
    }

    // ================= FILTROS
    if (!empty($cnesSelecionados)) {
        $cnesArray = array_values((array)$cnesSelecionados);
        $cnesArray = (array)$filtros['cnes'];

	$placeholders = [];
	foreach ($cnesArray as $i => $cnes) {
		$key = ":cnes$i";
		$placeholders[] = $key;
		$params[$key] = $cnes;
	}

	$whereExtra .= " AND unidades.nu_cnes IN (" . implode(',', $placeholders) . ") ";
    }

    if (!empty($filtros['equipe'])) {
        $params[':equipe'] = '{' . implode(',', (array)$filtros['equipe']) . '}';
    $whereExtra .= " AND equipes.nu_ine = ANY(:equipe) ";
    }

    if (!empty($filtros['microarea'])) {
        $params[':microarea'] = '{' . implode(',', (array)$filtros['microarea']) . '}';
    $whereExtra .= " AND visitas.nu_micro_area = ANY(:microarea) ";
    }

    // ================= SQL
    $sql = "
        SELECT 

            $campoNome AS nome_exibicao,

            to_char(
                to_date(visitas.co_dim_tempo::text, 'YYYYMMDD'),
                'YYYY-MM'
            ) AS mes,

            COUNT(*) AS total

        FROM tb_fat_visita_domiciliar visitas

        LEFT JOIN tb_dim_unidade_saude unidades 
            ON visitas.co_dim_unidade_saude = unidades.co_seq_dim_unidade_saude

        LEFT JOIN tb_dim_equipe equipes
            ON visitas.co_dim_equipe = equipes.co_seq_dim_equipe

        WHERE to_date(visitas.co_dim_tempo::text, 'YYYYMMDD') 
              >= (CURRENT_DATE - INTERVAL '12 months')

        AND to_date(visitas.co_dim_tempo::text, 'YYYYMMDD') 
              < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'


        $whereExtra

        GROUP BY $campoNome, mes
        ORDER BY mes ASC
    ";

    $pdo = pgConnect();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listarAtendimentosUltimos12Meses($filtros = []): array{
    try {

        $params = [];
        $where = [];

        // ================= CNES
        $cnesSelecionados = $filtros['cnes'] ?? [];
        $qtdCnes = is_array($cnesSelecionados) ? count($cnesSelecionados) : 0;

        if (!empty($cnesSelecionados)) {
            $placeholders = [];

            foreach ($cnesSelecionados as $i => $cnes) {
                $key = ":cnes$i";
                $placeholders[] = $key;
                $params[$key] = $cnes;
            }

            $where[] = "uns.nu_cnes IN (" . implode(',', $placeholders) . ")";
        }

        // ================= NÍVEL (REGRA DE NEGÓCIO)
        if (!empty($filtros['microarea'])) { 
            $campoNome = "'Microárea ' || ati.nu_micro_area";
        } elseif (!empty($filtros['equipe']) || $qtdCnes === 1) {
            // 🔥 1 CNES → detalhar por equipe
            $campoNome = "COALESCE(equ.no_equipe, 'Sem equipe')";
        } else {
            // 🔥 múltiplos CNES → visão por unidade
            $campoNome = "uns.no_unidade_saude";
        }

        // ================= FILTROS
        if (!empty($filtros['equipe'])) {
            $where[] = "equ.nu_ine = :equipe";
            $params[':equipe'] = $filtros['equipe'];
        }

       

        // ================= PERÍODO (OTIMIZADO)
        $where[] = "
            to_date(ati.co_dim_tempo::text, 'YYYYMMDD') >= (CURRENT_DATE - INTERVAL '12 months')
        ";
		
        $where[] = "
            to_date(ati.co_dim_tempo::text, 'YYYYMMDD') < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'
        ";

        // ================= TIPO (CBO)
        $cboPorTipo = [
            'medico' => [6904, 6879],
            'enfermeiro' => [6890],
            'odonto' => [6888],
        ];

        $cboSelecionados = [];

        if (!empty($filtros['tipo'])) {
            foreach ($filtros['tipo'] as $tipo) {
                if (isset($cboPorTipo[$tipo])) {
                    $cboSelecionados = array_merge($cboSelecionados, $cboPorTipo[$tipo]);
                }
            }
        }

        if (!empty($cboSelecionados)) {
            $placeholders = [];

            foreach ($cboSelecionados as $i => $cbo) {
                $key = ":cbo$i";
                $placeholders[] = $key;
                $params[$key] = (int)$cbo;
            }

            $where[] = "ati.co_dim_cbo_1 IN (" . implode(',', $placeholders) . ")";
        }

        // ================= WHERE FINAL
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // ================= CASE TIPO
        $caseTipo = "
            CASE 
                WHEN ati.co_dim_cbo_1 IN (6904,6879) THEN 'Médico'
                WHEN ati.co_dim_cbo_1 IN (6890) THEN 'Enfermagem'
                WHEN ati.co_dim_cbo_1 IN (6888) THEN 'Odonto'
                ELSE 'Outros'
            END
        ";

        // ================= BASE INDIVIDUAL
        $sqlIndividual = "
            SELECT 
                $campoNome AS nome_exibicao,
                $caseTipo AS tipo,
                to_char(to_date(ati.co_dim_tempo::text, 'YYYYMMDD'),'YYYY-MM') AS mes,
                COUNT(*) AS total
            FROM tb_fat_atendimento_individual ati
            LEFT JOIN tb_dim_unidade_saude uns 
                ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1
            LEFT JOIN tb_dim_equipe equ 
                ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1
            $whereSql and ati.co_dim_tipo_atendimento <> 5
            GROUP BY nome_exibicao, tipo, mes
        ";

        // ================= BASE ODONTO
        $sqlOdonto = "
            SELECT 
                $campoNome AS nome_exibicao,
                'Odonto' AS tipo,
                to_char(to_date(ati.co_dim_tempo::text, 'YYYYMMDD'),'YYYY-MM') AS mes,
                COUNT(*) AS total
            FROM tb_fat_atendimento_odonto ati
            LEFT JOIN tb_dim_unidade_saude uns 
                ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1
            LEFT JOIN tb_dim_equipe equ 
                ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1
            $whereSql and ati.co_dim_tipo_atendimento <> 5
            GROUP BY nome_exibicao, tipo, mes
        ";

        // ================= UNION
        $usarOdonto = empty($filtros['tipo']) || in_array('odonto', $filtros['tipo']);

        if ($usarOdonto) {
            $sql = "
                SELECT nome_exibicao, tipo, mes, SUM(total) as total
                FROM (
                    $sqlIndividual
                    UNION ALL
                    $sqlOdonto
                ) t
                GROUP BY nome_exibicao, tipo, mes
                ORDER BY mes ASC, nome_exibicao
            ";
        } else {
            $sql = $sqlIndividual . " ORDER BY mes ASC, nome_exibicao";
        }

        // ================= EXECUÇÃO
        $pdo = pgConnect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {

        error_log('Erro listarAtendimentos: ' . $e->getMessage());

        return [];
    }
}
function listarEscutaInicialUltimos12Meses($filtros = []): array{
    try {

        $params = [];
        $where = [];

        // ================= CNES
        $cnesSelecionados = $filtros['cnes'] ?? [];
        $qtdCnes = is_array($cnesSelecionados) ? count($cnesSelecionados) : 0;

        if (!empty($cnesSelecionados)) {
            $placeholders = [];

            foreach ($cnesSelecionados as $i => $cnes) {
                $key = ":cnes$i";
                $placeholders[] = $key;
                $params[$key] = $cnes;
            }

            $where[] = "uns.nu_cnes IN (" . implode(',', $placeholders) . ")";
        }

        // ================= NÍVEL (REGRA DE NEGÓCIO)
        if (!empty($filtros['microarea'])) { 
            $campoNome = "'Microárea ' || ati.nu_micro_area";
        } elseif (!empty($filtros['equipe']) || $qtdCnes === 1) {
            // 🔥 1 CNES → detalhar por equipe
            $campoNome = "COALESCE(equ.no_equipe, 'Sem equipe')";
        } else {
            // 🔥 múltiplos CNES → visão por unidade
            $campoNome = "uns.no_unidade_saude";
        }

        // ================= FILTROS
        if (!empty($filtros['equipe'])) {
            $where[] = "equ.nu_ine = :equipe";
            $params[':equipe'] = $filtros['equipe'];
        }

       

        // ================= PERÍODO (OTIMIZADO)
        $where[] = "
            to_date(ati.co_dim_tempo::text, 'YYYYMMDD') >= (CURRENT_DATE - INTERVAL '12 months')
        ";
		
        $where[] = "
            to_date(ati.co_dim_tempo::text, 'YYYYMMDD') < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'
        ";

        // ================= TIPO (CBO)
        $cboPorTipo = [
            'medico' => [6904, 6879],
            'enfermeiro' => [6890],
            'odonto' => [6888],
        ];

        $cboSelecionados = [];

        if (!empty($filtros['tipo'])) {
            foreach ($filtros['tipo'] as $tipo) {
                if (isset($cboPorTipo[$tipo])) {
                    $cboSelecionados = array_merge($cboSelecionados, $cboPorTipo[$tipo]);
                }
            }
        }

        if (!empty($cboSelecionados)) {
            $placeholders = [];

            foreach ($cboSelecionados as $i => $cbo) {
                $key = ":cbo$i";
                $placeholders[] = $key;
                $params[$key] = (int)$cbo;
            }

            $where[] = "ati.co_dim_cbo_1 IN (" . implode(',', $placeholders) . ")";
        }

        // ================= WHERE FINAL
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // ================= CASE TIPO
        $caseTipo = "
            CASE 
                WHEN ati.co_dim_cbo_1 IN (6904,6879) THEN 'Médico'
                WHEN ati.co_dim_cbo_1 IN (6890) THEN 'Enfermagem'
                WHEN ati.co_dim_cbo_1 IN (6888) THEN 'Odonto'
                ELSE 'Outros'
            END
        ";

        // ================= BASE INDIVIDUAL
        $sqlIndividual = "
            SELECT 
                $campoNome AS nome_exibicao,
                $caseTipo AS tipo,
                to_char(to_date(ati.co_dim_tempo::text, 'YYYYMMDD'),'YYYY-MM') AS mes,
                COUNT(*) AS total
            FROM tb_fat_atendimento_individual ati
            LEFT JOIN tb_dim_unidade_saude uns 
                ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1
            LEFT JOIN tb_dim_equipe equ 
                ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1
            $whereSql and ati.co_dim_tipo_atendimento = 5
            GROUP BY nome_exibicao, tipo, mes
        ";

        // ================= BASE ODONTO
        $sqlOdonto = "
            SELECT 
                $campoNome AS nome_exibicao,
                'Odonto' AS tipo,
                to_char(to_date(ati.co_dim_tempo::text, 'YYYYMMDD'),'YYYY-MM') AS mes,
                COUNT(*) AS total
            FROM tb_fat_atendimento_odonto ati
            LEFT JOIN tb_dim_unidade_saude uns 
                ON uns.co_seq_dim_unidade_saude = ati.co_dim_unidade_saude_1
            LEFT JOIN tb_dim_equipe equ 
                ON equ.co_seq_dim_equipe = ati.co_dim_equipe_1
            $whereSql and ati.co_dim_tipo_atendimento = 5
            GROUP BY nome_exibicao, tipo, mes
        ";

        // ================= UNION
        $usarOdonto = empty($filtros['tipo']) || in_array('odonto', $filtros['tipo']);

        if ($usarOdonto) {
            $sql = "
                SELECT nome_exibicao, tipo, mes, SUM(total) as total
                FROM (
                    $sqlIndividual
                    UNION ALL
                    $sqlOdonto
                ) t
                GROUP BY nome_exibicao, tipo, mes
                ORDER BY mes ASC, nome_exibicao
            ";
        } else {
            $sql = $sqlIndividual . " ORDER BY mes ASC, nome_exibicao";
        }

        // ================= EXECUÇÃO
        $pdo = pgConnect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {

        error_log('Erro listarAtendimentos: ' . $e->getMessage());

        return [];
    }
}
function calcVisitas($row) {

    $w = [];

    // Sempre pega as 4 primeiras
    for ($i = 1; $i <= 4; $i++) {
        $w[$i] = (int)($row["sem{$i}"] ?? 0);
    }

    // Verifica se existe semana 5
    if (isset($row['sem5'])) {
        $w[5] = (int)$row['sem5'];
    }

    $total = array_sum($w);
    $safe = $total > 0 ? $total : 1;

    $result = [];

    // Mantém padrão antigo: w1, w2...
    foreach ($w as $k => $valor) {
        $result["w{$k}"] = $valor;
    }

    // Total
    $result['total'] = $total;

    // Percentuais no padrão antigo: p1, p2...
    foreach ($w as $k => $valor) {
        $result["p{$k}"] = ($valor / $safe) * 100;
    }

    return $result;
}
function maiorValorSemanas($row): int{
    return max(
        (int)$row['sem1'],
        (int)$row['sem2'],
        (int)$row['sem3'],
        (int)$row['sem4']
    );
}
function normalizarFiltros($filtros)
{
    foreach (['cnes', 'equipe', 'cboLista'] as $campo) {

        // 🔥 garante que a chave sempre existe
        if (!isset($filtros[$campo])) {
            $filtros[$campo] = [];
            continue;
        }

        if (!empty($filtros[$campo])) {

            if (is_string($filtros[$campo])) {
                $filtros[$campo] = array_filter(
                    array_map('trim', explode(',', $filtros[$campo]))
                );
            }

            if (!is_array($filtros[$campo])) {
                $filtros[$campo] = [$filtros[$campo]];
            }
        }
    }

    return $filtros;
}
function montarFiltroIN($campo, $valores, &$params, $prefixo)
{
    if (empty($valores)) return '';

    // 🔥 GARANTIA ABSOLUTA
    if (!is_array($valores)) {
        $valores = [$valores];
    }

    $placeholders = [];

    foreach ($valores as $i => $val) {
        $key = ":{$prefixo}{$i}";
        $placeholders[] = $key;
        $params[$key] = $val;
    }

    return " AND $campo IN (" . implode(',', $placeholders) . ") ";
}
function montarAgrupamento($agruparPor)
{
    switch ($agruparPor) {

        case 'profissional':
            return [
                "prof.no_profissional AS profissional, prof.nu_cns AS cns_profissional",
                "prof.no_profissional, prof.nu_cns, uns.nu_cnes, uns.no_unidade_saude"
            ];

        case 'equipe':
            return [
                "equ.no_equipe,equ.nu_ine,",
                "uns.nu_cnes, uns.no_unidade_saude,  equ.nu_ine, equ.no_equipe"
            ];

        case 'unidade':
        default:
            return [
                "",
                "uns.nu_cnes, uns.no_unidade_saude"
            ];
    }
}
function adicionarPeriodoParams(&$params, $periodo)
{
    foreach ($periodo as $k => $v) {
        $params[":$k"] = $v;
    }
}
function consolidarAtendimentos($ano, $mes, $tipo, $filtros = [])
{

	
    // =========================
    // 1. APS (BASE PRINCIPAL)
    // =========================
    $filtros['tipoAtendimento'] = 2;
    $dados = listarAtendimentosCnes($ano, $mes, $filtros, $tipo);

    // Escuta APS
    $dadosEscuta = listarEscutaIncialCnes($ano, $mes, $filtros, $tipo);
    $mapEscuta = [];
    foreach ($dadosEscuta as $item) {
        $mapEscuta[$item['nu_cnes']] = $item['escuta_inicial'] ?? 0;
    }

    // Visita APS
    $filtros['tipoAtendimento'] = 5;
    $dadosVisita = listarAtendimentosCnes($ano, $mes, $filtros, $tipo);
    $mapVisita = [];
    foreach ($dadosVisita as $item) {
        $mapVisita[$item['nu_cnes']] = $item['demanda_espontanea'] ?? 0;
    }

    // Aplica no array principal
    foreach ($dados as $i => $item) {
        $cnes = $item['nu_cnes'];
		$dados[$i]['tipo'] = $filtros['tipo'] ?? 'aps';
		$dados[$i]['cboLista'] = is_array($filtros['cboLista'] ?? null)
    ? $filtros['cboLista']
    : [$filtros['cboLista']];
        $dados[$i]['escuta_inicial']   = $mapEscuta[$cnes] ?? 0;
        $dados[$i]['visitaDomiciliar'] = $mapVisita[$cnes] ?? 0;

    }

    // =========================
    // 2. ODONTO
    // =========================
    $filtros['tipo'] = "odonto";
    $filtros['cboLista'] = "6888";

    // Atendimento Odonto
    $filtros['tipoAtendimento'] = 2;
    $dadosOdonto = listarAtendimentosCnes($ano, $mes, $filtros, $tipo);

    $mapOdonto = [];
    foreach ($dadosOdonto as $item) {
        $mapOdonto[$item['nu_cnes']] = $item;
    }

    // Escuta Odonto
    $dadosEscutaOdonto = listarEscutaIncialCnes($ano, $mes, $filtros, $tipo);
    foreach ($dadosEscutaOdonto as $item) {
        $cnes = $item['nu_cnes'];
        $mapOdonto[$cnes]['escuta_inicial'] = $item['escuta_inicial'] ?? 0;
    }

    // Visita Odonto
    $filtros['tipoAtendimento'] = 5;
    $dadosVisitaOdonto = listarAtendimentosCnes($ano, $mes, $filtros, $tipo);
    foreach ($dadosVisitaOdonto as $item) {
        $cnes = $item['nu_cnes'];
        $mapOdonto[$cnes]['visitaDomiciliar'] = $item['demanda_espontanea'] ?? 0;
    }

    // =========================
    // 3. SOMA FINAL
    // =========================
    $campos = [
        'consulta_programada',
        'consulta_agendada',
        'escuta_inicial',
        'demanda_espontanea',
        'urgencia',
        'sem1',
        'sem2',
        'sem3',
        'sem4',
        'sem5',
        'visitaDomiciliar'
    ];

    foreach ($dados as $i => $item) {
        $cnes = $item['nu_cnes'];

        foreach ($campos as $campo) {
            $dados[$i][$campo] =
                ($item[$campo] ?? 0) +
                ($mapOdonto[$cnes][$campo] ?? 0);
        }
    }

    return $dados;
}
function renderCardBody($item) {
    include 'partials/card_body.php';
}