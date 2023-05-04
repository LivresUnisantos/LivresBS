<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/produtos');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$oProdutos = new Produtos();
$oListas = new Listas();

$ordens = array("Nome", "Preço");
//$lista = 1;

$categoria = "";
$filtro = "";
$ordem = $ordens[0];
$categoria_display = "todos";
$ordem_display = $ordens[0];

//echo $_GET["lista"]." / ".$_GET["categoria"]." / ".$_GET["ordem"];

$url = $_SERVER["HTTP_HOST"]; //livresbs.com.br
$file_path = $_SERVER["PHP_SELF"];
$path = substr($file_path,0,strrpos($file_path, "/"));
$base_url= $_SERVER["REQUEST_SCHEME"] ."://". $url ."/". str_replace("/","",$path)."/";

if (isset($_GET["lista"])) {
    if ($_GET["lista"] != "") {
        $lista = $_GET["lista"];
    }
}
if (isset($_GET["categoria"])) {
    if ($_GET["categoria"] != "" && $_GET["categoria"] != "todos") {
        $categoria = $_GET["categoria"];
        $categoria_display = $_GET["categoria"];
    }
}
if (isset($_GET["f"])) {
    if ($_GET["f"] != "") {
        $filtro = $_GET["f"];
    }
}
if (isset($_GET["ordem"])) {
    if ($_GET["ordem"] != "") {
        $ordem = $_GET["ordem"];
        $ordem_display = $_GET["ordem"];
        $ordem = strtolower($ordem);
        $ordem = str_replace("ç","c",$ordem);
        if ($ordem != "nome" && $ordem != "preco") {
            $ordem=$ordens[0];
            $ordem_display=$ordem;
        }
    }
}

$categorias = $oProdutos->categorias();
$produtos = $oListas->produtosListaAtivos($lista, $ordem, $categoria, $filtro);

echo $twig->render('index2.html', [
    "base_url"          => $base_url,
    "categorias"        => $categorias,
    "produtos"          => $produtos,
    "ordem"             => $ordem_display,
    "categoria"         => $categoria_display,
    "lista"             => $lista,
    "ordens"            => $ordens,
    "filtro"            => $filtro
    ]);
?>