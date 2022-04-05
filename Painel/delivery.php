<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$levelRequired=1000;
include "../config.php";
include "helpers.php";
include "acesso.php";


require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Orgânico</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link rel="stylesheet" href="painel.css">
    <style>
        .debito {
            color: #ff0000;
        }
        .credito {
            color: #34ad5c;
        }
    </style>
  </head>
  <body>
  <?php
$alerta = "";
if (isset($_GET["data"])) {
	$getData = $livres->dataPelaString($_SESSION["data_consulta"]);
	$dataStr = $livres->dataPeloID($getData);
	if (!$dataStr) {
		$alerta = "Selecione uma data";
	}
} else {
	$alerta = (isset($_SESSION['data_id']) ? "" : "Selecione uma data");
}

echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
	"frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
	"alerta"			=> $alerta
]);

if ($alerta != "") {
	echo '<div class="alert alert-danger" role="alert">Selecione uma data com entrega planejada</div>';
} else {

	$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);

	if (isset($_GET["data"])) {
		echo '<p>';
		if (!isset($_GET["imprimir"])) {
			echo '<a href="?data='.$getData.'&imprimir=1">Habilitar Modo Impressão</a>';
		} else {
			echo '<a href="?data='.$getData.'">Habilitar Modo de Edição</a>';
		}
	}
	echo '</p>';
	$sqlListaPedidosDia = "SELECT * FROM pedidos_consolidados ped LEFT JOIN Consumidores cons on ped.consumidor_id = cons.id  ";
	//$sqlListaPedidosDia .= "WHERE pedido_data = '".$dataStr."' AND cons.comunidade <> 0 AND ped.consumidor_id IS NOT NULL ";
	$sqlListaPedidosDia .= " WHERE pedido_data = '".$dataStr."' AND ped.consumidor_id IS NOT NULL AND cons.consumidor NOT LIKE '%Vendas loja não consumidores%'";
	$sqlListaPedidosDia .= " ORDER BY cons.comunidade, cons.consumidor";
	
	if (!empty($_POST)) {
		$sql = $sqlListaPedidosDia;
		
		$st = $conn->prepare($sql);
		$st->execute();
		
		$rs = $st->fetchAll();
		foreach ($rs as $row) {
			$id = $row["pedido_id"];
			/* ARMENG TEMPORÁRIO PARA CONVERTER O FORMATO ANTIGO DE DESCRIÇÃO DE DELIVERY PARA O NOVO */
			$value = $_POST[$id];
			if ($value == 'Não') {
					$delivery = 1;
			} else {
				if ($value == 'Sim') {
					$delivery = 2;
				} else {
					$delivery = 3;
				}
			}
			/* FIM ARMENG */
			$valor = $_POST["valor_entrega_".$id];
			$valor = str_replace(",",".",$valor);
			$valor = str_replace("R$","",$valor);
			$sql = "UPDATE pedidos_consolidados SET pedido_retirada = ".$delivery.", pedido_entrega_valor = ".$valor." WHERE pedido_id = ".$id;
			setLog("log.txt","Alteração Delivery Consumidor",$sql);
			$st = $conn->prepare($sql);
			if ($st->execute()) {
				//echo "Alteração realizada";
			} else {
				echo "Falha ao realizar alteração";
			}
		}
	}
		
	if (!isset($_GET["data"])) {
		echo "Selecione uma data";
	} else {
		if (strlen($getData) > 0) {

			$sql = "SELECT * FROM pedidos_consolidados ped LEFT JOIN Consumidores cons on ped.consumidor_id = cons.id ";
			//$sql .= "WHERE pedido_data = '".$dataStr."' AND cons.comunidade <> 0 AND ped.consumidor_id IS NOT NULL ";
			$sql .= " WHERE pedido_data = '".$dataStr."' AND ped.consumidor_id IS NOT NULL AND cons.consumidor NOT LIKE '%Vendas loja não consumidores%'";
			$sql .= " ORDER BY cons.comunidade, cons.consumidor";
			$st = $conn->prepare($sql);
			$st->execute();
			if ($st->rowCount() > 0) {
				if (!isset($_GET["imprimir"])) {
					echo '<form method="POST" action="">';
				}
				echo '<table border="1">';
				echo '<tr>';
				echo '<td>Consumidor</td>';
				echo '<td>Comunidade</td>';
				echo '<td>Telefone</td>';
				echo '<td>Endereço</td>';
				echo '<td>Crédito/Débito</td>';
				echo '<td>Delivery</td>';
				echo '<td>Valor Entrega</td>';
				echo '</tr>';
				$rs = $st->fetchAll();
				foreach ($rs as $row) {
					//$com = $row["comunidade"];
					/* ARMENG TEMPORÁRIO PARA CONVERTER O FORMATO ANTIGO DE DESCRIÇÃO DE DELIVERY PARA O NOVO */
					if ($row["pedido_retirada"] == 1) {
							$delivery = "Não";
					} else {
						if ($row["pedido_retirada"] == 2) {
							$delivery = "Sim";
						} else {
							$delivery = "";
						}
					}
					/* FIM ARMENG */
					$idConsumidor = $row["consumidor_id"];
					echo '<tr>';
					echo '<td>'.ucwords(mb_strtolower($row["consumidor"]),'UTF-8').'</td>';
					echo '<td>'.$row["comunidade"].'</td>';
					echo '<td>'.$row["telefone"].'</td>';
					echo '<td>'.$row["pedido_endereco"].'</td>';
					if (!isset($_GET["imprimir"])) {
						echo '<td class="'.($row["credito"] < 0 ? "debito" : "credito").'">'.($row["credito"] < 0 ? "-" : "").'R$'.number_format(abs($row["credito"]),2,",",".").'</td>';
						echo '<td>'.generateMenu($delivery,$row["pedido_id"],$row["pedido_id"]).'</td>';
						echo '<td><input type="text" name="valor_entrega_'.$row["pedido_id"].'" id="valor_entrega_'.$row["pedido_id"].'" value="'.number_format($row["pedido_entrega_valor"],2,",","").'" /></td>';
					} else {
						echo '<td class="'.($row["credito"] < 0 ? "debito" : "credito").'">'.($row["credito"] < 0 ? "-" : "").'R$'.number_format(abs($row["credito"]),2,",",".").'</td>';
						echo '<td>'.$delivery.'</td>';
						echo '<td>'.number_format($row["pedido_entrega_valor"],2,",","").'</td>';
					}
					echo '</tr>';
				}
				if (!isset($_GET["imprimir"])) {
					echo '<tr>';
					echo '<td colspan="2">';
					echo '<input type="submit" name="submit" value="Salvar" />';
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
				if (!isset($_GET["imprimir"])) {
					echo '</form>';
				}
			}
		}
	}
}

function generateMenu($selected, $name, $id) {

    $opcoes = array("","Sim","Não","Não sei ainda");
    $html = "";
    $html .= '<select id="'.$id.'" name="'.$name.'">';
    foreach ($opcoes as $opcao) {
        if (mb_strtolower($opcao,'UTF-8') == mb_strtolower($selected,'UTF-8')) {
            $html .= '<option value="'.$opcao.'" selected="selected">'.$opcao.'</option>';
        } else {
            $html .= '<option value="'.$opcao.'">'.$opcao.'</option>';
        }
    }
    $html .= '</select>';
    return $html;
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>

<link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.js"></script>
<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.pt-BR.js"></script>

<script>
$(function () {
	//DATEPICKER CONFIG
	var start = new Date(), prevDay, startHours = 0;

	// ÚLTIMO HORÁRIO
	start.setHours(0);
	start.setMinutes(0);

	var dataPicker = $('.jwc_datepicker_start').datepicker({
		timepicker: true,
		startDate: start,
		minHours: startHours
	}).data('datepicker');
});
</script>
  </body>
</html>