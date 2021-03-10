<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "menu.php";
include "helpers.php";
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
		table, td {
			border: 1px solid black;
			border-collapse: collapse;
			text-align: center;
		}
		.firstLine {
			background: #11479e;
			color:#fff;
			font-weight:bold;
		}
	</style>
	<title>Livres - Comboio Orgânico</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
  </head>
  <body>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
include "menu_selecao_data.php";

if (isset($_GET["data"])) {
	if (strlen($_GET["data"]) > 0) {
		$sql = "SELECT * FROM Calendario WHERE id = ".$_GET["data"];
		$st = $conn->prepare($sql);
		$st->execute();
		$rs=$st->fetchAll();
		$dataEntrega = $rs[0]["data"];
		for ($i = 1; $i <= getTotalGrupos($conn); $i++) {
		    $frequencias[$i] = $rs[0][$i."acomunidade"];
		}
		$dataEntrega = strtotime($dataEntrega);

		//Dicionário de produtos
		$sql = "SELECT * FROM produtos ORDER BY nome";
		$st = $conn->prepare($sql);
		$st->execute();
		$rs=$st->fetchAll();
		foreach ($rs as $row) {
		    $produtos[$row["id"]]["produto"] = $row["nome"];
		    $produtos[$row["id"]]["produtor"] = $row["produtor"];
		    $produtos[$row["id"]]["unidade"] = $row["unidade"];
		    $produtos[$row["id"]]["preco"] = $row["preco"];
		    $produtos[$row["id"]]["preco_produtor"] = $row["preco_produtor"];
		}
		//Pedido Fixo
		$sql = getSQLPedidoSemana($dataEntrega,$frequencias,1);
		echo $sql;
		$st = $conn->prepare($sql);
		$st->execute();
		$rs=$st->fetchAll();
		$pedidoFixo="";
		foreach ($rs as $row) {
		    if (!is_array($pedidoFixo)) {
		        $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["quantidade"]=0;
		        $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["subtotal"]=0;
		    } else {
		        if (!array_key_exists($row["produtor"],$pedidoFixo)) {
	                $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["quantidade"]=0;
	                $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["subtotal"]=0;
	            } else {
	                if (!array_key_exists($row["IDProduto"],$pedidoFixo[$row["produtor"]])) {
	                    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["quantidade"]=0;
	                    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["subtotal"]=0;
	                }
	            }
		    }
		    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["quantidade"]+=$row["Quantidade"];
		    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["subtotal"]+=$row["Quantidade"]*$row["preco_produtor"];
		    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["unidade"]=$row["unidade"];
		    $pedidoFixo[$row["produtor"]][$row["IDProduto"]]["produto"]=$row["nome"];
		}
		//Pedidos Variáveis
		$sql = "SELECT produtos.id, produtos.nome, produtos.unidade, produtos.preco_produtor, produtosVar.Quantidade, ";
		$sql .= "produtos.produtor FROM produtosVar LEFT JOIN produtos ON produtosVar.idProduto = produtos.id WHERE idCalendario = ".$_GET["data"];
		$st = $conn->prepare($sql);
		$st->execute();
		if ($st->rowCount() > 0) {
		    $rs = $st->fetchAll();
		    foreach ($rs as $row) {
		        if ($row["Quantidade"] > 0) {
    		        $pedidoVariavel[$row["produtor"]][$row["id"]]["produto"] = $row["nome"];
    		        $pedidoVariavel[$row["produtor"]][$row["id"]]["unidade"] = $row["unidade"];
    		        $pedidoVariavel[$row["produtor"]][$row["id"]]["quantidade"] = $row["Quantidade"];
    		        $pedidoVariavel[$row["produtor"]][$row["id"]]["preco"] = $row["preco_produtor"];
    		        $pedidoVariavel[$row["produtor"]][$row["id"]]["subtotal"] = $row["preco_produtor"]*$row["Quantidade"];
		        }
		    }
		}
		//Pedido Completo
		$pedidoTotal = $pedidoFixo;
    	if (isset($pedidoVariavel)) {
    		foreach ($pedidoVariavel as $produtor => $pedido) {
    		    foreach ($pedido as $idProduto => $dados) {
    		        if (array_key_exists($produtor,$pedidoTotal)) {
    		            if (array_key_exists($idProduto,$pedidoTotal[$produtor])) {
    		                $pedidoTotal[$produtor][$idProduto]["quantidade"] += $dados["quantidade"];
    		                $pedidoTotal[$produtor][$idProduto]["subtotal"] += $dados["subtotal"];
    		            } else {
    		                $pedidoTotal[$produtor][$idProduto]["produto"] = $dados["produto"];
    		                $pedidoTotal[$produtor][$idProduto]["unidade"] = $dados["unidade"];
    		                $pedidoTotal[$produtor][$idProduto]["quantidade"] = $dados["quantidade"];
    		                $pedidoTotal[$produtor][$idProduto]["preco"] = $dados["preco"];
    		                $pedidoTotal[$produtor][$idProduto]["subtotal"] = $dados["preco"]*$dados["quantidade"];
    		            }
    		        } else {
    		            $pedidoTotal[$produtor][$idProduto]["produto"] = $dados["produto"];
		                $pedidoTotal[$produtor][$idProduto]["unidade"] = $dados["unidade"];
		                $pedidoTotal[$produtor][$idProduto]["quantidade"] = $dados["quantidade"];
		                $pedidoTotal[$produtor][$idProduto]["preco"] = $dados["preco"];
		                $pedidoTotal[$produtor][$idProduto]["subtotal"] = $dados["preco"]*$dados["quantidade"];
    		        }
    		    }
    		}
    	}
		//Montar lista
		echo '<table style="vertical-align:top;">';
		$totalGeral["fixo"] = 0;
		$totalGeral["variavel"] = 0;
		$totalGeral["total"] = 0;
		foreach ($pedidoTotal as $produtor=>$produtos) {
		    $totalProdutor["fixo"] = 0;
		    $totalProdutor["variavel"] = 0;
		    $totalProdutor["total"] = 0;
		    echo "<tr>";
		    echo '<td width="33%"><b>'.$produtor.' - Pedido Fixo</b></td>';
		    echo '<td width="33%"><b>'.$produtor.' - Pedido Variável</b></td>';
		    echo '<td width="34%"><b>'.$produtor.' - Pedido Total</b></td>';
		    echo "</tr>";
		    foreach ($produtos as $idProduto => $dadosProduto) {
		        echo '<tr>';
		        //Pedido fixo
		        echo '<td style="vertical-align:top;">';
		        if (array_key_exists($produtor,$pedidoFixo)) {
    		        if (array_key_exists($idProduto,$pedidoFixo[$produtor])) {
    		            echo $pedidoFixo[$produtor][$idProduto]["quantidade"]." ".$pedidoFixo[$produtor][$idProduto]["unidade"];
    		            echo " x ".$pedidoFixo[$produtor][$idProduto]["produto"];
    		            echo " - R$".number_format($pedidoFixo[$produtor][$idProduto]["subtotal"],2,",",".");
    		            $totalProdutor["fixo"] += $pedidoFixo[$produtor][$idProduto]["subtotal"];
    		        } else {
    		            echo "-";
    		        }
		        } else {
		            echo "-";
		        }
		        echo "</td>";
		        //Pedido Variável
		        echo '<td style="vertical-align:top;">';
		        if (isset($pedidoVariavel)) {
    		        if (array_key_exists($produtor,$pedidoVariavel)) {
    		            if (array_key_exists($idProduto,$pedidoVariavel[$produtor])) {
    		                echo $pedidoVariavel[$produtor][$idProduto]["quantidade"]." ".$pedidoVariavel[$produtor][$idProduto]["unidade"];
    		                echo " x ".$pedidoVariavel[$produtor][$idProduto]["produto"];
    		                echo " - R$".number_format($pedidoVariavel[$produtor][$idProduto]["subtotal"],2,",",".");
    		                $totalProdutor["variavel"] += $pedidoVariavel[$produtor][$idProduto]["subtotal"];
    		            } else {
    		                echo "-";
    		            }
    		        } else {
    		            echo "-";
    		        }
		        }
		        echo "</td>";
		        //Pedido Total Geral
		        echo '<td style="vertical-align:top;">';
				echo $dadosProduto["quantidade"]." ".$dadosProduto["unidade"]." x ".$dadosProduto["produto"]." - R$".number_format($dadosProduto["subtotal"],2,",",".");
				echo "</td>";
				echo "</tr>";
				$totalProdutor["total"] += $dadosProduto["subtotal"];
		    }
		    echo '<tr>';
		    //Subtotal por produtor
		    //Total Fixo
            echo '<td style="vertical-align:top;">';
            echo "Subtotal ".$produtor." - R$".number_format($totalProdutor["fixo"],2,",",".");
			echo "</td>";
		    //Total Variável
		    echo '<td style="vertical-align:top;">';
            echo "Subtotal ".$produtor." - R$".number_format($totalProdutor["variavel"],2,",",".");
            echo "</td>";
		    //Total Geral
		    echo '<td style="vertical-align:top;">';
            echo "Subtotal ".$produtor." - R$".number_format($totalProdutor["total"],2,",",".");
            echo "</td>";
			echo '</tr>';
			$totalGeral["fixo"] += $totalProdutor["fixo"];
			$totalGeral["variavel"] += $totalProdutor["variavel"];
			$totalGeral["total"] += $totalProdutor["total"];
		}
		if (($totalGeral["fixo"] + $totalGeral["variavel"] + $totalGeral["total"]) > 0) {
    		echo '<tr><td></td></tr>';
			echo '<tr>';
			echo '<td style="vertical-align:top;">';
			echo "Total Fixo da semana - R$".number_format($totalGeral["fixo"],2,",",".");
			echo "</td>";
			echo '<td style="vertical-align:top;">';
			echo "Total Variável da semana - R$".number_format($totalGeral["variavel"],2,",",".");
			echo "</td>";
			echo '<td style="vertical-align:top;">';
			echo "Total Geral da semana - R$".number_format($totalGeral["total"],2,",",".");
			echo "</td>";
			echo "</tr>";
		}
		echo '</table>';
		/*
		//Montar lista pedidos variáveis
		echo "<p>&nbsp;</p>";
		echo '<table style="vertical-align:top;">';
		echo '<tr><td><b>Pedidos Variáveis</b></td></tr>';
		$totalGeral=0;
		foreach ($pedidoVariavel as $produtor=>$produtos) {
		    echo "<tr><td><b>".$produtor."</b></td></tr>";
		    $totalProdutor=0;
		    foreach ($produtos as $produto=>$dadosProduto) {
		        echo '<tr><td style="vertical-align:top;">';
				echo $dadosProduto["quantidade"]." ".$dadosProduto["unidade"]." x ".$produto." - R$".number_format($dadosProduto["subtotal"],2,",",".");
				echo "</td></tr>";
				$totalProdutor+=$dadosProduto["subtotal"];
		    }
		    echo '<tr><td style="vertical-align:top;">';
			echo "Subtotal ".$produtor." - R$".number_format($totalProdutor,2,",",".");
			$totalGeral+=$totalProdutor;
			echo "</td></tr>";
		}
		if ($totalGeral > 0) {
    		echo '<tr><td></td></tr>';
			echo '<tr><td style="vertical-align:top;">';
			echo "Total da semana - R$".number_format($totalGeral,2,",",".");
			echo "</td></tr>";
		}
		echo '</table>';
		*/
	}
}
?>
  </body>
</html>
<?php
function friendlyFreq($freq) {
	$frequencia = "";
	if (substr($freq,0,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " = ";
		}
		$frequencia .= "Semanal";
	}
	if (substr($freq,1,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " + ";
		}
		$frequencia .= "Quinzenal";
	}
	if (substr($freq,2,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " + ";
		}
		$frequencia .= "Mensal";
	}
	return $frequencia;
}
?>