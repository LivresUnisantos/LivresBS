<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

if (!isset($_SESSION["data_id"]) || $_SESSION["data_id"] == "") {
    echo $twig->render('consolidar.html', [
        "titulo"            => "LivresBS - Consolidar Entregas",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "alerta"          => "Selecione uma data"
        ]);
} else {
    if (strtotime($_SESSION['data_consulta']) < strtotime(date("Y-m-d H:i:s")) && $_SESSION["id"] != 1 && $_SESSION["id"] != 3) {
        echo $twig->render('consolidar.html', [
        "titulo"            => "LivresBS - Consolidar Entregas",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "alerta"          => "Você não tem permissão para consolidar um pedido passado"
        ]);
    } else {
        $oConsolidar = new ConsolidarPedidos($_SESSION["data_consulta"]);
        
        $contagemConsolidado = $oConsolidar->totalPedidosConsolidadosPorData();
        
        echo $twig->render('consolidar.html', [
            "titulo"            => "LivresBS - Consolidar Entregas",
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
            "conteudo"          => $contagemConsolidado
            ]);
    }
}
?>