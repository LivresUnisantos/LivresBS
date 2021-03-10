<?php
$levelRequired=2000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
//$twig = new \Twig\Environment($loader, ['debug' => true]);
//$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig = new \Twig\Environment($loader, ['debug' => false]);


    
if (!isset($_GET["data"])) {
    echo $twig->render('entregas.html', [
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_GET['data']) ? $_GET["data"] : ""),
        "alerta"            => "Selecione uma data"
        ]);
} else {        
    if (!$dataStr = $livres->dataPeloID($_GET["data"],'string')) {
        echo $twig->render('entregas.html', [
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_GET['data']) ? $_GET["data"] : ""),
            "alerta"            => "Data não encontrada"
            ]);
    } else {
        $frequencia_semana = $calendario->montaDisplayFrequenciaSemana(strtotime($dataStr));
        $oPedidos = new PedidosConsolidados($dataStr);
        $conteudo = $oPedidos->pedidoCompletoPorConsumidor();        

        if (!$conteudo) {
            echo $twig->render('entregas.html', [
                "frequencia_semana" => $frequencia_semana,
                "menu_datas"        => $calendario->listaDatas(),
                "data_selecionada"  => (isset($_GET['data']) ? $_GET["data"] : ""),
                "alerta"            => "Sem pedidos para esta data"
                ]);
        } else {
            echo $twig->render('entregas.html', [
                "titulo"            => "LivresBS - Entregas da Semana - ".date('d/m/Y',strtotime($dataStr)),
                "data_entrega"      => date('d/m/Y',strtotime($dataStr)),
                "conteudo"          => $conteudo,
                "frequencia_semana" => $frequencia_semana,
                "menu_datas"        => $calendario->listaDatas(),
                "data_selecionada"  => (isset($_GET['data']) ? $_GET["data"] : "")
                ]);
        }

    }
}
?>