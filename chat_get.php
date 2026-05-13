<?php
require 'config.php';
$pdo = mysqlConnect();

$me = $_SESSION['user']['id'];
$user = $_GET['user'];
$offset = $_GET['offset'] ?? 0;

$sql = "
SELECT * FROM mensagens
WHERE (de_id = ? AND para_id = ?)
   OR (de_id = ? AND para_id = ?)
ORDER BY id DESC
LIMIT 20 OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$me, $user, $user, $me]);

echo json_encode(array_reverse($stmt->fetchAll()));