<?php
if (!isset($_GET)) exit();

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$uuid = $_SERVER['QUERY_STRING'];

$livres = new Livres();
$pix = new Pix();

$pixCopiaCola = $pix->showCopiaCola($uuid);
if ($pixCopiaCola) {
    echo $pix->PrintQRCode($pixCopiaCola);
    echo '<br>';
    echo $pixCopiaCola;
} else {
    echo "Link de pagamento não encontrado";
}
?>