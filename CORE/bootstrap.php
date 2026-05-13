<?php
declare(strict_types=1);

/**
 * BASE PATH (RAIZ DO PROJETO)
 */
define('BASE_PATH', dirname(__DIR__));

/**
 * LOAD ENV + CONFIG
 */
require_once BASE_PATH . '/CORE/env.php';
require_once BASE_PATH . '/CORE/config.php';

/**
 * HEADERS DE SEGURANÇA
 */
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

/**
 * CONFIGURAÇÃO DE SESSÃO (ÚNICA)
 */
if (session_status() === PHP_SESSION_NONE) {

    session_name('secsaude_session');

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/**
 * FUNÇÕES GLOBAIS
 */
function session_fingerprint(): string {
    return hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
}