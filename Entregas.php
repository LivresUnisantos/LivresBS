<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "config.php"
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Orgânico</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
  </head>
  <body>
<?php
if (!isset($_GET["password"])) {
?>
<div class="grid-x grid-padding-x">
	<div class="large-4">
		<form action="" method="GET">
			<label>Senha</label>
			<input type="password" name="password" id="password" />
			<input type="submit" name="Enviar" id="Enviar" value="Enviar" />
		</form>
	</div>
	<div class="large-8">
	</div>
</div>
<?php
	exit;
} else {
	if ($_GET["password"] != "decrescimento") {
		exit ("Senha incorreta");
	}
}

if (!isset($_GET["frequencia"]) || !isset($_GET["dia"]) || !isset($_GET["mes"]) || !isset($_GET["ano"])) {
?>
	<div class="grid-x grid-padding-x">
		<div class="large-4">
			<form action="" method="GET">
				<label>Dia da entrega</label>
				<select id="dia" name="dia">
					<?php
					for ($i = 1; $i <= 31; $i++) {
						echo '<option value="'.$i.'">'.$i.'</option>';
					}
					?>
				</select>
				<label>Mês</label>
				<select id="mes" name="mes">
					<?php
					for ($i = 1; $i <= 12; $i++) {
						echo '<option value="'.$i.'">'.$i.'</option>';
					}
					?>
				</select>
				<label>Ano</label>
				<select id="ano" name="ano">
					<?php
					for ($i = 2019; $i <= 2025; $i++) {
						echo '<option value="'.$i.'">'.$i.'</option>';
					}
					?>
				</select>
				<label>Tipo Frequência</label>
				<select name="frequencia" id="frequencia">
					<option value="Semanal">Semanal</option>
					<option value="Quinzenal">Quinzenal</option>
					<option value="Mensal">Mensal</option>
				</select>
				<input type="hidden" name="password" id="password" value="decrescimento" />
				<input type="submit" name="Enviar" id="Enviar" value="Enviar" />
			</form>
		</div>
		<div class="large-8">
		</div>
	</div>
	<?php
	exit();
}
$frequencia = $_GET["frequencia"];
$dataEntrega = $_GET["mes"].'/'.$_GET["dia"].'/'.$_GET["ano"];
$dataEntrega = strtotime($dataEntrega);
echo "Entrega <b><u>".$frequencia."</u></b> de ".date("d/M/Y",$dataEntrega)."<br>";
echo '<a href="?password=decrescimento">Clique aqui para escolher outra data</a>';
$tabela="Respostas";
//$conn = new PDO("mysql:host=localhost;dbname=id1608716_livres","id1608716_henderson","190788");
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$counter=0;
$sql = "SELECT * FROM Consumidores WHERE ativo = 1 ORDER BY Consumidor ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
?>
<table style="vertical-align:top;">
<?php
foreach ($rs as $row) {
	$sql2 = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE IDConsumidor = ".$row["id"];
	$sql2 .= " AND produtos.previsao <= '".date("Y-m-d",$dataEntrega)."'";
	$sql2 .= " AND Pedidos.Quantidade > 0";
	if ($frequencia == "Semanal") {
		$sql2 .= " AND Pedidos.frequencia = 'Semanal'";
	} else {
	    if ($frequencia == "Mensal") {
		    $sql2 .= " AND Pedidos.frequencia = 'Mensal'";
	    }
	}
	$sql2 .= " ORDER BY produtos.nome";
	$st2 = $conn->prepare($sql2);
	$st2->execute();
	$rs2=$st2->fetchAll();
	if (count($rs2) > 0) {
	    $counter++;
		if ($counter % 2 == 1) {
			echo '<tr><td style="vertical-align:top;">';
		} else {
			echo '<td style="vertical-align:top;">';
		}
		echo "<b>Consumidor: ".$row["consumidor"]." (código: ".$row["id"].")</b><br>";
		echo "Email: ".$row["email"]."<br>";
		echo "CPF: ".$row["cpf"]."<br>";
		echo "Forma Entrega: ".$row["forma_entrega"]."<br>";
		echo "Cota: R$".$row["cota_imediato"].",00<br>";
		echo "Pedido: <br>";
		foreach ($rs2 as $row2) {
			echo $row2["Quantidade"]." ".$row2["unidade"]." x ".$row2["nome"]." (código ".$row2["id"].")<br>";
		}
		echo "</td>";
		if ($counter % 2 == 0) {
			echo "</tr>";
		}
	}
}
?>
</table>
  </body>
</html>
