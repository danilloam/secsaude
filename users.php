<?php
require_once __DIR__ . '/CORE/bootstrap.php';
$pdo = mysqlConnect();

$me = $_SESSION['user']['id'];

$sql = "
SELECT 
    u.id,
    u.nome,

    EXISTS(
        SELECT 1
        FROM user_sessions s
        WHERE s.user_id = u.id
        AND s.is_active = 1
        AND s.last_activity > UNIX_TIMESTAMP(NOW() - INTERVAL 5 MINUTE)
    ) AS online

FROM users u
WHERE u.id != ?
AND EXISTS(
    SELECT 1
    FROM user_sessions s
    WHERE s.user_id = u.id
    AND s.is_active = 1
    AND s.last_activity > UNIX_TIMESTAMP(NOW() - INTERVAL 5 MINUTE)
)
ORDER BY u.nome ASC;
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$me]);

echo json_encode($stmt->fetchAll());