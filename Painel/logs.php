<?php
$levelRequired=20000;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config.php";
include "acesso.php";
include "helpers.php";
include "menu.php";
?>
<!DOCTYPE html>
<!--https://codepen.io/colorlib/full/rxddKy-->
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Livres BS - Consumo Consciente</title>
</head>
<body>
<?php
echo "Log Consumidores";
$arquivo_tmp = "../Consumidor/log.txt";
$dados = file($arquivo_tmp);
$prefix = "";
foreach($dados as $linha) {
	//Retirar os espaços em branco no inicio e no final da string
	$linha = trim($linha);
	$linha = htmlspecialchars($linha);
	echo $prefix.$linha."<br>";
	if (strlen($linha) == 0) {
	    $prefix = "-";
	} else {
	    $prefix = "";
	}
}
?>
</body>
</html>