<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

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
	<link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<link rel="stylesheet" href="painel.css">
	<style>
		table, td {
			border: 1px solid #bbd6ee;
			border-collapse: collapse;
			text-align: center;
			font-family: Calibri;
			<?php
            if (isset($_GET["imprimir"])) {
                echo 'font-size: 10px;';
            }
            ?>
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
  </head>
  <body>
  
<?php
if (!isset($_GET["imprimir"])) {
	if (isset($_GET["data"])) {
		$getData = $livres->dataPelaString($_SESSION["data_consulta"]);
	}	
	echo $twig->render('menu.html', [
		"titulo" => "LivresBS",
		"menu_datas" => $calendario->listaDatas(),
		"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
		"frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
	]);
}
?>

<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
if (!isset($_GET["data"]) || !isset($_SESSION["data_id"])) {
	echo '<div class="alert alert-danger" role="alert">Selecione uma data com entrega planejada</div>';
} else {
	if (strlen($_GET["data"]) > 0) {
	    /* Salvar dados manuais das cestas */
	    if (isset($_POST["data"])) {
	        $sqlSave = "SELECT * FROM Consumidores WHERE ativo = 1";
	        $st = $conn->prepare($sqlSave);
	        $st->execute();
	        $rsSave=$st->fetchAll();
	        foreach ($rsSave as $save) {
	            //$cesta = $_POST["cesta_variavel_".$save["id"]];
	            $cesta="";
	            if (isset($_POST["diferenca_".$save["id"]])) {
    	            $diferenca=$_POST["diferenca_".$save["id"]];
    	            $diferenca=str_replace(",",".",$diferenca);
    	            $diferenca=str_replace("R","",$diferenca);
    	            $diferenca=str_replace("r","",$diferenca);
    	            $diferenca=str_replace("$","",$diferenca);
    	            $direrenca=trim($diferenca);
    	            if ($diferenca == "") {
    	                $diferenca="NULL";
    	            }
    	            $escolha1 = $_POST["escolha1_".$save["id"]];
                    $escolha2 = $_POST["escolha2_".$save["id"]];
                    $quantidade1 = $_POST["quantidade1_".$save["id"]];
                    $quantidade1=str_replace(",",".",$quantidade1);
                    $quantidade2 = $_POST["quantidade2_".$save["id"]];
                    $quantidade2=str_replace(",",".",$quantidade2);
                    
                    $cotavariavel = $_POST["cotavariavel_".$save["id"]];
                    
                    if ($escolha1 == "") { $escolha1 = "NULL"; }
                    if ($escolha2 == "") { $escolha2 = "NULL"; }
                    if ($quantidade1 == "") { $quantidade1 = "NULL"; }
                    if ($quantidade2 == "") { $quantidade2 = "NULL"; }
    	            
    	            $sqlSearch = "SELECT * FROM PedidosVar WHERE idCalendario = ".$_POST["data"]." AND idConsumidor = ".$save["id"];
    	            $st = $conn->prepare($sqlSearch);
    	            $st->execute();
    	            $rsSearch=$st->fetchAll();
    	            if ($st->rowCount() > 0) {
    	                $sqlSave2 = "UPDATE PedidosVar SET cesta_variavel = '".$cesta."', diferenca=".$diferenca.", ";
    	                $sqlSave2 .= "escolhaOpcao1=".$escolha1.", escolhaOpcao2=".$escolha2.", quantidadeOpcao1=".$quantidade1.", quantidadeOpcao2=".$quantidade2." ";
    	                $sqlSave2 .= "WHERE id = ".$rsSearch[0]["id"];
    	            } else {
    	                if ($diferenca != "NULL" || $escolha1 != "NULL" || $escolha2 != "NULL" || $quantidade1 != "NULL" || $quantidade2 != "NULL") {
    	                    $sqlSave2 = "INSERT INTO PedidosVar (idConsumidor,idCalendario,escolhaOpcao1,escolhaOpcao2,quantidadeOpcao1,quantidadeOpcao2,cesta_variavel,diferenca,resposta_livres) VALUES (";
    	                    $sqlSave2 .= $save["id"].",".$_POST["data"].",".$escolha1.",".$escolha2.",".$quantidade1.",".$quantidade2.",'".$cesta."',".$diferenca.",1)";
    	                } else {
    	                    $sqlSave2="";
    	                }
    	            }
    	            if (strlen($sqlSave2)>0) {
    	                $st=$conn->prepare($sqlSave2);
    	                $st->execute();
    	                setlog('log.txt','Alteração pedido variável consumidor (cota exibida: '.$cotavariavel.')',$sqlSave2);
    	            }
	            }
	        }
	    }
	    /* FIM DADOS MANUAIS CESTA */
	    /* SALVAR PEDIDO REAL TOTAL */
	    if (isset($_POST["salvaPedidoGeral"])) {
	        $sql = "SELECT produtos.unidade AS unidade, produtosVar.id AS id, produtosVar.Quantidade AS Quantidade FROM produtosVar LEFT JOIN produtos ON produtosVar.idProduto = produtos.id WHERE idCalendario = ".$getData;
	        $st = $conn->prepare($sql);
	        $st->execute();
	        $rs = $st->fetchAll();
	        foreach ($rs as $row) {
	            $quantidade = $_POST["quantidadePedido_".$row["id"]];
	            if ($quantidade == "") {
	                $quantidade = "NULL";
                } else {
                    /*if (strtolower($row["unidade"]) == "dúzia") {
                        $quantidade = $quantidade/12;
                    }*/
                }
	            $sqlUpdate = "UPDATE produtosVar SET Quantidade = ".$quantidade." WHERE id = ".$row["id"];
	            $st = $conn->prepare($sqlUpdate);
	            $st->execute();
	            setlog('log.txt','Alteração pedido variável total geral',$sqlUpdate);
	        }
	    }
	    /* FIM SALVAR PEDIDO REAL TOTAL */
		$sql = "SELECT * FROM Calendario WHERE id = ".$getData;
		$st = $conn->prepare($sql);
		$st->execute();
		$rs=$st->fetchAll();
		$dataEntrega = $rs[0]["data"];
		//$frequenciaCodigo[1] = $rs[0]["1acomunidade"];
		//$frequenciaCodigo[2] = $rs[0]["2acomunidade"];
		$frequenciaCodigo = getFrequencias($conn,$getData);
		$dataEntrega = strtotime($dataEntrega);
		$proximoSabado=date("Y-m-d",$dataEntrega);
		$counter=0;
		/* OBTER INFO DE QUEM NÃO REALIZOU PEDIDO */
		if (!isset($_GET["imprimir"])) {
    		$sqlFalta = "SELECT * FROM Consumidores LEFT JOIN PedidosVar ON PedidosVar.idConsumidor = Consumidores.id AND PedidosVar.idCalendario = ".$getData." WHERE Consumidores.ativo=1";
    		$st = $conn->prepare($sqlFalta);
    		$st->execute();
    		$rsFalta=$st->fetchAll();
    		$faltaResponder = "";
    		$contaFalta=0;
    		$contaTotal=0;
    		foreach ($rsFalta as $rowFalta) {
    		    if ($rowFalta["idOpcao1"] == "" && $rowFalta["idOpcao2"] == "") {
    		        if (strlen($faltaResponder) > 0) { $faltaResponder .= ", "; }
    		        $faltaResponder .= ucwords(mb_strtolower($rowFalta["consumidor"],'UTF-8'));
    		        $contaFalta++;
    		    }
    		    $contaTotal++;
    		}
    		$msgFalta = "Total de respostas: ".($contaTotal-$contaFalta)." de ".$contaTotal."<br>";
    		if ($contaFalta > 0) {
    		    //Desabilitado temporariamente até resolver o problema dos consumidores que são semanais e estão aparecendo na lista de quinzenal
    		    //$msgFalta .= "Consumidores pendentes: ".$faltaResponder;
		    }
		}
		/* (FIM) OBTER INFO DE QUEM NÃO REALIZOU PEDIDO */
		if (!isset($_GET["imprimir"])) {
		    echo '<p>'.$msgFalta.'</p>';
		    echo '<p>Link para realizar pedidos: <a href="../Variavel" target="_blank">Clique Aqui</a></p>';
		}
		
		?>
        <form method="POST" action="">
        <?php
        if (!isset($_GET["imprimir"])) {
        ?>
        <input type="submit" id="submit" name="submit" value="Salvar Dados" /> - <a href="?data=<?php echo $getData; ?>&imprimir=1" target="_blank">Versão para Impressão</a>
        <?php
        }
        ?>
        <input type="hidden" id="data" name="data" value="<?php echo $getData; ?>" />
		<table id="capture" style="vertical-align:top;">
	    <tr class="firstLine">
		<td width="250">* CONSUMIDOR CONSCIENTE *</td>
		<td width="100">* FAIXA VARIÁVEL *</td>
		<td width="350">Opções Consumidor</td>
		<td width="350">Definição Produto 1</td>
		<td width="400">Definição Produto 2</td>
		<td width="100">Diferença</td>
		<td width="70">Adicional</td>
		<td width="200">Data/Hora Pedido</td>
		<td width="70">Delivery</td>
		</tr>
		<?php
		/* Pegar ordem dos pedidos */
		$sqlOrdem = "SELECT * FROM PedidosVar WHERE idCalendario = ".$getData." AND resposta_livres = 0";
		$st = $conn->prepare($sqlOrdem);
		$st->execute();
		$rsOrdem = $st->fetchAll();
		
		$conta=0;
		$ordem[]="";
		foreach ($rsOrdem as $row) {
		    $conta++;
		    $ordem[$row["idConsumidor"]] = $conta;
		}
		
		/* LISTA DE VARIÁVEIS DA SEMANA */
    	$sqlLVar = "SELECT produtos.nome AS nome, produtos.unidade AS unidade, produtos.preco AS preco, produtos.id AS id FROM produtosVar LEFT JOIN produtos ON produtosVar.idProduto = produtos.id WHERE idCalendario = ".$getData;
    	$st = $conn->prepare($sqlLVar);
    	$st->execute();
    	$rsLVar=$st->fetchAll();
    	$selectVar="";
    	foreach ($rsLVar as $lVar) {
    	    $listaVar[$lVar["id"]]["nome"] = $lVar["nome"];
    	    $listaVar[$lVar["id"]]["preco"] = $lVar["preco"];
    	    $listaVar[$lVar["id"]]["unidade"] = $lVar["unidade"];
    	    $selectVar .= '<option value="'.$lVar["id"].'">'.ccase($lVar["nome"]).' - R$'.number_format($lVar["preco"],2,",",".").'</option>\r\n';
    	}
		
		$sqlCons = "SELECT * FROM Consumidores WHERE ativo = 1 AND comunidade <> 0 ORDER BY consumidor ASC";
		//$sqlCons = "SELECT * FROM pedidos_consolidados ped LEFT JOIN Consumidores cons ON cons.id = ped.consumidor_id WHERE ped.pedido_data = '".date('Y-m-d H:i',strtotime($proximoSabado))."' ORDER BY cons.consumidor ASC";
		$st = $conn->prepare($sqlCons);
		$st->execute();
		$rsCons=$st->fetchAll();
		foreach ($rsCons as $cons) {
		    $idConsumidor=$cons["id"];
		    $comunidade=$cons["comunidade"];
		    //$cota=$cons["cota_imediato"];
		    $nome=$cons["consumidor"];
		    $cpf=$cons["cpf"];
		    //Calcular cota variável
    		/******
    		 * Mudança em 05/08/2020 09:17 para transformar as duas queries abaixo em uma única que vem na sequência
    		$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia = 'Semanal' AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximoSabado."' ORDER BY produtos.nome";
        	$st = $conn->prepare($sql);
        	$st->execute();
        	$rsSemanal=$st->fetchAll();
        	$contaSemanal=0;
        	foreach ($rsSemanal as $row) {					
        		$contaSemanal++;
        	}
        	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia <> 'Mensal' AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximoSabado."' ORDER BY produtos.nome";
        	$st = $conn->prepare($sql);
        	$st->execute();
        	$rsQuinzenal=$st->fetchAll();
        	$contaQuinzenal=0;
        	//$valorCesta=0;
        	foreach ($rsQuinzenal as $row) {
        		if ($contaSemanal > 0 && $row["Frequencia"] == "Quinzenal") {
        			//$valorCesta+=0.5*$row["preco"]*$row["Quantidade"];
        		} else {
        			//$valorCesta+=$row["preco"]*$row["Quantidade"];
        		}
        		$contaQuinzenal++;
        	}******/
        	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximoSabado."' ORDER BY produtos.nome";
        	$st = $conn->prepare($sql);
        	$st->execute();
        	$rsSemanal=$st->fetchAll();
        	$contaSemanal=0;
        	$contaQuinzenal=0;
        	$contaMensal=0;
        	foreach ($rsSemanal as $row) {
        	    if ($row["Frequencia"] == 'Semanal') {
        		    $contaSemanal++;
        	    }
        	    if ($row["Frequencia"] == 'Semanal' || $row["Frequencia"] == 'Quinzenal') {
        	        $contaQuinzenal++;
        	    }
        	    if ($row["Frequencia"] == 'Mensal') {
        	        $contaMensal++;
        	    }
        	}
        	/* fim mudança 05/08/2020 */
        	//obter cota e valor da cesta pelo modo novo
        	$sql = "SELECT * FROM pedidos_consolidados WHERE pedido_data = '".date('Y-m-d H:i',strtotime($proximoSabado))."' AND consumidor_id = ".$idConsumidor;
        	$st = $conn->prepare($sql);
        	$st->execute();
        	$rsCotaNova = $st->fetch();
        	
        	$cota = $rsCotaNova["pedido_cota"];
        	$valorCesta = $rsCotaNova["pedido_fixa"];
        	//if abaixo permite que hajam cestas apenas mensais. nesse caso, o variável é calculado com base no preço da cesta mensal e não semanal/quinzenal
        	if ($valorCesta == 0) {
        	    $valorCesta = $rsCotaNova["pedido_mensal"];
		    }
        	
        	/*if ($cota == 0 || $valorCesta == 0) {
        	    echo "Erro ao obter dados -> consumidor_id = ".$idConsumidor;
        	    exit();
        	}*/
        	if ((getFreq($frequenciaCodigo[$comunidade],"q") && $contaQuinzenal>0) || (getFreq($frequenciaCodigo[$comunidade],"s") && $contaSemanal > 0) || (getFreq($frequenciaCodigo[$comunidade],"m") && $contaMensal > 0)) {
        	    $mostrarCesta = true;
        	    if ($cota == 0 || $valorCesta == 0) {
        	        echo '<h5 style="background-color: red;">';
                    echo "Erro. Consumidor ativo e sem pedido consolidado -> ".$cons["consumidor"]."(".$cons["id"].")";
                    echo '</h5>';
        	        $mostrarCesta = false;
        	        //exit();
        	    }
        	    
        	    if ($rsCotaNova["pedido_fixa"] == 0 && $rsCotaNova["pedido_mensal"] != 0) {
        	        $mostrarCesta = true;
        	        echo $cons["consumidor"];
        	    }
        	    
        	    if ($mostrarCesta) {
            	    //Fim cálculo cota variável
                	//Obter pedido já feito
                	$sql = "SELECT * FROM PedidosVar WHERE idConsumidor = ".$idConsumidor." AND idCalendario = ".$getData;
                	$st = $conn->prepare($sql);
                	$st->execute();
                	$rsVar=$st->fetchAll();
                	$opc1="";
                	$opc2="";
                	$cestaVariavel="";
                	$diferenca="";
                	$dataPedido="";
                	$semResposta=true;
                	$respostaLivres=0;
                	$adicional=0;
                	$quantidadeOpcao1="";
                	$quantidadeOpcao2="";
                	$escolhaOpcao1="";
                	$escolhaOpcao2="";
                	$unidade1="";
                	$unidade2="";
                	$delivery="";
            		foreach ($rsVar as $row) {
            		    if (!is_null($row["idOpcao1"])) {
            		        $opc1=$listaVar[$row["idOpcao1"]]["nome"]." - R$".number_format($listaVar[$row["idOpcao1"]]["preco"],2,",",".");
            		    } else {
            		        $opc1="";
            		    }
            		    if (!is_null($row["idOpcao2"])) {
                            $opc2=$listaVar[$row["idOpcao2"]]["nome"]." - R$".number_format($listaVar[$row["idOpcao2"]]["preco"],2,",",".");   
            		    } else {
            		        $opc2="";
            		    }
            		    $cestaVariavel=$row["cesta_variavel"];
            		    $diferenca=$row["diferenca"];
            		    $dataPedido=$row["data_pedido"];
            		    $semResposta=false;
            		    $respostaLivres=$row["resposta_livres"];
            		    $adicional = $row["adicional"];
            		    $quantidadeOpcao1=$row["quantidadeOpcao1"];
                    	$quantidadeOpcao2=$row["quantidadeOpcao2"];
                    	$escolhaOpcao1=$row["escolhaOpcao1"];
                    	$escolhaOpcao2=$row["escolhaOpcao2"];
                    	$delivery=$row["delivery"];
                    	if (!is_null($row["idOpcao1"])) {
                    	    $unidade1=$listaVar[$row["idOpcao1"]]["unidade"];
                    	}
                    	if (!is_null($row["idOpcao2"])) {
            		        $unidade2=$listaVar[$row["idOpcao2"]]["unidade"];
                    	}
            		}
        		    if ($counter % 2 == 0) {
        			    echo '<tr class="lineBlank">';
        		    } else {
        			    echo '<tr class="lineColor">';
        		    }
        		    if (isset($_GET["imprimir"])) {
        		        echo "<td>".ucwords(mb_strtolower(abvNome($nome),'UTF-8'))." (G".$comunidade.")</td>";
        		    } else {
        		        echo '<td><a href="../Variavel/?cpf='.$cpf.'" target="_blank">'.ucwords(mb_strtolower(abvNome($nome),'UTF-8')).' (G'.$comunidade.')</a></td>';
        		    }
        		    echo "<td>";
        		    //em alguns casos, quando desconto é aplicado na cesta da pessoa, o valor da cesta fica negativo.
        		    //as linhas abaixo servem para que o valor de variável exibido seja o valor necessário para zerar o preço da cesta
        		    if ($valorCesta < 0) {
            		    echo "R$".number_format((-1*$valorCesta),2,",",".");
        		    } else {
        		        echo "R$".number_format(($cota-$valorCesta),2,",",".");
        		    }
        		    echo '<input type="hidden" name="cotavariavel_'.$idConsumidor.'" id="cotavariavel_'.$idConsumidor.'" value="R$'.number_format(($cota-$valorCesta),2,",",".").'" />';
        		    echo "</td>";
        	        //Opções consumidor
        	        if ($opc1 != "") {
        	            echo "<td>".$opc1;
        	            if ($opc2 != "") {
        	                echo " | ".$opc2;
        	            }
                    } else {
                        echo "<td>".$opc2;
                    }
                    echo "</td>";
        		    /* TRATAMENTO DA ESCOLHA DOS PRODUTOS PELO LIVRES */
    		        $nome1 = "";
    		        $nome2 = "";
    		        $unidade1 = "";
    		        $unidade2 = "";
    		        $variavelTotal=0;
    		        $esc1 = '<select id="escolha1_'.$idConsumidor.'" name="escolha1_'.$idConsumidor.'">';
    		        $esc1 .= '<option value=""></option>';
    		        foreach ($rsLVar as $lVar) {
    		            if ($escolhaOpcao1 == $lVar["id"]) {
                	        $esc1 .= '<option selected="selected" value="'.$lVar["id"].'">'.ccase($lVar["nome"]).' - R$'.number_format($lVar["preco"],2,",",".").'</option>';
                	        $nome1 = $lVar["nome"];
                	        $unidade1 = $lVar["unidade"];
                	        //if (strtolower($unidade1) == "dúzia") {
                	        //    $variavelTotal+=$lVar["preco"]*$quantidadeOpcao1/12;
                	        //} else {
                	            $variavelTotal+=$lVar["preco"]*$quantidadeOpcao1;
                	        //}
    		            } else {
    		                $esc1 .= '<option value="'.$lVar["id"].'">'.ccase($lVar["nome"]).' - R$'.number_format($lVar["preco"],2,",",".").'</option>';
    		            }
                	}
    		        $esc1 .= '</select>';
    		        $esc2 = '<select id="escolha2_'.$idConsumidor.'" name="escolha2_'.$idConsumidor.'">';
    		        $esc2 .= '<option value=""></option>';
    		        foreach ($rsLVar as $lVar) {
    		            if ($escolhaOpcao2 == $lVar["id"]) {
                	        $esc2 .= '<option selected="selected" value="'.$lVar["id"].'">'.ccase($lVar["nome"]).' - R$'.number_format($lVar["preco"],2,",",".").'</option>';
                	        $nome2 = $lVar["nome"];
                	        $unidade2 = $lVar["unidade"];
                	        //if (strtolower($unidade2) == "dúzia") {
                	        //    $variavelTotal+=$lVar["preco"]*$quantidadeOpcao2/12;
                	        //} else {
                	            $variavelTotal+=$lVar["preco"]*$quantidadeOpcao2;
                	        //}
    		            } else {
    		                $esc2 .= '<option value="'.$lVar["id"].'">'.ccase($lVar["nome"]).' - R$'.number_format($lVar["preco"],2,",",".").'</option>';
    		            }
                	}
    		        $esc2 .= '</select>';
    		        if (!isset($_GET["imprimir"])) {
        		        echo '<td>';
        		        echo '<input type="text" size="5" name="quantidade1_'.$idConsumidor.'" id="quantidade1_'.$idConsumidor.'" value="'.$quantidadeOpcao1.'" /> x ';
        		        echo $esc1.'</td>';
        		        echo '<td>';
        		        echo '<input type="text" size="5" name="quantidade2_'.$idConsumidor.'" id="quantidade2_'.$idConsumidor.'" value="'.$quantidadeOpcao2.'" /> x ';
        		        echo $esc2.'</td>';
    		        } else {
    		            if ($nome1 != "" && $quantidadeOpcao1 > 0) {
    		                //if (strtolower($unidade1) == "dúzia") {
    		                //  $unidade1 = "unidade";
    		                //}
        		            echo '<td>'.$quantidadeOpcao1.' '.$unidade1.' x '.$nome1.'</td>';
    		            } else {
    		                echo '<td></td>';
    		            }
        		        if ($nome2 != "" && $quantidadeOpcao2 > 0) {
        		            //if (strtolower($unidade2) == "dúzia") {
    		                //  $unidade2 = "unidade";
    		                //}
        		            echo '<td>'.$quantidadeOpcao2.' '.$unidade2.' x '.$nome2.'</td>';
    		            } else {
    		                echo '<td></td>';
    		            }
    		        }
        		    /* FIM DA TRATAMENTO ESCOLHA DOS PRODUTOS PELO LIVRES */
        		    
        		    $diferenca=str_replace(".",",",$diferenca);
        		    $diferenca=str_replace("R","",$diferenca);
        		    $diferenca=str_replace("r","",$diferenca);
        		    $diferenca=str_replace("$","",$diferenca);
        		    $direrenca=trim($diferenca);
        		    $variavelTotal=$variavelTotal-($cota-$valorCesta);
        		    if (!isset($_GET["imprimir"])) {
        		        echo '<td><input size="10" type="text" value="'.$diferenca.'" id="diferenca_'.$idConsumidor.'" name="diferenca_'.$idConsumidor.'" />';
        		        if ($variavelTotal < 0) {
        		            $variavelTotal="R$".number_format($variavelTotal,2,",",".");
        		            echo '<span style="color:#00FF00;">'.$variavelTotal.'</span>';
        		        } else {
        		            $variavelTotal="R$".number_format($variavelTotal,2,",",".");
        		            echo '<span style="color:#FF0000;">'.$variavelTotal.'</span>';
        		        }
        		        echo '</td>';
        		    } else {
        		        echo '<td>'.$diferenca.'</td>';
        		    }
        		    echo '<td>'.(($adicional == 1) ? "Sim" : "Não").'</td>';
        		    if (array_key_exists($idConsumidor,$ordem)) {
        	            echo "<td>".$ordem[$idConsumidor]." - ".date("d/m/Y H:i:s",strtotime($dataPedido))."</td>";
        		    } else {
        		        if (isset($respostaLivres) && $respostaLivres == 1) {
        		            echo "<td>Resposta Livres</td>";
        		        } else {
        		            echo "<td>Sem resposta</td>";
        		        }
        		    }
        		    echo '<td>'.$delivery.'</td>';
        		    echo "</tr>";
                }
		    }
	    }
	}
}
?>
</table>
</form>
<?php
//TABELA RESUMO DOS PEDIDOS -->
echo '<p></p>';
if (isset($_GET["data"])) {
	$getData = $livres->dataPelaString($_SESSION["data_consulta"]);
    $sql = "SELECT produtosVar.Quantidade AS Quantidade, produtosVar.id AS IDProdVar, produtos.id, produtos.nome, produtos.produtor, produtos.unidade, produtos.preco_produtor FROM produtosVar LEFT JOIN produtos ON produtos.id = produtosVar.idProduto WHERE produtosVar.idCalendario = ".$getData;
    $st = $conn->prepare($sql);
    $st->execute();
    $rs = $st->fetchAll();
    if ($st->rowCount() > 0) {
        foreach ($rs as $row) {
            $arrProdutos[$row["id"]]["nome"] = $row["nome"];
            /*if ($row["unidade"] == "dúzia") {
                $arrProdutos[$row["id"]]["unidade"] = "unidade";
                $arrProdutos[$row["id"]]["preco_produtor"] = $row["preco_produtor"]/12;
                if (!is_null($row["Quantidade"])) {
                    $arrProdutos[$row["id"]]["quantidadePedido"] = round($row["Quantidade"]*12,0);
                } else {
                    $arrProdutos[$row["id"]]["quantidadePedido"] = null;
                }
            } else {*/
                $arrProdutos[$row["id"]]["unidade"] = $row["unidade"];
                $arrProdutos[$row["id"]]["preco_produtor"] = $row["preco_produtor"];
                $arrProdutos[$row["id"]]["quantidadePedido"] = $row["Quantidade"];
            //}
            $arrProdutos[$row["id"]]["produtor"] = $row["produtor"];
            $arrProdutos[$row["id"]]["quantidadeMinima"] = 0;
            $arrProdutos[$row["id"]]["IDProdVar"] = $row["IDProdVar"];
        }
        $sql = "SELECT * FROM PedidosVar WHERE idCalendario = ".$getData;
        $st = $conn->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            foreach($rs as $row) {
                if (!is_null($row["escolhaOpcao1"])) {
                    $arrProdutos[$row["escolhaOpcao1"]]["quantidadeMinima"] += $row["quantidadeOpcao1"];
                }
                if (!is_null($row["escolhaOpcao2"])) {
                    $arrProdutos[$row["escolhaOpcao2"]]["quantidadeMinima"] += $row["quantidadeOpcao2"];
                }
            }
            //Reescrever Array para separar por produtor
            foreach ($arrProdutos as $arr) {
                $arrProdutor[$arr["produtor"]][$arr["nome"]]["unidade"] = $arr["unidade"];
                $arrProdutor[$arr["produtor"]][$arr["nome"]]["quantidadeMinima"] = $arr["quantidadeMinima"];
                $arrProdutor[$arr["produtor"]][$arr["nome"]]["preco_produtor"] = $arr["preco_produtor"];
                $arrProdutor[$arr["produtor"]][$arr["nome"]]["quantidadePedido"] = $arr["quantidadePedido"];
                $arrProdutor[$arr["produtor"]][$arr["nome"]]["IDProdVar"] = $arr["IDProdVar"];
            }
            //Mostrar tabela de pedidos
            if (!isset($_GET["imprimir"])) {
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="salvaPedidoGeral" id="salvaPedidoGeral" value="1" />';
            }
            echo '<table>';
            echo '<tr>';
            echo '<td>Produtor</td>';
            echo '<td>Produto</td>';
            echo '<td>Unidade</td>';
            if (!isset($_GET["imprimir"])) {
                echo '<td>Quantidade Mínima</td>';
            }
            echo '<td>Quantidade Pedido</td>';
            echo '<td>Preço Produtor</td>';
            echo '</tr>';
            foreach ($arrProdutor as $nomeProdutor=>$produtor) {
                //Nome Produtor
                echo '<tr><td>'.$nomeProdutor.'</td>';
                //Produtos
                $totalProdutor=0;
                $conta = 0;
                foreach ($produtor as $nomeProduto=>$produto) {
                    if ($conta > 0) {
                        echo '<tr>';
                        echo '<td></td>';
                    }
                    echo '<td>'.$nomeProduto.'</td>';
                    echo '<td>'.$produto["unidade"].'</td>';
                    if (!isset($_GET["imprimir"])) {
                        if ($produto["quantidadePedido"] != $produto["quantidadeMinima"]) {
                            echo '<td style="color: #FF0000;">'.$produto["quantidadeMinima"].'</td>';
                        } else {
                            echo '<td style="color: #00FF00;">'.$produto["quantidadeMinima"].'</td>';
                        }
                        echo '<td><input type="text" name="quantidadePedido_'.$produto["IDProdVar"].'" id="quantidadePedido_'.$produto["IDProdVar"].'" value="'.$produto["quantidadePedido"].'" /></td>';
                        //echo '<td></td>';
                    } else {
                        echo '<td>'.$produto["quantidadePedido"].'</td>';
                    }
                    echo '<td>R$'.number_format($produto["preco_produtor"],3,",",".").'</td>';
                    echo '</tr>';
                    $totalProdutor+=$produto["quantidadePedido"]*$produto["preco_produtor"];
                    $conta++;
                }
                //Total produtor
                echo '<tr>';
                echo '<td>Total '.$nomeProdutor.'</td>';
                echo '<td colspan="4">R$'.number_format($totalProdutor,2,",",".").'</td>';
                echo '</tr>';
            }
            if (!isset($_GET["imprimir"])) {
                echo '<tr>';
                echo '<td colspan="6"><input type="submit" name="submit1" id="submit1" value="Salvar Pedido Variáveis" /></td>';
                echo '</tr>';
            }
            echo '</table>';
            if (!isset($_GET["imprimir"])) {
                echo '</form>';
            }
        }
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>

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
<?php
function ccase($str) {
	return ucwords(mb_strtolower($str,'UTF-8'));
}
function abvNome($nome) {
    $path = explode(" ",$nome);
    for ($i = 0;$i < count($path); $i++) {
        if ($i == 0) {
            $nome = $path[$i];
        } else {
            if ($i == count($path)-1) {
                $nome .= " ".$path[$i];
            } else { 
                $nome .= " ".substr($path[$i],0,1);
            }
        }
    }
    return $nome;
}
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