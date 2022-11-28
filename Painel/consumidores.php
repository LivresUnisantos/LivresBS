<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$oConsumidores = new Consumidores();

$consumidores = $oConsumidores->consumidoresTodos("ativo desc, comunidade asc, consumidor asc");

echo $twig->render('consumidores.html', [
    "titulo"            => "LivresBS - Editar Consumidores",
    "menu_datas"        => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
    "level_user"        => $_SESSION["level"],
    "level_write"       => 15000,
    "conteudo"          => $consumidores,
    "grupos"            => $livres->getParametro("grupos")
    ]);
?>