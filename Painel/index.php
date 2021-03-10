<?php
require_once 'acesso.php';
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

echo $twig->render('index.html', [
    "titulo"            => "LivresBS",
    "menu_datas"        => $calendario->listaDatas(),
    //"data_selecionada"  => (isset($_GET['data']) ? $_GET["data"] : ""),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
]);
?>