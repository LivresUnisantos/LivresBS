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

$oProdutos = new Produtos;

$alerta = "";
$sucesso = "";
if (isset($_SESSION["alerta"])) {
    $alerta = $_SESSION["alerta"];
    unset($_SESSION["alerta"]);
}
if (isset($_SESSION["sucesso"])) {
    $sucesso = $_SESSION["sucesso"];
    unset($_SESSION["sucesso"]);
}

//Tratar restauração de backup
if (isset($_GET["id"])) {
    $retorno = $oProdutos->restaurarBackupProdutos($_GET["id"]);
    if ($retorno === true) {
        $sucesso = "Backup restaurado com sucesso";
        $_SESSION["sucesso"] = $sucesso;
    } else {
        $alerta = 'Ocorreram algumas falhas ao restaurar o backup.<br>'.implode('<br>', $retorno);
        $_SESSION["alerta"] = $alerta;
    }
    header('Location: backup_produtos.php');
}
//Tratar execução de backups
if (isset($_GET["backup"])) {
    if ($oProdutos->realizarBackupProdutos()) {
        $sucesso = "Backup criado com sucesso";
        $_SESSION["sucesso"] = $sucesso;
    } else {
        $alerta = "Falha ao realizar backup. Backup não realizado";
        $_SESSION["alerta"] = $alerta;
    }
    header('Location: backup_produtos.php');
}

$conteudo = $oProdutos->listarBackupProdutos();

echo $twig->render('backup_produtos.html', [
    "titulo"            => "LivresBS - Consolidar Entregas",
    "menu_datas"        => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
    "conteudo"          => $conteudo,
    "alerta"            => nl2br($alerta),
    "sucesso"           => $sucesso
    ]);

/*if (!isset($_SESSION["data_id"]) || $_SESSION["data_id"] == "") {
    echo $twig->render('backup_produtos.html', [
        "titulo"            => "LivresBS - Consolidar Entregas",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "alerta"          => "Selecione uma data"
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
*/
?>