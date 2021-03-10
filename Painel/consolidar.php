<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET["id"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if (!isset($_GET["op"])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}
$op = $_GET["op"];

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$dataStr = $calendario->dataPeloID($_GET["id"]);

if (!$dataStr) {
    if (!isset($_GET["data"])) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
}

$oConsolidar = new ConsolidarPedidos($dataStr);

if ($op == 'total') {
    if ($oConsolidar->consolidarAgoraTudo()) {
        echo "Consolidado total";
    }    
}
if ($op == 'variavel') {
    if ($oConsolidar->consolidarAgoraVariavel()) {
        echo "Consolidado variáveis";
    }    
}
?>