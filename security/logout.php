<?php
require_once __DIR__ . '/../CORE/bootstrap.php';

// garante sessão iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// atualiza sessão no banco (se existir)
if (session_id()) {
    mysqlConnect()->prepare("
        UPDATE user_sessions 
        SET is_active = 0 
        WHERE session_id = ?
    ")->execute([session_id()]);
}

// destrói sessão
session_unset();
session_destroy();

// redireciona para login
header("Location: /secsaude/security/login.php");
exit;