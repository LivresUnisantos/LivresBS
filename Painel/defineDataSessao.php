<?php
require_once "acesso.php";
require_once "../includes/autoloader.inc.php";
if (isset($_GET["data"])) {
    $livres = new Livres;
    
    $data = $_GET["data"];
    $data = DateTime::createFromFormat('d/m/Y H:i', $data);
    $data = $data->format('Y-m-d H:i');

    $dataId = $livres->dataPelaString($data);

    $_SESSION['data_consulta'] = $data;
    $_SESSION['data_id'] = $dataId;
    echo $_SESSION['data_consulta'];
} else {
    if (isset($_SESSION['data_consulta'])) {
        echo $_SESSION['data_consulta'];
    } else {
        echo "";
    }
}
?>