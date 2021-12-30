<?php
$levelRequired=20000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

setlocale(LC_TIME, 'pt_BR');

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);//

$filter = new \Twig\TwigFilter('stripslashes', 'stripslashes');
$twig->addFilter($filter);

$alerta = "";
$sucesso = "";
if (isset($_SESSION["alerta"])) { 
    $alerta = $_SESSION["alerta"];
    unset($_SESSION["alerta"]);
};
if (isset($_SESSION["sucesso"])) {
    $sucesso = $_SESSION["sucesso"];
    unset($_SESSION["sucesso"]);
};


if (!isset($_SESSION["data_consulta"]) || $_SESSION["data_consulta"] == "") {
    echo $twig->render('ecoholerites_rel.html', [
        "titulo"            => "LivresBS - Consolidar Entregas",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "alerta"          => "Selecione uma data"
        ]);
        
} else {

    $oEcoholerite = new Ecoholerite;
    $data = DateTime::createFromFormat('Y-m-d H:i', $_SESSION["data_consulta"]);
    
    $totalDia = $oEcoholerite->relatorioPagamento($data->format('Y-m-d'), $data->format('Y-m-d'), 1, 1);
    $totalTrabalho = $oEcoholerite->relatorioPagamento($data->format('Y-m-d'), $data->format('Y-m-d'), 1, 0);
    $totalEntregas = $oEcoholerite->relatorioPagamento($data->format('Y-m-d'), $data->format('Y-m-d'), 0, 1);
    $totalMes = $oEcoholerite->relatorioPagamento($data->format('Y-m-1'), $data->format('Y-m-t'), 1, 1);

    if ($totalDia) $conteudo["Total do dia " . $data->format('d/m/Y')] = $totalDia;
    if ($totalTrabalho) $conteudo["Apenas ecohoras do dia " . $data->format('d/m/Y')] = $totalTrabalho;
    if ($totalEntregas) $conteudo["Apenas ecopedaladas " . $data->format('d/m/Y')] = $totalEntregas;
    if ($totalMes) $conteudo["Total mês " . $data->format('F')] = $totalMes;

    if (!isset($conteudo)) $conteudo="";
    
    echo $twig->render('ecoholerites_rel.html', [
        "titulo"                => "LivresBS - Ecoholerite",
        "alerta"                => $alerta,
        "sucesso"               => $sucesso,
        "admin_logado"          => $_SESSION["id"],
        "conteudo"              => $conteudo,
        "data_selecionada"      => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        ]);
}
    
?>