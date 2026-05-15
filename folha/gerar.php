<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/CORE/bootstrap.php';

use Dompdf\Dompdf;

$pdo = pgConnect();

$id = $_GET['id'];
$mes = $_GET['mes'];
$ano = $_GET['ano'];

// BUSCA
$stmt = $pdo->prepare("SELECT * FROM servidores WHERE id = :id");
$stmt->execute([':id'=>$id]);
$s = $stmt->fetch();

// HTML
ob_start();
include 'folha_template.php';
$html = ob_get_clean();

// PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

$dompdf->stream("folha.pdf", ["Attachment"=>false]);