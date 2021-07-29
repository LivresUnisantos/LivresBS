<?php
require_once "../includes/autoloader.inc.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../Painel/helpers.php";
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Orgânico</title>
    <link rel="stylesheet" href="../css/foundation.css">
    <link rel="stylesheet" href="../css/app.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
        .form-inline {
          display: flex;
          flex-flow: row;
          align-items: center;
          min-width: 100px;
        }
        .form-inline label {
          margin: 5px 10px 5px 0;
        }
        
        .form-inline select {
            width: 150px;
            margin: 0;
        }
        
        .form-inline input {
          vertical-align: middle;
          margin: 5px 10px 5px 10px;
          padding: 10px;
          background-color: #fff;
          border: 1px solid #ddd;
        }
        
        .form-inline button {
          padding: 10px;
          background-color: dodgerblue;
          border: 1px solid #ddd;
          color: white;
          margin-left: 10px;
        }
        
        .form-inline button:hover {
          background-color: royalblue;
        }
    </style>
  </head>
  <body>
<?php
/*
//Código desabilitado quando entregas começaram a acontecer terças e sábados
//Próxima entrega é encontrada buscando próximo dia de entrega a partir do dia de hoje
//Desde que haja entrega do grupo da pessoa no dia
//Identificação do dia movida para mais abaixo
$hoje = date("Y-m-d");
$diaSemana = date('N', strtotime($hoje));
if ($diaSemana == 6) {
	$sabado = strtotime($hoje);
	$proximoSabado = date("Y-m-d", $sabado);
} else {
	if ($diaSemana == 7) {
		$sabado = strtotime("+6 day", strtotime($hoje));
		$proximoSabado = date('Y-m-d', $sabado);
	} else {
		$sabado = strtotime("+".(6-$diaSemana)." day", strtotime($hoje));
		$proximoSabado = date('Y-m-d', $sabado);
	}
}
*/
if (!isset($_GET["cpf"])) {
?>
<div class="grid-container">
		<div class="row justify-content-center ">
			<div class="large-4 " style='margin-top: 10%; margin-left: 50%'>
				Consulte sua cesta com produtos disponíveis a partir da próxima entrega.
				<form action="" method="GET">
					<label for="cpf" />CPF</label>
					<input  data-validation="cpf" style="width: 45%" type="text"  placeholder="Preencha seu CPF" value="" name="cpf" id="cpf" />
					<input type="submit" value="Enviar" />
				</form>
			</div>
			
		</div>
	</div> ?>
<?php
} else {
	$cpf = $_GET["cpf"];
	$cpf=str_replace(".","",$cpf);
	$cpf=str_replace(",","",$cpf);
	$cpf=str_replace("-","",$cpf);
	

$dsn = 'mysql:dbname=livres;host=127.0.0.1';
$user = 'root';
$password = 'toor';

$conn = new PDO($dsn, $user, $password,
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
	//Identificar consumidor
    $sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 0) {
    	echo "<div class='row justify-content-center' style='margin-top: 10%'";
    	echo "<p>Consumidor não encontrado</p>";
    	echo '<a class="btn btn-small btn-success" style="width:25%" href="myIndex.php">Voltar</a>';
    	echo "</div>";
    	exit();
    }
    $rs=$st->fetchAll();

	//Consumidor identificado, identificar próxima entrega
	$consumidor=$rs[0]["consumidor"];
	$idConsumidor=$rs[0]["id"];
	$cota=$rs[0]["cota_imediato"];
    $comunidade=$rs[0]["comunidade"];
    $hoje = date("Y-m-d");

    /*
	Desativada trava para mostrar cesta de todos os consumidores, mesmo sem grupo/cesta ativa
	$sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' AND LENGTH(".$comunidade."acomunidade) = 3 AND ".$comunidade."acomunidade <> '000' ORDER BY data ASC";
    $st = $conn->prepare($sql);
    $st->execute();
    
    if ($st->rowCount() == 0) {
        echo "Você não possui nenhuma entrega prevista. Caso ache isso um equívoco, gentileza entrar em contato conosco!";
        exit();
    }*/
    
    $sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' ORDER BY data ASC";

    $st = $conn->prepare($sql);
    $st->execute();

  	$senha = "SELECT senha FROM Consumidores WHERE cpf='".$cpf."'";

    $checksenha = $conn->prepare($senha);
    $checksenha->execute();


		if ($checksenha->rowCount() == 0) {
        echo "Não há senha";
        exit();
    }
    else {
    	echo "Com senha";
    	echo '	<input  data-validation="cpf" style="width: 45%" type="text"  placeholder="Preencha seu CPF" value="" name="cpf" id="cpf" />';
    }

		// if ($st->rowCount() == 0) {
  //       echo "Não há nenhuma entrega prevista!";
  //       exit();
  //   }
    
    $rsProxima =$st->fetch();
    $proximaEntrega = $rsProxima["data"];
    $idProximaEntrega = $rsProxima["id"];
    

	
$dsn = 'mysql:dbname=livres;host=127.0.0.1';
$user = 'root';
$password = 'toor';

$conn = new PDO($dsn, $user, $password,
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
	//$cpf="01797985884";

	function ccase($str) {
		return ucwords(mb_strtolower($str,'UTF-8'));
	}

	$sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."' ORDER BY id DESC";
	$st = $conn->prepare($sql);
	$st->execute();
	if ($st->rowCount() == 0) {
		echo "<p>Consumidor não encontrado</p>";
		echo '<a href=".">Voltar</a>';
		exit();
	}


?>

<script src="../js/vendor/jquery.js"></script>
<script src="../js/vendor/what-input.js"></script>
<script src="../js/vendor/foundation.js"></script>
<script src="../js/app.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
$.validate({
  lang: 'pt',
  modules : 'brazil'
});
</script>
</body>
</html>