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
if (isset($_GET["data"])) {
	$getData = $livres->dataPelaString($_SESSION["data_consulta"]);
}

echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
	"frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);
?>
<?php
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
if (!empty($_POST)) {
    foreach ($_POST as $id=>$value) {
        if ($id != "submit") {
            //if (strlen($value) > 0) {
                /*$sql = "SELECT * FROM PedidosVar WHERE idConsumidor = ".$id." AND idCalendario = ".$getData;
                $st = $conn->prepare($sql);
                $st->execute();
                echo "<p>";
                if ($st->rowCount() == 0) {
                    $sql = "INSERT INTO PedidosVar (idConsumidor,idCalendario,delivery) VALUES (".$id.",".$getData.",'".$value."')";
                    setLog("log.txt","Inclusão Delivery Consumidor",$sql);
                    $st = $conn->prepare($sql);
                    if ($st->execute()) {
                        echo "Alteração realizada";
                    } else {
                        echo "Falha ao realizar alteração";
                    }
                } else {
                    $sql = "UPDATE PedidosVar SET delivery = '".$value."' WHERE idConsumidor = ".$id." AND idCalendario = ".$getData;
                    setLog("log.txt","Alteração Delivery Consumidor",$sql);
                    $st = $conn->prepare($sql);
                    if ($st->execute()) {
                        echo "Alteração realizada";
                    } else {
                        echo "Falha ao realizar alteração";
                    }
                }
                echo "</p>";
                */
                /* ARMENG TEMPORÁRIO PARA CONVERTER O FORMATO ANTIGO DE DESCRIÇÃO DE DELIVERY PARA O NOVO */
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
                $sql = "UPDATE pedidos_consolidados SET pedido_retirada = ".$delivery." WHERE pedido_id = ".$id;
                setLog("log.txt","Alteração Delivery Consumidor",$sql);
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    //echo "Alteração realizada";
                } else {
                    echo "Falha ao realizar alteração";
                }
            //}
        }
    }
}
    
if (!isset($_GET["data"])) {
	echo "Selecione uma data";
} else {
	if (strlen($getData) > 0) {
	    $sql = "SELECT * FROM Calendario WHERE id = ".$getData;
	    $st = $conn->prepare($sql);
	    $st->execute();
	    $rs = $st->fetch();
	    $dataEntrega=$rs["data"];
	    //$frequencia1=$rs["1acomunidade"];
	    //$frequencia2=$rs["2acomunidade"];
	    $frequencias = getFrequencias($conn,$getData);
	    
	    //$sql = getSQLPedidoSemana(strtotime($dataEntrega),$frequencia1,$frequencia2);
	    $sql = getSQLPedidoSemana(strtotime($dataEntrega),$frequencias,1);
	    $st = $conn->prepare($sql);
	    $st->execute();
	    $rs = $st->fetchAll();
	    foreach ($rs as $row) {
	        if (!isset($entregas)) {
	            $entregas[$row["IDConsumidor"]]["Semanal"] = 0;
	            $entregas[$row["IDConsumidor"]]["Quinzenal"] = 0;
	            $entregas[$row["IDConsumidor"]]["Mensal"] = 0;
	        } else {
	            if (!array_key_exists($row["IDConsumidor"],$entregas)) {
	                $entregas[$row["IDConsumidor"]]["Semanal"] = 0;
	                $entregas[$row["IDConsumidor"]]["Quinzenal"] = 0;
	                $entregas[$row["IDConsumidor"]]["Mensal"] = 0;
	            }
	        }
	        $entregas[$row["IDConsumidor"]][$row["Frequencia"]]++;
	    }
	    
		$sql = 'SELECT Consumidores.id AS ConsumidorId, Consumidores.*, PedidosVar.* FROM Consumidores LEFT JOIN PedidosVar';
		$sql .= ' ON PedidosVar.idConsumidor = Consumidores.id AND PedidosVar.idCalendario = '.$getData.' WHERE Consumidores.ativo=1 ORDER BY consumidor';
		
		$st = $conn->prepare($sql);
		$st->execute();
		$rs = $st->fetchAll();
		foreach ($rs as $row) {
		    $lEnderecos[$row["idConsumidor"]] = $row["endereco_entrega"];
		}
		
		$sql = "SELECT ped.pedido_id as pedido_id, cons.id as ConsumidorId, cons.consumidor as consumidor, cons.telefone as telefone, ped.pedido_retirada as delivery,
		        cons.comunidade as comunidade, cons.credito as credito,
		        ped.pedido_endereco as endereco_entrega FROM pedidos_consolidados ped
                LEFT JOIN Consumidores cons
                ON cons.id = ped.consumidor_id
                LEFT JOIN Calendario cal
                ON cal.`data` = ped.pedido_data
                WHERE cons.consumidor IS NOT NULL
                AND cal.id=" . $getData . "
                ORDER BY cons.consumidor";
		$st = $conn->prepare($sql);
		$st->execute();
		if ($st->rowCount() > 0) {
		    if (!isset($_GET["imprimir"])) {
		        echo '<form method="POST" action="">';
		    }
		    echo '<table border="1">';
            echo '<tr>';
            echo '<td>Consumidor</td>';
            echo '<td>Telefone</td>';
            echo '<td>Endereço</td>';
            echo '<td>Crédito/Débito</td>';
            echo '<td>Delivery</td>';
            echo '</tr>';
		    $rs = $st->fetchAll();
		    foreach ($rs as $row) {
		        $com = $row["comunidade"];
		        /* ARMENG TEMPORÁRIO PARA CONVERTER O FORMATO ANTIGO DE DESCRIÇÃO DE DELIVERY PARA O NOVO */
                if ($row["delivery"] == 1) {
                        $delivery = "Não";
                } else {
                    if ($row["delivery"] == 2) {
                        $delivery = "Sim";
                    } else {
                        $delivery = "";
                    }
                }
                /* FIM ARMENG */
		        $semanal = (!array_key_exists($row["ConsumidorId"],$entregas) || $entregas[$row["ConsumidorId"]]["Semanal"] == 0) ? false : true;
		        $quinzenal = (!array_key_exists($row["ConsumidorId"],$entregas) || $entregas[$row["ConsumidorId"]]["Quinzenal"] == 0) ? false : true;
		        $mensal = (!array_key_exists($row["ConsumidorId"],$entregas) || $entregas[$row["ConsumidorId"]]["Mensal"] == 0) ? false : true;
	            //if ((getFreq($frequencias[$com],"s") && $semanal) || (getFreq($frequencias[$com],"q") && $quinzenal) || (getFreq($frequencias[$com],"m") && $mensal)) {
	                $idConsumidor = $row["ConsumidorId"];
	                echo '<tr>';
    		        echo '<td>'.ucwords(mb_strtolower($row["consumidor"]),'UTF-8').'</td>';
    		        echo '<td>'.$row["telefone"].'</td>';
    		        //echo '<td>'.$row["endereco_entrega"].'</td>';
    		        if (array_key_exists($idConsumidor,$lEnderecos)) {
    		            echo '<td>'.$lEnderecos[$idConsumidor].'</td>';
    		        } else {
    		            echo '<td>&nbsp;</td>';
    		        }
	                if (!isset($_GET["imprimir"])) {
        		        echo '<td class="'.($row["credito"] < 0 ? "debito" : "credito").'">'.($row["credito"] < 0 ? "-" : "").'R$'.number_format(abs($row["credito"]),2,",",".").'</td>';
        		        echo '<td>'.generateMenu($delivery,$row["pedido_id"],$row["pedido_id"]).'</td>';
	                } else {
        		        echo '<td class="'.($row["credito"] < 0 ? "debito" : "credito").'">'.($row["credito"] < 0 ? "-" : "").'R$'.number_format(abs($row["credito"]),2,",",".").'</td>';
        		        echo '<td>'.$delivery.'</td>';
	                }
	                echo '</tr>';
	            //}
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