<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

if (isset($_POST["cpf"])) {
    $cpf = $_POST["cpf"];
    $cpf=str_replace(".","",$cpf);
	$cpf=str_replace(",","",$cpf);
	$cpf=str_replace("-","",$cpf);
	$cpf=str_replace(" ","",$cpf);
	
	$oCons = new Consumidores();
	$cons = $oCons->encontrarPorCPF($cpf);
	if (!$cons) {
	    $alerta = "Consumidor não encontrado";
	} else {
	    session_start();
	    $_SESSION["id_consumidor"] = $cons["id"];
	    $_SESSION["cpf"] = $cons["cpf"];
	    $_SESSION["grupo"] = $cons["comunidade"];
	    header("Location: index2.php");
	}
}

$livres = new Livres();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/index');
$twig = new \Twig\Environment($loader, ['debug' => false]);

echo $twig->render('logar.html', [
        "alerta"        => (isset($alerta) ? $alerta : "")
    ]);
?>