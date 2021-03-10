<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
    <head>
    <meta charset="utf-8">
    </head>
    <body>
<?php
include "config.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
/*
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=livresbs_demo",$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
*/

//Buscar consumidor
$cpf=trim($_GET["cpf"]);
$sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
$st=$conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
$idConsumidor=$rs[0]["id"];//->lastInsertId();
$consumidor = $rs[0]["consumidor"];

if ($idConsumidor == 0) {
	echo "Erro ao cadastrar seu pedido. Por favor, tente novamente.";
	exit();
} else {
	//Cadastrar pedido, varrendo toda a lista de produtos cadastrada no banco
	$sql = "SELECT * FROM produtos WHERE mensal = 1 ORDER BY categoria,nome ASC";
	$st = $conn->prepare($sql);
	$st->execute();
	$rs=$st->fetchAll();

	$erros = "";
	$count=0;
	foreach ($rs as $row) {
		$idProduto=$row["id"];
		$quantidade=intval($_GET["prod_".$row["id"]]);
		$frequencia=$_GET["freq_prod_".$row["id"]];
		//echo $idProduto." - ".$quantidade." - ".$frequencia."<br>";
		if ($quantidade > 0 && strlen($frequencia)) {
			$sql = "INSERT INTO Pedidos (IDConsumidor,IDProduto,Quantidade,Frequencia) VALUES (".$idConsumidor.",".$idProduto.",".$quantidade.",'".$frequencia."')";
			$st=$conn->prepare($sql);
			if ($st->execute()) {
				$count++;
			} else {
				if (strlen($erros)>0) {
					$erros.="<br>";
				}
				$erros .= "Erro ao cadastrar o seu pedido do produto: ".$row["nome"];
			}
		} else {
			if ($quantidade > 0) {
				if (strlen($erros)>0) {
					$erros.="<br>";
				}
				$erros.="Frequência do pedido não preenchida para o seguinte produto: ".$$row["nome"];
			}
		}
	}
	echo "Olá ".$consumidor.",<br><br>";
	echo "Seu pedido com ".$count." produto".($count > 1 ? "s" : "")." foi cadastrado com sucesso.";
	if (strlen($erros)>0) {
		echo "<br><br>";
		echo "Os produtos abaixo não foram cadastrados. Entre em contato com o Livres para incluílos manualmente.";
		echo $erros;
	}
}
?>
