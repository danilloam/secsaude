<?php
require_once __DIR__ . '/CORE/bootstrap.php';
mysqlConnect()->prepare("
    UPDATE user_sessions 
    SET is_active = 0 
    WHERE session_id = ?
")->execute([session_id()]);

session_unset();
session_destroy();

header("Location: /secsaude/security/login.php");
exit;