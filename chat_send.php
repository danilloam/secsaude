<?php
require 'config.php';
$pdo = mysqlConnect();

$from = $_SESSION['user']['id'];
$to = $_POST['para'];

$type = 'text';
$msg = $_POST['mensagem'] ?? null;
$filePath = null;

if (!empty($_FILES['file']['name'])) {

    $dir = "uploads/chat/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filePath = $dir . uniqid() . "_" . $_FILES['file']['name'];

    move_uploaded_file($_FILES['file']['tmp_name'], $filePath);

    if (str_contains($_FILES['file']['type'], 'image')) {
        $type = 'image';
    } else {
        $type = 'audio';
    }

    $msg = null;
}

$sql = "INSERT INTO mensagens (de_id,para_id,mensagem,type,file_path)
        VALUES (?,?,?,?,?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$from,$to,$msg,$type,$filePath]);

echo json_encode(['ok'=>true]);