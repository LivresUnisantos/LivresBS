<?php
$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);//
//unset($_SESSION["data_consulta"]);
$dataStr = "";
if (isset($_SESSION["data_consulta"])) {
    $getData = $livres->dataPelaString($_SESSION["data_consulta"]);
    if (!$dataStr = $livres->dataPeloID($getData,'string')) {
        echo $twig->render('pagamentos.html', [
            "titulo"            => "LivresBS - Controle Pagamentos",
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            "alerta"            => "Data não encontrada",
            ]);
        exit();
    }
}

if (isset($_SESSION["data_consulta"])) {
    $oPedidos = new PedidosConsolidados($dataStr);
    $conteudo = $oPedidos->pedidoCompletoPorConsumidor();
} else {
    $oPedidos = new PedidosConsolidados();
    $conteudo = $oPedidos->pedidsoPagamentoPendente();
}

$oPix = new Pix;
echo $twig->render('pagamentos.html', [
    "titulo"                => "LivresBS - Controle Pagamentos - ".date('d/m/Y',strtotime($dataStr)),
    "data_entrega"          => ($dataStr == '') ? '' : date('d/m/Y',strtotime($dataStr)),
    "conteudo"              => $conteudo,
    "formas_pagamento"      => $livres->formas_pagamento(),
    "status_pagamento"      => array(0 => "Não Pago",1 => "Em aprovação", 2 => "Pago"),
    "url_pix_pagamento"     => $livres->getParametro('url_pix_pagamento'),
    "pix_pendentes"         => $oPix->CopiaColaPendentes(),
    "pix_erro_valor"        => $oPix->CopiaColaErroValor(),
    "pagamento_erro_valor"  => $oPix->PagamentoErroValor(),
    "frequencia_semana"     => $calendario->montaDisplayFrequenciaSemana(),
    "menu_datas"            => $calendario->listaDatas(),
    "data_selecionada"      => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    ]);
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
/*
if (!isset($_GET["data"]) && !isset($_SESSION["data_consulta"])) {
    echo $twig->render('pagamentos.html', [
        "titulo"            => "LivresBS - Controle Pagamentos",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "alerta"            => "Selecione uma data",
        ]);
} else {        
    $getData = $livres->dataPelaString($_SESSION["data_consulta"]);
    if (!$dataStr = $livres->dataPeloID($getData,'string')) {
        echo $twig->render('pagamentos.html', [
            "titulo"            => "LivresBS - Controle Pagamentos",
            "menu_datas"        => $calendario->listaDatas(),
            "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            "alerta"            => "Data não encontrada",
            ]);
    } else {
        $oPedidos = new PedidosConsolidados($dataStr);
        $conteudo = $oPedidos->pedidoCompletoPorConsumidor();
        
        $oPix = new Pix;
        
        echo $twig->render('pagamentos.html', [
            "titulo"                => "LivresBS - Controle Pagamentos - ".date('d/m/Y',strtotime($dataStr)),
            "data_entrega"          => date('d/m/Y',strtotime($dataStr)),
            "conteudo"              => $conteudo,
            "formas_pagamento"      => $livres->formas_pagamento(),
            "status_pagamento"      => array(0 => "Não Pago",1 => "Em aprovação", 2 => "Pago"),
            "url_pix_pagamento"     => $livres->getParametro('url_pix_pagamento'),
            "pix_pendentes"         => $oPix->CopiaColaPendentes(),
            "pix_erro_valor"        => $oPix->CopiaColaErroValor(),
            "pagamento_erro_valor"  => $oPix->PagamentoErroValor(),
            "frequencia_semana"     => $calendario->montaDisplayFrequenciaSemana(),
            "menu_datas"            => $calendario->listaDatas(),
            "data_selecionada"      => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
            ]);
    }
}
*/
?>