<?php
require '../_pdf/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf;
ob_start();
require 'montagem_print.php';
$dompdf->loadHtml(ob_get_clean());
$dompdf->setPaper("A4");
$dompdf->render();
$dompdf->stream("montagem.pdf", ["Attachment" => false]);


