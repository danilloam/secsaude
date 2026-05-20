<?php
require_once __DIR__ . '/CORE/bootstrap.php';
require_once __DIR__ . '/security/session_guard.php';

// view padrão
$view = $_GET['view'] ?? 'home';

// segurança básica (evita acesso indevido)
$allowedViews = [
    'home',
    'visitas',
    'atendimentos',
	'interdicoes',
	'falecomsuaequipe',
	'agendaconecta',
	'configuracoes'
];

// valida
if (!in_array($view, $allowedViews)) {
    $view = '404';
}

// monta caminho
$file = __DIR__ . "/pages/{$view}.php";

// fallback extra
if (!file_exists($file)) {
    $file = __DIR__ . "/pages/404.php";
}

// carrega view
require $file;