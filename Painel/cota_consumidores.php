<?php
$levelRequired=10000;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config.php";
include "acesso.php";
include "helpers.php";
include "menu.php";
?>
<!doctype html>
<html lang="en">
  <head>
	  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		table, td {
			border: 1px solid #bbd6ee;
			border-collapse: collapse;
			text-align: center;
			font-family: Calibri;
		}
		.firstLine {
			background: #5b9bd5;
			color:#fff;
			font-weight:bold;
		}
		.lineColor {
		    background: #ddebf7;
		}
		.lineBlank {
		    background: #fff;
		}
		#previewImage {
		    position:absolute;
		    left: 100px;
		    z-index: 1;
		    display:none;
		    background: #fff;
		    text-align:center;
		    width:600px;
		}
		#downloadImage {
		    display:none;
		}
	</style>
    <title>Livres - Comboio Orgânico</title>
    <script src="../html2canvas.js"></script>
    <script src="../jquery-3.4.1.min.js"></script>
    <script>
        $(document).ready(function(){
            var element=$("#capture")[0];
            var getCanvas;
            $("#getImage").on('click', function() {
                html2canvas(element, {
                   onrendered: function(canvas) {
                       $("#previewImage").empty();
                       $("#previewImage")[0].append(canvas);
                       getCanvas=canvas;
                       $("#previewImage").show();
                       $("#downloadImage").show();
                   } 
                });
            });
            $("#downloadImage").on('click', function () {
                var imageData = getCanvas.toDataURL("image/png");
                var newData = imageData.replace(/^data:image\/png/, "data:application/octet-stream");
                $("#downloadImage").attr("download", $("#data").children("option:selected").text()+".png").attr("href", newData);
            });
            $(document).on('click', function() {
                $("#previewImage").hide();
            });
        });
    </script>
  </head>
  <body>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
include "menu_selecao_data.php";
$receita = 0;
if (isset($_GET["data"])) {
    if (isset($_GET["imprimir"])) {
        echo '<p><a href="?data='.$_GET["data"].'">Habilitar edição de cota</a></p>';
    } else {
        echo '<p><a href="?data='.$_GET["data"].'&imprimir=1">Desabilitar edição de cota</a></p>';
    }
	if (strlen($_GET["data"]) > 0) {
	    /* SALVAR NOVA COTA DE CONSUMIDOR */
	    if (isset($_POST["idConsumidor"])) {
	        $idConsumidor = $_POST["idConsumidor"];
	        $cotaConsumidor = $_POST["cota"];
	        $cotaConsumidor=str_replace(",",".",$cotaConsumidor);
	        $cotaConsumidor=str_replace("R","",$cotaConsumidor);
	        $cotaConsumidor=str_replace("$","",$cotaConsumidor);
	        $sqlUpdate = "UPDATE Consumidores SET cota_imediato = ".$cotaConsumidor." WHERE id = ".$idConsumidor;
	        $st = $conn->prepare($sqlUpdate);
	        $st->execute();
	        setLog("log.txt","Atualização Cota",$sqlUpdate);
	    }
	    //Fim nova cota
		$sql = "SELECT * FROM Calendario WHERE id = ".$_GET["data"];
		$st = $conn->prepare($sql);
		$st->execute();
		$rs=$st->fetchAll();
		$dataEntrega = $rs[0]["data"];
		//$frequencia[1] = $rs[0]["1acomunidade"];
		//$frequencia[2] = $rs[0]["2acomunidade"];
		$frequencia = getFrequencias($conn,$_GET["data"]);
		$dataEntrega = strtotime($dataEntrega);
		$counter=0;
		?>
		<div id="previewImage"><p><a id="fecharPreviewImage" href="#">Fechar</a></p></div>
        <input type="button" id="getImage" value="Salvar como imagem" />
        <a id="downloadImage" href="#">Baixar imagem</a>
		<table id="capture" style="vertical-align:top;">
		<?php
		$G2 = false;
		$sql2 = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id";
		$sql2 .= " LEFT JOIN Consumidores ON Consumidores.id = Pedidos.IDConsumidor";
		$sql2 .= " WHERE produtos.previsao <= '".date("Y-m-d",$dataEntrega)."'";
		$sql2 .= " AND Pedidos.Quantidade > 0";
		$sql2 .= " AND Consumidores.ativo = 1";
		/*$freq = "";
		if (substr($frequencia,0,1) == 1) {
			$freq .= "(Pedidos.frequencia = 'Semanal'";
		}
		if (substr($frequencia,1,1) == 1) {
			if (strlen($freq) > 0) {
				$freq .= " OR ";
			} else {
				$freq .= "(";
			}
			$freq .= "Pedidos.frequencia = 'Quinzenal'";
		}
		if (substr($frequencia,2,1) == 1) {
			if (strlen($freq) > 0) {
				$freq .= " OR ";
			} else {
				$freq .= "(";
			}
			$freq .= "Pedidos.frequencia = 'Mensal'";
		}
		$freq = ""; //essa linha simplesmente ignora tudo que foi feito acima para definir a frequência (teste)
		if (strlen($freq) > 0) {
			$freq = $freq . ")";
			$sql2 .= " AND ".$freq;
		}
		*/
		$sql2 .= " ORDER BY Consumidores.comunidade ASC, Consumidores.Consumidor ASC";
		$st2 = $conn->prepare($sql2);
		$st2->execute();
		$rs2=$st2->fetchAll();
		$consumidor = $rs2[0]["consumidor"];
		$grupo = $rs2[0]["comunidade"];
		$grupoAnterior=$grupo;
		$idConsumidor = $rs2[0]["id"];
		$cota = $rs2[0]["cota_imediato"];
		$totalSemanal=0;
		$totalQuinzenal=0;
		$header =   '<tr class="firstLine">';
		$header .=  '<td width="200">* CONSUMIDOR CONSCIENTE *</td>';
		$header .=   '<td width="140">* FAIXA VARIÁVEL *</td>';
		$header .=   '<td width="100">Grupo</td>';
		$header .=   '<td width="100">Cota Total</td>';
		$header .=   '<td width="100">Faixa Fixa</td>';
		if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
		    $header .=   '<td>Cota Calculada</td>';
		    $header .=   '<td width="100">Salvar</td>';
		}
		$header .=   '</tr>';
		echo $header;
		foreach ($rs2 as $row2) {
			if ($row2["consumidor"] != $consumidor) {
				//totalizar consumidor
				if ($totalSemanal == 0) {
					$fixo = $totalQuinzenal;
				} else {
					$fixo = $totalSemanal + ($totalQuinzenal/2);
				}
				if (($totalSemanal > 0 && substr($frequencia[$grupo],0,1) == 1) || substr($frequencia[$grupo],1,1) == 1) {
				    $receita += $cota;
				    if ($grupo != $grupoAnterior) {
				        echo $header;
				        $grupoAnterior=$grupo;
				    }
				    $counter++;
				    if ($counter % 2 == 0) {
					    echo '<tr class="lineBlank">';
				    } else {
					    echo '<tr class="lineColor">';
				    }
				    echo '<form method="POST" action="">';
				    echo "<td>".ucwords(mb_strtolower($consumidor,'UTF-8'))."</td>";
				    echo "<td>R$".number_format(($cota-$fixo),2,",",".")."</td>";
				    echo "<td>G".$grupo."</td>";
				    if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
				        echo '<td><input type="text" size="5" id="cota" name="cota" value="'.number_format($cota,2,",",".").'" /></td>';
				    } else {
				        echo "<td>R$".number_format($cota,2,",",".")."</td>";
				    }
				    echo "<td>R$".number_format($fixo,2,",",".")."</td>";
				    if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
				        echo '<input type="hidden" name="idConsumidor" id="idConsumidor" value="'.$idConsumidor.'" />';
				        $cotaCalculada = floor($fixo/5)*5+5;
				        if ($cotaCalculada-$fixo < 4) { $cotaCalculada += 5; }
				        if ($cotaCalculada > $cota) {
				            $cor = "#FF0000";
				        } else { 
				            if ($cotaCalculada < $cota) {
				                $cor = "#0000FF";
				            } else {
				                $cor = "#00FF00";
				            }
				        }
				        echo '<td><span style="color:'.$cor.';">R$'.number_format($cotaCalculada,2,",",".").'</span></td>';
				        echo '<td><input type="submit" id="submit" name="submit" value="Salvar" /></td>';
				    }
				    echo "</tr>";
				    echo "</form>";
				}
				$consumidor = $row2["consumidor"];
				$idConsumidor = $row2["id"];
				$grupo = $row2["comunidade"];
				$cota = $row2["cota_imediato"];
				$totalSemanal=0;
				$totalQuinzenal=0;
			}
			if ($row2["Frequencia"] == "Semanal") {
				$totalSemanal += $row2["Quantidade"]*$row2["preco"];
			}
			if ($row2["Frequencia"] == "Quinzenal") {
				$totalQuinzenal += $row2["Quantidade"]*$row2["preco"];
			}
		}
		if (($totalSemanal > 0 && substr($frequencia[$grupo],0,1) == 1) || substr($frequencia[$grupo],1,1) == 1) {
		    $receita += $cota;
			if ($totalSemanal == 0) {
				$fixo = $totalQuinzenal;
			} else {
				$fixo = $totalSemanal + ($totalQuinzenal/2);
			}
			$counter++;
			if ($counter % 2 == 0) {
			    echo '<tr class="lineBlank">';
			} else {
			    echo '<tr class="lineColor">';
			}
			echo '<form method="POST" action="">';
			echo "<td>".ucwords(strtolower($consumidor))."</td>";
			echo "<td>R$".number_format($cota-$fixo,2,",",".")."</td>";
			echo "<td>G".$grupo."</td>";
			if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
		        echo '<td><input type="text" size="5" id="cota" name="cota" value="'.number_format($cota,2,",",".").'" /></td>';
		    } else {
		        echo "<td>R$".number_format($cota,2,",",".")."</td>";
		    }
			echo "<td>R$".number_format($fixo,2,",",".")."</td>";
			if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
			    $cotaCalculada = floor($fixo/5)*5+5;
		        if ($cotaCalculada-$fixo < 4) { $cotaCalculada += 5; }
		        if ($cotaCalculada > $cota) {
		            $cor = "#FF0000";
		        } else { 
		            if ($cotaCalculada < $cota) {
		                $cor = "#0000FF";
		            } else {
		                $cor = "#00FF00";
		            }
		        }
		        echo '<td><span style="color:'.$cor.';">R$'.number_format($cotaCalculada,2,",",".").'</span></td>';
			    echo '<input type="hidden" name="idConsumidor" id="idConsumidor" value="'.$idConsumidor.'" />';
		        echo '<td><input type="submit" id="submit" name="submit" value="Salvar" /></td>';
		    }
			echo "</tr>";
			echo '</form>';
		}
	}
}
?>
</table>
<?php
/*
if ($receita > 0) {
    echo '<p>';
    echo "Receita Total: R$".number_format($receita,2,",",".");
    echo '</p>';
}
*/
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