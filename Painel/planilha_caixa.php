<?php
$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);//

if (!isset($_GET["data"])) {
    echo $twig->render('planilha_caixa.html', [
        "titulo"            => "LivresBS - Planilha de Caixa",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "alerta"            => "Selecione uma data",
        ]);
} else {        
    $getData = $livres->dataPelaString($_SESSION["data_consulta"]);
    if (!$dataStr = $livres->dataPeloID($getData,'string')) {
        echo $twig->render('planilha_caixa.html', [
            "titulo"            => "LivresBS - Planilha de Caixa",
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            "alerta"            => "Data não encontrada",
            ]);
    } else {
        $oPedidos = new PedidosConsolidados($dataStr);
        $conteudo = $oPedidos->pedidoCompletoPorConsumidor();

        echo $twig->render('planilha_caixa.html', [
            "titulo"            => "LivresBS - Planilha de Caixa - ".date('d/m/Y',strtotime($dataStr)),
            "data_entrega"      => date('d/m/Y',strtotime($dataStr)),
            "conteudo"          => $conteudo,
            "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            ]);
    }
}

?>