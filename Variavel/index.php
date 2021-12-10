<?php
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
    <script src="../js/vendor/jquery.js"></script>
	<script>
	$(document).ready(function() {
		$('#form_variavel').submit(function() {
		    var opcao1 = $("#opcao1").val();
		    var opcao2 = $("#opcao2").val();
		    var qt1 = $("#quantidade_1").val();
		    var qt2 = $("#quantidade_2").val();
		    var delivery = $("#delivery").val();
		    var endereco_entrega =  $("#endereco_entrega").val();
		    var adicional = $("input[name='adicional']:checked").val();
		    if (opcao1 == "" && opcao2 == "") {
		        alert ("Escolha ao menos uma opção de variável.");
		        event.preventDefault();
		        return false;
		    } else {
		        if (opcao1 != "" && qt1 == "") {
		            alert ("Escolha a quantidade de produtos na opção 1.");
		            event.preventDefault();
		            return false;
		        } else {
		            if (opcao2 != "" && qt2 == "") {
		                alert ("Escolha a quantidade de produtos na opção 2.");
    		            event.preventDefault();
    		            return false;
		            }
		        }
		    }
		    if (delivery == "") {
		        alert('Escolha se você deseja delivery ou retirada. Caso não saiba ainda, não tem problema. Marque a opção "não sei".');
		        event.preventDefault();
		        return false;
		    } else {
		        if (delivery == "Sim" || delivery == "Não sei ainda") {
		            if (endereco_entrega == "") {
		                alert('Você precisa preencher o seu endereço');
		                event.preventDefault();
		                return false;
		            }
		        }
		    }
		    if (typeof adicional == "undefined") {
		        alert('Selecione se você deseja pagar adicional referente aos variáveis ou não.');
		        event.preventDefault();
		        return false;
		    }
		    return;
        });
        $("#delivery").change(function() {
            if ($(this).val() == "Sim" || $(this).val() == "Não sei ainda") {
                alert("Confirme seu endereço de entrega!");
                $("#box_endereco_entrega").show();
                $("#endereco_entrega").focus();
                $("#endereco_entrega").css({"background-color": "#ffff00"});
                
            } else {
                $("#box_endereco_entrega").hide();
            }
        });
        $("#endereco_entrega").focusout(function() {
            $(this).css({"background-color": "#ffffff"});
        });
        $("[recalc=sim]").change(function() {
            opcao=$(this).attr('opcao');
            var produto = $("#opcao" + opcao + " option:selected").text();
            var quantidade = $("#quantidade_" + opcao + " option:selected").text();
            console.log(produto);
            console.log(quantidade);
            
            if (produto != "" && quantidade != "") {
                preco = produto.substring(produto.search("\\$")+1).replace(",",".");
                preco = preco * quantidade;
                preco = preco.toString();
                
                preco=preco.replace(".",",");
                if (preco.search(",") == -1) {
                    preco=preco+",00";
                }
                int=preco.substring(0,preco.search(","));
                if (int.length == 0) {
                    int = "0";
                }
                dec=preco.substring(preco.search(",")+1);
                dec=dec.substring(0,2);
                
                
                if (dec.length == 1) {
                    dec = dec + "0";
                }
                preco = int + "," + dec;
                $("#subtotal_opcao"+opcao).text("Subtotal R$"+preco);
            } else {
                $("#subtotal_opcao"+opcao).text("Subtotal R$0,00");
            }
            
            /*var option = $("option:selected", this).text();
            preco = option.substring(option.search("\\$")+1).replace(",",".")
            console.log(preco);*/
        });
	});
	</script>
  </head>
  <body>
<?php
include "../config.php";
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
function ccase($str) {
	return ucwords(strtolower($str));
}

//Encontrar data da próxima entrega
if (!isset($_GET["cpf"])) {
?>
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
			<div class="large-4">
				Preencha seu CPF cadastrado
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
	$cpf=str_replace(" ","",$cpf);

    $msg="";

    //Identificar consumidor
    setLog("log.txt","CPF: ".$cpf." - Logou para preenchimento","");
	$sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
	$st = $conn->prepare($sql);
	$st->execute();
	if ($st->rowCount() == 0) {
		echo "<p>Consumidor não encontrado</p>";
		echo '<a href=".">Voltar</a>';
		setLog("log.txt","CPF: ".$cpf." - consumidor não encontrado.","");
		exit();
	}
	$rs=$st->fetchAll();
	if ($rs[0]["ativo"] == 0) {
	    echo "<p>Sua cesta está desativada essa semana.<br>";
	    echo "Caso isso seja um engano, entre em contato conosco!</p>";
		echo '<a href=".">Voltar</a>';
		setLog("log.txt","CPF: ".$cpf." - consumidor inativo.","");
		exit();
	}
	//Consumidor identificado, identificar próxima entrega
	$consumidor=$rs[0]["consumidor"];
	$endereco_entrega=$rs[0]["endereco"];
	$idConsumidor=$rs[0]["id"];
	$cota=$rs[0]["cota_imediato"];
    $comunidade=$rs[0]["comunidade"];
    $hoje = date("Y-m-d");

    //$sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' AND ".$comunidade."acomunidade <> '0' AND ".$comunidade."acomunidade <> '000' ORDER BY data ASC";
    $sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' AND LENGTH(".$comunidade."acomunidade) = 3 AND ".$comunidade."acomunidade <> '000' ORDER BY data ASC";
    $st = $conn->prepare($sql);
    $st->execute();
    
    if ($st->rowCount() == 0) {
        echo "Você não possui nenhuma entrega prevista. Caso ache isso um equívoco, gentileza entrar em contato conosco!";
        /*****/
        //exit();
    }
    
    $rsProxima =$st->fetch();
    $proximaEntrega = $rsProxima["data"];
    $idProximaEntrega = $rsProxima["id"];
    $frequencia=getFrequencias($conn,$idProximaEntrega);

    //Entregas de sábado fecham na quinta-feira (2 dias antes) e entregas de terça-feira fecham no sábado (3 dias antes)
    if (date("w",strtotime($proximaEntrega)) == 2) { //date("w") retorna o dia da semana em número, sendo domingo = 0
        //entrega terça-feira
        $limite = strtotime('-2 days', strtotime($proximaEntrega));
        $limite = strtotime('+ 12 hours', $limite);
    } else {
        //qualquer outra data
        $limite = strtotime('-2 days', strtotime($proximaEntrega));
        $limite = strtotime('+ 12 hours', $limite);
    }
    $limite = date("Y-m-d H:i",$limite);
    
    /*****/
    //Armeng para permitir testes fora do horário aberto para pedido
    //$limite = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));
    //$limite = date("Y-m-d H:i",$limite);
    /*****/
    
    $agora = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));
    $agora = date("Y-m-d H:i",$agora);
    //echo $agora;

    if (strtotime($agora) > strtotime($limite)) {
        $msg = "Ops... Ultrapassamos o horário limite para escolha dos variáveis essa semana. <br>";
        if (date("w",strtotime($proximaEntrega)) == 2) { //date("w") retorna o dia da semana em número, sendo domingo = 0
            //Entrega terça-feira
            $msg .= "Nossos produtores ficam aguardando o pedido de cada consumidor para realizar sua colheira e o envio do produto fresquinho para a terça-feira, ";
            $msg .= "por isso é tão importante mantermos o prazo limite de quinta-feira 12h.";
        } else {
            //Entrega qualquer outro dia
            $msg .= "Nossos produtores ficam aguardando o pedido de cada consumidor para realizar sua colheira e o envio do produto fresquinho para o sábado, ";
            $msg .= "por isso é tão importante mantermos o prazo limite de domingo 12h.";
        }
        setLog("log.txt","CPF: ".$cpf." - tentativa de preenchimento fora do horário","");
        echo $msg;
        exit();
    }

    
    //Lista de variáveis
    $sqlProdutos = "SELECT produtos.id AS id, produtos.nome AS nome FROM produtosVar LEFT JOIN produtos ON produtosVar.idProduto = produtos.id WHERE produtosVar.idCalendario = ".$idProximaEntrega." ORDER BY nome ASC";
    $st = $conn->prepare($sqlProdutos);
    $st->execute();
    if ($st->rowCount() == 0) {
        $msg = "Ainda não foram disponibilizados produtos para preenchimento";
        setLog("log.txt","CPF: ".$cpf." - tentativa de preenchimento sem produtos cadastrados","");
        echo $msg;
        exit();
    }
    $rsProdutos = $st->fetchAll();
    foreach ($rsProdutos as $rowProd) {
        $prodVariaveis[$rowProd["id"]]=$rowProd["nome"];
    }
	
	$sql = "SELECT * FROM PedidosVar WHERE idConsumidor = ".$idConsumidor." AND (idOpcao1 IS NOT NULL or idOpcao2 IS NOT NULL or escolhaOpcao1 IS NOT NULL or escolhaOpcao2 IS NOT NULL) AND idCalendario = ".$idProximaEntrega;
	$st = $conn->prepare($sql);
	$st->execute();
	$rsPedidoVar=$st->rowCount();
	if ($rsPedidoVar > 0) {
        $msg = "<p>&nbsp;Você já realizou seu pedido de variável para essa semana. Caso deseje alterar, entre em contato diretamente com o Livres.";
        setLog("log.txt","CPF: ".$cpf." - Logou para realizar pedido, mas pedido já havia sido realizado.","");
        $rsPedidoVar=$st->fetchAll();
        foreach ($rsPedidoVar as $rowVar) {
            $msg .= "<br>Seu pedido:";
            $contaOpcao=1;
            if (!is_null($rowVar["idOpcao1"])) {
                $msg .= "<br>1ª Opção: ".$prodVariaveis[$rowVar["idOpcao1"]];
                $contaOpcao=2;
            }
            if (!is_null($rowVar["idOpcao2"])) {
                $msg .= "<br>".$contaOpcao."ª Opção: ".$prodVariaveis[$rowVar["idOpcao2"]];
            }
            $msg .= "</p>";
        }
	} else {
        //Se for POST, salvar e mostrar mensagem padrão apenas
        if (isset($_POST["cpf"])) {
            setLog("log.txt","CPF: ".$cpf." - Clicou para salvar o pedido.","");
            $cpf=$_POST["cpf"];
            $opcao1=$_POST["opcao1"];
            $opcao2=$_POST["opcao2"];
            $qt1=$_POST["quantidade_1"];
            $qt2=$_POST["quantidade_2"];
            $delivery=$_POST["delivery"];
            $endereco_entrega=$_POST["endereco_entrega"];
            if ($opcao1 == "") { $opcao1 = "NULL"; };
            if ($opcao2 == "") { $opcao2 = "NULL"; };
            if ($qt1 == "") { $qt1 = "NULL"; };
            if ($qt2 == "") { $qt2 = "NULL"; };
            
            if (strlen($cpf) == 0 || ($opcao1 == "NULL" && $opcao2 == "NULL")) {
                $msg = "&nbsp;Você precisa selecionar ao menos a 1ª opção de variável.";
            } else {
                if (($opcao1 != "NULL" && $qt1 == "NULL") || ($opcao2 != "NULL" && $qt2 == "NULL")) {
                    $msg = "&nbsp;Você precisa selecionar a quantidade de produtos nas opções de variável preenchidas.";
                } else {
                    if ($endereco_entrega == "" && ($delivery=="Sim" || $delivery="Não sei ainda")) {
                        $msg = "&nbsp;Você precisa preencher o seu endereço de entrega.";
                    } else {
                        if (!isset($_POST["adicional"])) {
                            $msg = "&nbsp;Selecione se você deseja pagar adicional referente aos variáveis ou não.";
                            setLog("log.txt","CPF: ".$cpf." - Adicional não preenchido","");
                        } else {
                            if (strlen($_POST["delivery"]) == 0) {
                                $msg = '&nbsp;Selecione se você deseja delivery essa semana. Caso você ainda não tenha certeza, não tem problema, marque a opção "não sei".';
                                setLog("log.txt","CPF: ".$cpf." - Delivery não preenchido","");
                            } else {
                                $adicional = $_POST["adicional"];
								$delivery = $_POST["delivery"];
								if ($delivery == "Não") {
									$endereco_entrega = "";
								}
                                $sqlBusca = "SELECT * FROM PedidosVar WHERE idConsumidor = ".$idConsumidor." AND idCalendario = ".$idProximaEntrega;
                            	$st = $conn->prepare($sqlBusca);
                            	$st->execute();
                            	if ($st->rowCount() > 0) {
                            	    $sql = "UPDATE PedidosVar SET idOpcao1 = ".$opcao1.", idOpcao2 = ".$opcao2.", adicional = ".$adicional.", delivery='".$delivery."', endereco_entrega='". $endereco_entrega ."' WHERE idConsumidor = ".$idConsumidor." AND idCalendario = ".$idProximaEntrega;
                            	    $st = $conn->prepare($sql);
                    	            $st->execute();
                    	            setLog("log.txt","CPF: ".$cpf." - Pedido atualizado (já havia resposta Livres)",$sql);
                            	} else {
                                    $sql = "INSERT INTO PedidosVar (idConsumidor,idCalendario,idOpcao1,idOpcao2,quantidadeOpcao1, quantidadeOpcao2, adicional,delivery, endereco_entrega) VALUES (".$idConsumidor.",".$idProximaEntrega.",".$opcao1.",".$opcao2.",$qt1,$qt2,$adicional,'".$delivery."','".$endereco_entrega."')";
                                    $st = $conn->prepare($sql);
                    	            $st->execute();
                    	            setLog("log.txt","CPF: ".$cpf." - Pedido inserido",$sql);
                            	}
                	            $msg = "&nbsp;Seu pedido de variáveis foi realizado.";
                            }
                        }
                    }
                }
            }
        }
	}

	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia = 'Semanal' AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximaEntrega."' ORDER BY produtos.nome";
	$st = $conn->prepare($sql);
	$st->execute();
	$rsSemanal=$st->fetchAll();
	$contaSemanal=0;
	foreach ($rsSemanal as $row) {					
		$contaSemanal++;
	}
	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Semanal' OR Pedidos.Frequencia = 'Quinzenal') AND produtos.previsao <= '".$proximaEntrega."' ORDER BY produtos.nome";
	$st = $conn->prepare($sql);
	$st->execute();
	$rsQuinzenal=$st->fetchAll();
	$contaQuinzenal=0;
	$valorCesta=0;
	foreach ($rsQuinzenal as $row) {
		if ($contaSemanal > 0 && $row["Frequencia"] == "Quinzenal") {
			$valorCesta+=0.5*$row["preco"]*$row["Quantidade"];
		} else {
			$valorCesta+=$row["preco"]*$row["Quantidade"];
		}
		$contaQuinzenal++;
	}
	//Obter cota pelo formato novo
	$sqlCotaNovo = "SELECT * FROM pedidos_consolidados WHERE pedido_data='".date('Y-m-d H:i',strtotime($proximaEntrega))."' AND consumidor_id = '".$idConsumidor."'";
	$st = $conn->prepare($sqlCotaNovo);
	$st->execute();
	
	$rs = $st->fetch();
	$cota = $rs["pedido_cota"];
	$valorCesta = $rs["pedido_fixa"];
    if ($valorCesta == 0) {
        $valorCesta = $rs["pedido_mensal"];
    }
	if (getFreq($frequencia[$comunidade],"q") || (getFreq($frequencia[$comunidade],"s") && $contaSemanal > 0)) {
	} else {
	    if (strlen($msg) == 0) {
	        $msg = "Este sábado é uma entrega de cesta semanal e você só possui cestas quinzenais, portanto não há preenchimento de variáveis.";
	        $msg .= "<br>Na próxima semana estaremos esperando você! :)";
	        setLog("log.txt","CPF: ".$cpf." - Tentou acesso, porém não possui cesta essa semana.","");
	    }
	}
	?>
	<p></p>
		<div class="grid-container">
			<div class="grid-x grid-padding-x callout">
				<div class="large-12 cell">
				    <a href="../Variavel">Escolher outro consumidor</a>
					<h3><?php echo ccase($consumidor); ?></h3>
				</div>
			</div>
            <?php
            if ($msg != "") {
                ?>
                <div class="grid-x grid-padding-x">
				    <div class="large-12 medium-12 cell text-center callout" id="semanal">
    					<div class="grid-x grid-padding-x">
    					    <?php echo $msg; ?>
					    </div>
                    </div>
				</div>
                <?php
        	} else {
        	?>
                <div class="grid-x grid-padding-x">
    				<div class="large-12 medium-12 cell text-center callout" id="semanal">
    					<h5>Variáveis disponíveis para entrega em <?php echo date("d/m/Y",strtotime($proximaEntrega)); ?></h5>
    					<div class="grid-x grid-padding-x">
    					    <table>
    					        <tr>
    					            <td>Alimento Variável</td>
    					            <td>Preço Comunitário (Comboio Orgânico)</td>
    					            <td>Preço Mercado Comum/Impessoal</td>
    					            <td>Estoque</td>
    					        </tr>
    						<?php
    						//Loop de produtos variáveis
    						$sql = "SELECT produtos.unidade as unidade, produtosVar.estoque AS estoque, produtos.id as id, produtos.nome AS nome, produtos.preco AS preco, produtos.preco_mercado AS preco_mercado FROM produtosVar LEFT JOIN produtos ON produtos.id = produtosVar.idProduto WHERE idCalendario = ".$idProximaEntrega." ORDER BY produtos.nome";
    						$st = $conn->prepare($sql);
    						$st->execute();
    						$rsProdutos=$st->fetchAll();
    						//Loop de produtos variáveis
    						$asterisco = false; //controle da observação de produtos não orgânicos
    						foreach ($rsProdutos as $row) {
    						    $pos = strpos($row["nome"],"*");
    						    if ($pos !== false) {
    						        $asterisco = true;
    						    }
    						    echo '<tr><td>'.ccase($row["nome"]).'</td>';
    						    echo '<td>R$'.number_format($row["preco"],2,",",".").'</td><td>R$'.number_format($row["preco_mercado"],2,",",".")."</td>";
    						    echo '<td>';
    						    if ($row["estoque"] == 1000) {
    						        echo "Temos bastante!";
    						    } else {
    						        echo $row["estoque"];
    						    }
    						    echo '</td>';
    						    echo "</tr>";
    						}
    					    ?>
    					    </table>
    					    <?php
    					    if ($asterisco) {
    					        echo '&nbsp;* Alimento agroecológico não certificado, mas de confiança, produzido pela Rede de permacultura urbana';
    					    }
    					    ?>
    					</div>
    				</div>
    			</div>
    
    			<div class="grid-x grid-padding-x">
    				<div class="large-12 medium-12 cell text-center callout" id="semanal">
    					<h5>Escolha suas opções de variáveis para entrega de <?php echo date("d/m/Y",strtotime($proximaEntrega)); ?></h5>
    					<h5>Você possui R$<?php echo number_format($cota-$valorCesta,2,",",".");?> de variável para esta semana.</h5>
    					<?php echo '<a href="../Cestas/?cpf='.$cpf.'" target="_blank">Clique aqui caso deseje consultar sua cesta</a>'; ?>
    					<p></p>
    					<div class="grid-x grid-padding-x">
    					    <form id="form_variavel" name="form_variavel" method="POST" action="">
    					        <input type="hidden" name="cpf" id="cpf" value="<?php echo $_GET["cpf"]; ?>">
    					        <div class="grid-x grid-padding-x">
    					            <div class="large-2 medium-3 cell">
    					                1ª Opção
					                </div>
    					            <div class="large-3 medium-3 cell">
        					            <select recalc="sim" id="opcao1" name="opcao1" opcao="1">
            					            <option value=""></option>
                    						<?php
                    						//Loop de produtos variáveis
                    						$pdisp = "";
                    						foreach ($rsProdutos as $row) {	
                    						    if ($pdisp != "") $pdisp .= " | ";
    						                    $pdisp .= $row["nome"]." (".$row["id"].")"; //logar produtos disponibilizados
                    							echo '<option value="'.$row["id"].'">'.ccase($row["nome"]).' ('.$row["unidade"].') - R$'.number_format($row["preco"],2,",",".").'</option>';
                    						}
                    						//logar produtos listados
    					                    setLog("log.txt","CPF: ".$cpf." - Produtos disponibilizados Opção 1: ".$pdisp,"");
                    						?>
                						</select>
                					</div>
                					<div class="large-2 medium-3 cell">
                					    Quantidade
            					    </div>
                				    <div class="large-2 medium-3 cell">
                						<div class="input-group">
                    						<select recalc="sim" opcao="1" class="pedido input-group-field" id="quantidade_1" name="quantidade_1">
            									<option></option>
            									<option>0</option>
            									<option>1</option>
            									<option>2</option>
            									<option>3</option>
            									<option>4</option>
            									<option>5</option>
            									<option>6</option>
            									<option>7</option>
            									<option>8</option>
            									<option>9</option>
            									<option>10</option>
            									<option>11</option>
            									<option>12</option>
            								</select>
        								    <!--<span class="input-group-label"><?php //echo $row["unidade"]; ?></span>-->
        								</div>
        							</div>
        							<div id="subtotal_opcao1" class="large-3 medium-3 cell">
                    				    Subtotal R$0,00
                					</div>
    					        </div>
    					        <div class="grid-x grid-padding-x">
    					            <div class="large-2 medium-3 cell">
    					                2ª Opção
					                </div>
    					            <div class="large-3 medium-3 cell">
        					            <select recalc="sim" id="opcao2" name="opcao2" opcao="2">
            					            <option value=""></option>
                    						<?php
                    						//Loop de produtos variáveis
                    						$pdisp = "";
                    						foreach ($rsProdutos as $row) {					
                    						    if ($pdisp != "") $pdisp .= " | ";
    						                    $pdisp .= $row["nome"]." (".$row["id"].")"; //logar produtos disponibilizados
                    							echo '<option value="'.$row["id"].'">'.ccase($row["nome"]).' ('.$row["unidade"].') - R$'.number_format($row["preco"],2,",",".").'</option>';
                    						}
                    						//logar produtos listados
    					                    setLog("log.txt","CPF: ".$cpf." - Produtos disponibilizados Opção 2: ".$pdisp,"");
                    						?>
                						</select>
                					</div>
                					<div class="large-2 medium-3 cell">
                					    Quantidade
            					    </div>
                				    <div class="large-2 medium-3 cell">
                						<div class="input-group">
                    						<select recalc="sim" opcao="2" class="pedido input-group-field" id="quantidade_2" name="quantidade_2">
            									<option></option>
            									<option>0</option>
            									<option>1</option>
            									<option>2</option>
            									<option>3</option>
            									<option>4</option>
            									<option>5</option>
            									<option>6</option>
            									<option>7</option>
            									<option>8</option>
            									<option>9</option>
            									<option>10</option>
            									<option>11</option>
            									<option>12</option>
            								</select>
        								    <!--<span class="input-group-label"><?php //echo $row["unidade"]; ?></span>-->
        								</div>
        							</div>
        							<div id="subtotal_opcao2" class="large-3 medium-3 cell">
                    				    Subtotal R$0,00
                					</div>
    					        </div>
        						<p>
        						    <label for="delivery">Você deseja delivery essa semana?</label>
        						    <select id="delivery" name="delivery">
        						        <option value=""></option>
        						        <option value="Sim">Sim</option>
        						        <option value="Não">Não</option>
        						        <option value="Não sei ainda">Não sei ainda</option>
        						    </select>
        						</p>
        						<p id="box_endereco_entrega" style="display: none;">
        						    <label for="endereco_entrega">Qual endereço de entrega?</label>
        						    <input type="text" id="endereco_entrega" name="endereco_entrega" value="<?php echo $endereco_entrega; ?>" />
        						</p>
        						<p><input type="radio" group="adicional" name="adicional" id="adicionalSim" value="1" />
        						<label for="adicionalSim">Desejo que as duas opções de produtos sejam incluídas na minha cesta, mesmo que isso ultrapasse minha cota de variável. Pagarei a diferença.</label></p>
        						<p><input type="radio" group="adicional" name="adicional" id="adicionalNao" value="0" />
        						<label for="adicionalNao">Não quero que minha cota de variável seja ultrapassada. Coloque apenas o necessário de produto para atingir o valor de R$<?php echo number_format($cota-$valorCesta,2,",",".");?>.</label></p>
        						<p><input type="submit" value="Salvar" id="salvar" name="salvar" /></p>
    						</form>
    					</div>
    				</div>
    			</div>
    		<?php
        	}
        	?>
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
