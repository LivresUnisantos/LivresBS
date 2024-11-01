<?php
require_once "../includes/autoloader.inc.php";
$livres = new Livres();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config.php";
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
		<div class="grid-x grid-padding-x">
			<div class="large-4">
				Consulte sua cesta com produtos disponíveis a partir da próxima entrega.
				<form action="" method="GET">
					<label for="cpf" />CPF</label>
					<input type="text" data-validation="cpf" placeholder="Preencha seu CPF" value="" name="cpf" id="cpf" />
					<input type="submit" value="Enviar" />
				</form>
			</div>
			
		</div>
	</div>
<?php
} else {
	$cpf = $_GET["cpf"];
	$cpf=str_replace(".","",$cpf);
	$cpf=str_replace(",","",$cpf);
	$cpf=str_replace("-","",$cpf);
	
	$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
	//Identificar consumidor
    $sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 0) {
    	echo "<p>Consumidor não encontrado</p>";
    	echo '<a href=".">Voltar</a>';
    	exit();
    }
    $rs=$st->fetchAll();

	//Consumidor identificado, identificar próxima entrega
	$consumidor=$rs[0]["consumidor"];
	$idConsumidor=$rs[0]["id"];
	$cota=$rs[0]["cota_imediato"];
    $comunidade=$rs[0]["comunidade"];
    $hoje = date("Y-m-d");

    //$sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' ORDER BY data ASC";
    $sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' AND LENGTH(".$comunidade."acomunidade) = 3 AND ".$comunidade."acomunidade <> '000' ORDER BY data ASC";
    $st = $conn->prepare($sql);
    $st->execute();
    
    if ($st->rowCount() == 0) {
        echo "Você não possui nenhuma entrega prevista. Caso ache isso um equívoco, gentileza entrar em contato conosco!";
        exit();
    }
    
    $rsProxima =$st->fetch();
    $proximaEntrega = $rsProxima["data"];
    $idProximaEntrega = $rsProxima["id"];
    

	$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
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
	$rs=$st->fetchAll();
	$consumidor=$rs[0]["consumidor"];
	$idConsumidor=$rs[0]["id"];
	$cota=$rs[0]["cota_imediato"];
	$ativo = $rs[0]["ativo"];
	?>
	<p></p>
		<div class="grid-container">
			<div class="grid-x grid-padding-x callout">
				<div class="large-12 cell">
				    <a href="../Cestas">Consultar outro consumidor</a>
					<h3><?php echo ccase($consumidor); ?></h3>
					<?php
					echo "Telefone: ".$rs[0]["telefone"]."<br>";
					echo "Endereço: ".$rs[0]["endereco"]."<br>";
					echo "CPF: ".$rs[0]["cpf"]."<br>";
					if ($ativo == 0) {
					    echo "Seu pedido está na lista de espera para a próxima comunidade!";
					}
					?>
					<!--
					<p>Nós do LIVRES estamos muito felizes e satisfeitos com a parceria que estamos selando. Acreditamos que juntos fica mais fácil de construir outra economia sem agrotóxico, sem atravessadores e sem exploração. Financiaremos a agricultura familiar, e nossos pequenos produtores da AOVALE e APROATE, que juntos conosco, serão responsáveis por oferecer produtos de qualidade e fresquinhos à toda nossa  comunidade do comboio orgânico.</p>
					<p>Para que possamos em breve começar a entregar seus alimentos livres de agrotóxicos, precisamos que você confirme que esta será sua cesta FIXA IMEDIATA SEMANAL (ou seja, lista de produtos que serão recebidos todas as semanas do mês a partir do momento em que começarem as entregas) e FIXA QUINZENAL (ou seja, produtos serão recebidos a cada 15 dias, ou melhor dizendo, em semanas alternadas). As cestas virão JUNTAS, em uma mesma sacola. </p>
					<p>As informações abaixo são de acordo com o que você preencheu no seu “carrinho comunitário” no nosso site.  Leia atentamente e caso esteja de acordo em  receber todos os produtos, dê um visto ao final da página para a confirmação de cada cesta. </p>
					-->
				</div>
			</div>
			<?php
			$sqlEntregues = "SELECT * FROM pedidos_consolidados WHERE consumidor_id = ".$idConsumidor." ORDER BY pedido_data DESC";
			$st = $conn->prepare($sqlEntregues);
			$st->execute();
			if ($st->rowCount() > 0) {
			    $rsEntregues = $st->fetchAll();
			?>
			<div class="grid-x grid-padding-x callout">
			    <div class="large-12 medium-12 cell text-left">
		            <form class="form-inline" method="GET">
		                <label for="cesta_entrega">Consulte sua cesta entregue em</label>
		                <select id="cesta_entrega" name="cesta_entrega">
                            <option value="">Selecione</option>
		                    <?php
		                    foreach ($rsEntregues as $row) {
                                $selected = "";
                                if (isset($_GET["cesta_entrega"]) && $_GET["cesta_entrega"] == $row["pedido_id"]) {
                                    $selected = " selected = selected";
                                }
		                        echo '<option value="'.$row["pedido_id"].'"'.$selected.'>'.date('d/m/Y',strtotime($row["pedido_data"])).'</option>';
		                    }
		                    ?>
		                </select>
		                <input type="hidden" name="cpf" id="cpf" value="<?php echo (isset($_GET["cpf"])) ? $_GET["cpf"] : ""; ?>" />
		                <button type="submit">Consultar</button>
                    </form>
                    <?php
                    if (isset($_GET["cesta_entrega"]) && $_GET["cesta_entrega"] != "") {
                    ?>
                        <span><a href="?cpf=<?php echo $_GET["cpf"]; ?>">Visualizar minha cesta de compromisso</a></span>
                    <?php
                    }
                    ?>
			    </div>
            </div>
            <?php
			}
			?>
            <?php
            if (isset($_GET["cesta_entrega"]) && $_GET["cesta_entrega"] != "") {
                $sqlEntregue = "SELECT * FROM pedidos_consolidados ped
                                LEFT JOIN pedidos_consolidados_itens it ON it.pedido_id = ped.pedido_id
                                LEFT JOIN unidades un ON it.item_tipo = un.id
                                WHERE ped.pedido_id = ".$_GET["cesta_entrega"];
                $st = $conn->prepare($sqlEntregue);
                $st->execute();
                if ($st->rowCount() > 0) {
                    $rsEntregue = $st->fetchAll();
                    ?>
                    <div class="grid-x grid-padding-x">
				        <div class="large-12 medium-12 cell text-center callout" id="semanal">
                            <h5>CESTA ENTREGUE EM <?php echo date('d/m/Y',strtotime($rsEntregue[0]["pedido_data"])); ?></h5>
				            <div class="grid-x grid-padding-x">
            					<?php
            					//Loop de pedidos já entregues
            					$totalcesta=0;
            					foreach ($rsEntregue as $row) {
            						echo '<div class="medium-6 text-left">&nbsp'.ccase($row["item_produto"]).'</div>';
                                    echo '<div class="medium-2 text-left">'.($row["item_qtde"]*1).' '.$row["unidade"].'</div>';
                                    echo '<div class="medium-2 text-left">'.$row["item_freq_cesta"].'</div>';
            						echo '<div class="medium-2">R$'.number_format($row["item_valor"]*$row["item_qtde"],2,",",".").'</div>';
            						$totalcesta++;
            					}
                                ?>
                                <p>&nbsp;</p>
                            </div>
                            <div class="text-left callout">
								<?php
								if ($row["pedido_valor_total"] < $row["pedido_cota"]) {
								?>
									<p>Valor total da cesta: R$<?php echo number_format($row["pedido_cota"],2,",","."); ?> (sendo R$
									<?php echo number_format(($row["pedido_cota"]-$row["pedido_fixa"]),2,",","."); ?> de variável)
								<?php
								} else {
								?>
									<p>Valor total da cesta: R$<?php echo number_format($row["pedido_valor_total"],2,",","."); ?>, sendo:<br>
									<?php
									echo 'Cota na semana: R$'.number_format($row["pedido_cota"],2,",",".")."<br>";
									//verificar excedente de variável
									$cotaVariavel = $row["pedido_cota"]-$row["pedido_fixa"];
									$excedente = $row["pedido_variavel"]-$cotaVariavel;
									if ($excedente > 0.5) {
										echo 'Excedente variável: R$'.number_format($excedente,2,",",".").'<br>';
									}
									if ($row["pedido_mensal"] > 0) {
										echo 'Pedido Mensal: R$'.number_format($row["pedido_mensal"],2,",",".");
									}                                
									?>
									</p>
								<?php
								}
								?>
                            </div>
					    </div>
					</div>
                    <?php
                }
            ?>
            <?php
            } else {
            ?>
			<div class="grid-x grid-padding-x">
                <?php
                $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia = 'Semanal' AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximaEntrega."' ORDER BY produtos.nome";
                $st = $conn->prepare($sql);
                $st->execute();
                if ($st->rowCount() > 0) {
                ?>
				<div class="large-12 medium-12 cell text-center callout" id="semanal">
					<h5>CESTA IMEDIATA SEMANAL</h5>
					<div class="grid-x grid-padding-x">
						<?php
						//Loop de pedidos semanais						
						$rsSemanal=$st->fetchAll();
						$totalCesta=0;
						foreach ($rsSemanal as $row) {					
							echo '<div class="medium-7 text-left">&nbsp'.ccase($row["nome"]).'</div>';
							echo '<div class="medium-3 text-left">'.$row["Quantidade"].' '.$row["unidade"].'</div>';
							echo '<div class="medium-2">R$'.number_format($row["preco"]*$row["Quantidade"],2,",",".").'</div>';
							$totalCesta += $row["preco"]*$row["Quantidade"];
						}
                        ?>
                        <p>&nbsp;</p> 
                    </div>
                    <div class="text-left callout">
                        <p>Valor total da cesta: R$<?php echo number_format($livres->cotaIdeal($totalCesta),2,",","."); ?> (sendo
                        R$<?php echo number_format($livres->cotaIdeal($totalCesta)-$totalCesta,2,",","."); ?> de variável)</p>
                    </div>
                </div>
                <?php
                }
                ?>
				<div class="medium-1">&nbsp;</div>
				<div class="large-12 medium-12 text-center callout" id="quinzenal">
					<h5>CESTA IMEDIATA QUINZENAL</h5>
					<div class="grid-x grid-padding-x">
						<?php
						//Loop de pedidos quinzenais+semanais
						$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Semanal' OR Pedidos.Frequencia = 'Quinzenal') AND produtos.previsao <= '".$proximaEntrega."' ORDER BY produtos.nome";
						$st = $conn->prepare($sql);
						$st->execute();
						$rsQuinzenal=$st->fetchAll();
						$totalCesta=0;
						foreach ($rsQuinzenal as $row) {
							echo '<div class="medium-6 text-left">&nbsp'.ccase($row["nome"]).'</div>';
							echo '<div class="medium-2 text-left">'.$row["Quantidade"].' '.$row["unidade"].'</div>';
							echo '<div class="medium-2 text-left">'.$row["Frequencia"].'</div>';
                            echo '<div class="medium-2">R$'.number_format($row["preco"]*$row["Quantidade"],2,",",".").'</div>';
                            $totalCesta+=$row["preco"]*$row["Quantidade"];
						}
                        ?>
                        <p>&nbsp;</p>
                    </div>
                    <div class="text-left callout">
                        <p>Valor total da cesta: R$<?php echo number_format($livres->cotaIdeal($totalCesta),2,",","."); ?> (sendo
                        R$<?php echo number_format($livres->cotaIdeal($totalCesta)-$totalCesta,2,",","."); ?> de variável)</p>
                    </div>
				</div>
			</div>
			<! -- CESTA COMPROMISSO -->
			<div class="grid-x grid-padding-x">
                <?php
                $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia = 'Semanal' AND Pedidos.Quantidade > 0 ORDER BY produtos.nome";
                $st = $conn->prepare($sql);
                $st->execute();
                if ($st->rowCount() > 0) {
                ?>
				<div class="large-12 medium-12 cell text-center callout" id="semanal">
					<h5>CESTA COMPROMISSO SEMANAL</h5>
					<div class="grid-x grid-padding-x">
						<?php
						//Loop de pedidos semanais						
						$rsSemanal=$st->fetchAll();
						$totalCesta=0;
						foreach ($rsSemanal as $row) {					
							echo '<div class="medium-7 text-left">&nbsp'.ccase($row["nome"]).'</div>';
							echo '<div class="medium-3 text-left">'.$row["Quantidade"].' '.$row["unidade"].'</div>';
							echo '<div class="medium-2">R$'.number_format($row["preco"]*$row["Quantidade"],2,",",".").'</div>';
							$totalCesta += $row["preco"]*$row["Quantidade"];
						}
                        ?>
                        <p>&nbsp;</p>
                    </div>
                    <div class="text-left callout">
                        <p>Valor total da cesta: R$<?php echo number_format($livres->cotaIdeal($totalCesta),2,",","."); ?> (sendo
                        R$<?php echo number_format($livres->cotaIdeal($totalCesta)-$totalCesta,2,",","."); ?> de variável)</p>
                    </div>
                </div>
                <?php
                }
                ?>
				<div class="medium-1">&nbsp;</div>
				<div class="large-12 medium-12 text-center callout" id="quinzenal">
					<h5>CESTA COMPROMISSO QUINZENAL</h5>
					<div class="grid-x grid-padding-x">
						<?php
						//Loop de pedidos quinzenais+semanais
						$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Semanal' OR Pedidos.Frequencia = 'Quinzenal') ORDER BY produtos.nome";
						$st = $conn->prepare($sql);
						$st->execute();
						$rsQuinzenal=$st->fetchAll();
						$totalCesta=0;
						foreach ($rsQuinzenal as $row) {
						    if (strtotime($row["previsao"]) > strtotime($proximaEntrega)) {
						        echo '<div class="medium-6 text-left"><b>&nbsp'.ccase($row["nome"]).'</b></div>';
						    } else {
						        echo '<div class="medium-6 text-left">&nbsp'.ccase($row["nome"]).'</div>';
						    }
							echo '<div class="medium-2 text-left">'.$row["Quantidade"].' '.$row["unidade"].'</div>';
							echo '<div class="medium-2 text-left">'.$row["Frequencia"].'</div>';
                            echo '<div class="medium-2">R$'.number_format($row["preco"]*$row["Quantidade"],2,",",".").'</div>';
                            $totalCesta+=$row["preco"]*$row["Quantidade"];
						}
                        ?>
                        <p>&nbsp;</p>
                    </div>
                    <div class="text-left callout">
                        <p>Valor total da cesta: R$<?php echo number_format($livres->cotaIdeal($totalCesta),2,",","."); ?> (sendo
                        R$<?php echo number_format($livres->cotaIdeal($totalCesta)-$totalCesta,2,",","."); ?> de variável)</p>
                    </div>
				</div>
            </div>
            <?php
            $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Mensal') ORDER BY produtos.nome";
            $st = $conn->prepare($sql);
            $st->execute();

            if ($st->rowCount() > 0) {
            ?>
			<div class="grid-x grid-padding-x">
    			<div class="medium-1">&nbsp;</div>
    			<div class="large-12 medium-12 text-center callout" id="quinzenal">
    				<h5>CESTA MENSAL</h5>
    				<div class="grid-x grid-padding-x">
    					<?php
    					//Loop de pedidos mensais
    					$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Mensal') ORDER BY produtos.nome";
    					$st = $conn->prepare($sql);
    					$st->execute();
    					$rsMensal=$st->fetchAll();
    					$totalCesta=0;
    					foreach ($rsMensal as $row) {
    						echo '<div class="medium-6 text-left">&nbsp'.ccase($row["nome"]).'</div>';
    						echo '<div class="medium-2 text-left">'.$row["Quantidade"].' '.$row["unidade"].'</div>';
    						echo '<div class="medium-2 text-left">'.$row["Frequencia"].'</div>';
							echo '<div class="medium-2">R$'.number_format($row["preco"]*$row["Quantidade"],2,",",".").'</div>';
    						$totalCesta+=$row["preco"]*$row["Quantidade"];
    					}
    					?>
                    </div>
                    <div class="text-left callout">
                        <p>Valor total da cesta: R$<?php echo number_format($totalCesta,2,",","."); ?></p>
                    </div>
    			</div>
            </div>
            <?php
            }
            ?>
			<?php
            } //fecha IF da cesta já entregue
			?>
			<!--
			<div class="grid-x grid-padding-x">
				<div class="large-12">
				<p></p>
				<?php echo ccase($consumidor); ?><br>
				CPF: <?php echo substr($cpf,0,3).".".substr($cpf,3,3).".".substr($cpf,6,3)."-".substr($cpf,9,2); ?><br>
				Consumidor Consciente
				</div>
			</div>
			-->
		</div>
<?php
}
?>
<script src="../js/vendor/jquery.js"></script>
<script src="../js/vendor/what-input.js"></script>
<script src="../js/vendor/foundation.js"></script>
<script src="../js/app.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<script>
$.validate({
	lang: 'pt',
	modules : 'brazil'
});
</script>
</body>
</html>
