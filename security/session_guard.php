<?php
declare(strict_types=1);

require_once __DIR__ . '/../CORE/bootstrap.php';
require_once __DIR__ . '/../CORE/cache.php';

const LOCK_TIMEOUT = 300;
const SESSION_TIMEOUT = 900;
const MAX_LIFETIME = 28800;
const REGEN_INTERVAL = 300;

$now = time();

/**
 * NÃO RODAR NO LOGIN
 */
if (basename($_SERVER['PHP_SELF']) === 'login.php') {
    return;
}

/**
 * LOGIN OBRIGATÓRIO
 */
if (empty($_SESSION['user']['id'])) {
    header("Location: " . BASE_URL . "/security/login.php");
    exit;
}

$pdo = mysqlConnect();

/**
 * VALIDA SESSÃO NO BANCO
 */
$stmt = $pdo->prepare("
    SELECT * FROM user_sessions 
    WHERE session_id = ? 
    AND user_id = ? 
    AND is_active = 1
    LIMIT 1
");

$stmt->execute([
    session_id(),
    $_SESSION['user']['id']
]);

$session = $stmt->fetch();

if (!$session) {
    session_destroy();
    header("Location: " . BASE_URL . "/security/login.php");
    exit;
}

/**
 * ANTI HIJACK
 */
if (!hash_equals($session['fingerprint'], session_fingerprint())) {
    session_destroy();
    header("Location: " . BASE_URL . "/security/login.php");
    exit;
}

/**
 * CONTROLE DE TEMPO
 */
if (($now - ($_SESSION['created'] ?? 0)) > MAX_LIFETIME ||
    ($now - ($_SESSION['last_activity'] ?? 0)) > SESSION_TIMEOUT) {

    session_destroy();
	header("Location: " . BASE_URL . "/security/login.php?expired=1");
   
    exit;
}

/**
 * LOCK
 */
if (!empty($_SESSION['locked'])) {
	    header("Location: " . BASE_URL . "/security/lock.php");

    exit;
}

if (($now - ($_SESSION['last_activity'] ?? 0)) > LOCK_TIMEOUT) {
    $_SESSION['locked'] = true;
     header("Location: " . BASE_URL . "/security/lock.php");
    exit;
}

/**
 * ATUALIZA ATIVIDADE
 */
$_SESSION['last_activity'] = $now;

$pdo->prepare("
    UPDATE user_sessions 
    SET last_activity = ?
    WHERE session_id = ?
")->execute([$now, session_id()]);

/**
 * REGENERAÇÃO SEGURA
 */
$_SESSION['last_regen'] ??= $now;

if (($now - $_SESSION['last_regen']) > REGEN_INTERVAL) {

    $oldId = session_id();

    session_regenerate_id(true);
    $newId = session_id();

    $pdo->prepare("
        UPDATE user_sessions 
        SET session_id = ?
        WHERE session_id = ?
    ")->execute([$newId, $oldId]);

    $_SESSION['last_regen'] = $now;
}