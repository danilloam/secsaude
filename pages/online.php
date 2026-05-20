<?php
require_once __DIR__ . '/../CORE/bootstrap.php';
$pdo = mysqlConnect();

$userId = $_SESSION['user']['id'];
$sessionId = session_id();

$sql = "
UPDATE user_sessions
SET last_activity = NOW()
WHERE user_id = :user
  AND session_id = :session
  AND is_active = 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user' => $userId,
    ':session' => $sessionId
]);