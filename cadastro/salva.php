<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
    </head>
    <body>
<?php
include "../config.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);

//Cadastrar consumidor
$consumidor = ucwords(strtolower(trim($_POST["consumidor"])));
$consumidor = str_replace("'","''",$consumidor);
$nascimento=$_POST["nascimento"];
$email=trim(strtolower($_POST["email"]));
$cpf=trim($_POST["cpf"]);
$cpf=str_replace(".","",$cpf);
$cpf=str_replace(",","",$cpf);
$cpf=str_replace("-","",$cpf);
$cpf=str_replace(" ","",$cpf);
$endereco=ucwords(strtolower(trim($_POST["endereco"])));
$endereco = str_replace("'","''",$endereco);
$telefone=trim($_POST["telefone"]);
$telefone=str_replace(".","",$telefone);
$telefone=str_replace(",","",$telefone);
$telefone=str_replace("-","",$telefone);
$telefone=str_replace(" ","",$telefone);
$preferencia_entrega=$_POST["preferencia_entrega"];
/* AJUSTE TEMPORÁRIO PARA ALOCAR NOVOS CADASTROS AO G5 e G6 AUTOMATICAMENTE */
/*if ($preferencia_entrega == "Sábado") {
    $comunidade = 5;
} else {
    $comunidade = 6;
}*/
$sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
$st=$conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0) {
	echo "Seu CPF já está cadastrado e seu pedido não foi salvo.<br>";
	echo 'Consulte seu pedido cadastrado em <a href="../Cestas?cpf='.$_POST["cpf"].'" target="_blank">'.$_SERVER["HTTP_HOST"].'/Cestas</a><br>';
	echo "Entre em contato com os coordenadores do Livres caso queira alterá-lo.";
	exit();
}

$sql = "INSERT INTO Consumidores (consumidor, nascimento, email, cpf, endereco, telefone,preferencia_entrega) VALUES ('".$consumidor."','".$nascimento."','".$email."','".$cpf."','".$endereco."','".$telefone."','".$preferencia_entrega."')";
//$sql = "INSERT INTO Consumidores (consumidor, email, cpf, endereco, telefone,preferencia_entrega, comunidade) VALUES ('".$consumidor."','".$email."','".$cpf."','".$endereco."','".$telefone."','".$preferencia_entrega."', ".$comunidade.")";
/* FIM AJUSTE */
$st=$conn->prepare($sql);
$st->execute();
$idConsumidor=$conn->lastInsertId();

if ($idConsumidor == 0) {
	echo "Erro ao cadastrar seu pedido. Por favor, tente novamente.";
	echo "<br>Não foi possível salvar os dados de consumidor";
	exit();
} else {
	//Cadastrar pedido, varrendo toda a lista de produtos cadastrada no banco
	$sql = "SELECT * FROM produtos WHERE carrinho = 1 ORDER BY categoria,nome ASC";
	$st = $conn->prepare($sql);
	$st->execute();
	$rs=$st->fetchAll();

	$erros = "";
	$count=0;
	foreach ($rs as $row) {
		$idProduto=$row["id"];
		$multiplicador=$row["multiplicador_unidade2"];
		if (isset($_POST["prod_".$row["id"]])) {
    		$quantidade=intval($_POST["prod_".$row["id"]]);
    		if (is_null($quantidade) || $quantidade=="") {
    		    $quantidade=0;
    		}
    		$frequencia=$_POST["freq_prod_".$row["id"]];
    		if ($quantidade > 0 && strlen($frequencia)) {
    		    $quantidade = $quantidade * $multiplicador;
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
	}
	echo "Olá ".$consumidor.",<br><br>";
	echo "Seu pedido com ".$count." produto".($count > 1 ? "s" : "")." foi cadastrado com sucesso.";
	if (strlen($erros)>0) {
		echo "<br><br>";
		echo "Os produtos abaixo não foram cadastrados. Entre em contato com o Livres para incluí-los manualmente.";
		echo $erros;
	}
}
?>
