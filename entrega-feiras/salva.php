<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    </head>
    <body>
<?php
include "../config.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);

//Cadastrar consumidor
$consumidor = ucwords(strtolower(trim($_GET["consumidor"])));
$email=trim(strtolower($_GET["email"]));
$cpf=trim($_GET["cpf"]);
$cpf=str_replace(".","",$cpf);
$cpf=str_replace(",","",$cpf);
$cpf=str_replace("-","",$cpf);
$cpf=str_replace(" ","",$cpf);
$endereco=ucwords(strtolower(trim($_GET["endereco"])));//
$telefone=trim($_GET["telefone"]);
$telefone=str_replace(".","",$telefone);
$telefone=str_replace(",","",$telefone);
$telefone=str_replace("-","",$telefone);
$telefone=str_replace(" ","",$telefone);
$sql = "INSERT INTO ContatosFeiras (consumidor, email, cpf, endereco, telefone) VALUES ('".$consumidor."','".$email."','".$cpf."','".$endereco."','".$telefone."')";
$st=$conn->prepare($sql);
$st->execute();
$idConsumidor=$conn->lastInsertId();

if ($idConsumidor == 0) {
	echo "Erro ao cadastrar seus dados. Por favor, tente novamente.";
	exit();
}
echo "Olá ".$consumidor.",<br><br>";
echo "Seus dados foram cadastrados com sucesso.";
?>
