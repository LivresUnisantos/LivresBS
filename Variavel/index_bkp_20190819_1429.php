<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Orgânico</title>
    <link rel="stylesheet" href="../css/foundation.css">
    <link rel="stylesheet" href="..;css/app.css">
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
$hoje = date("Y-m-d");
$sql = "SELECT * FROM Calendario WHERE data >= '".$hoje."' ORDER BY data ASC";
$st = $conn->prepare($sql);
$st->execute();
$rsSabado =$st->fetchAll();
$proximoSabado = $rsSabado[0]["data"];
$idProximoSabado = $rsSabado[0]["id"];
if (!isset($_GET["cpf"])) {
?>
	<div class="grid-container">
		<div class="grid-x grid-padding-x">
			<div class="large-4">
				Escolha suas opções de variáveis para a entrega de <?php echo date("d/m/Y",strtotime($proximoSabado)); ?>
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

    $limite = strtotime('-2 days', strtotime($proximoSabado));
    $limite = strtotime('+ 12 hours', $limite);
    $limite = date("Y-m-d H:i",$limite);
    $agora = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));
    $agora = date("Y-m-d H:i",$agora);

    if (strtotime($agora) > strtotime($limite)) {
        $msg = "Ops... Ultrapassamos o horário limite para escolha dos variáveis essa semana. <br>";
        $msg .= "Nossos produtores ficam aguardando o pedido de cada consumidor para realizar sua colheira e o envio do produto fresquinho para o sábado, ";
        $msg .= "por isso é tão importante mantermos o prazo limite de quinta-feira 12h.";
    }

	$sql = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
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

    //Lista de variáveis
    $sqlProdutos = "SELECT * FROM produtosVar WHERE idCalendario = ".$idProximoSabado." ORDER BY nome ASC";
    $st = $conn->prepare($sqlProdutos);
    $st->execute();
    $rsProdutos = $st->fetchAll();
    foreach ($rsProdutos as $rowProd) {
        $prodVariaveis[$rowProd["id"]]=$rowProd["nome"];
    }
	
	$sql = "SELECT * FROM PedidosVar WHERE idConsumidor = ".$idConsumidor." AND idCalendario = ".$idProximoSabado;
	$st = $conn->prepare($sql);
	$st->execute();
	$rsPedidoVar=$st->rowCount();
	if ($rsPedidoVar > 0) {
        $msg = "<p>&nbsp;Você já realizou seu pedido de variável para essa semana. Caso deseje alterar, entre em contato diretamente com o Livres.";
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
	}

    //Se for POST, salvar e mostrar mensagem padrão apenas
    if (isset($_POST["cpf"])) {
        $cpf=$_POST["cpf"];
        $opcao1=$_POST["opcao1"];
        $opcao2=$_POST["opcao2"];
        if ($opcao1 == "") {
            $opcao1 = "NULL";
        }
        if ($opcao2 == "") {
            $opcao2 = "NULL";
        }
        if (strlen($cpf) == 0 || strlen($opcao1) == 0) {
            $msg = "&nbsp;Você precisa selecionar ao menos a 1ª opção de variável.";
        } else {
            $sql = "INSERT INTO PedidosVar (idConsumidor,idCalendario,idOpcao1,idOpcao2) VALUES (".$idConsumidor.",".$idProximoSabado.",".$opcao1.",".$opcao2.")";
            $st = $conn->prepare($sql);
	        $st->execute();
	        $msg = "&nbsp;Seu pedido de variáveis foi realizado.";
        }
    }

	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Frequencia = 'Semanal' AND Pedidos.Quantidade > 0 AND produtos.previsao <= '".$proximoSabado."' ORDER BY produtos.nome";
	$st = $conn->prepare($sql);
	$st->execute();
	$rsSemanal=$st->fetchAll();
	$contaSemanal=0;
	foreach ($rsSemanal as $row) {					
		$contaSemanal++;
	}
	$sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE Pedidos.IDConsumidor = ".$idConsumidor." AND Pedidos.Quantidade > 0 AND (Pedidos.Frequencia = 'Semanal' OR Pedidos.Frequencia = 'Quinzenal') AND produtos.previsao <= '".$proximoSabado."' ORDER BY produtos.nome";
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
    					<h5>Variáveis disponíveis para entrega em <?php echo date("d/m/Y",strtotime($proximoSabado)); ?></h5>
    					<div class="grid-x grid-padding-x">
    					    <table>
    					        <tr>
    					            <td>Alimento Variável</td>
    					            <td>Preço Comunitário (Comboio Orgânico)</td>
    					            <td>Preço Mercado Comum/Impessoal</td>
    					        </tr>
    						<?php
    						//Loop de produtos variáveis
    						$sql = "SELECT * FROM produtosVar WHERE idCalendario = ".$idProximoSabado." ORDER BY nome";
    						$st = $conn->prepare($sql);
    						$st->execute();
    						$rsProdutos=$st->fetchAll();
    						//Loop de produtos variáveis
    						foreach ($rsProdutos as $row) {					
    						    echo '<tr><td>'.ccase($row["nome"]).'</td><td>R$'.number_format($row["preco"],2,",",".").'</td><td>R$'.number_format($row["preco_impessoal"],2,",",".")."</td></tr>";
    						}
    					    ?>
    					    </table>
    					</div>
    				</div>
    			</div>
    
    			<div class="grid-x grid-padding-x">
    				<div class="large-12 medium-12 cell text-center callout" id="semanal">
    					<h5>Escolha suas opções de variáveis para entrega de <?php echo date("d/m/Y",strtotime($proximoSabado)); ?></h5>
    					<h5>Você possui R$<?php echo number_format($cota-$valorCesta,2,",",".");?> de variável para esta semana.</h5>
    					<?php echo '<a href="../Cestas/?cpf='.$cpf.'" target="_blank">Clique aqui caso deseje consultar sua cesta</a>'; ?>
    					<div class="grid-x grid-padding-x">
    					    <form method="POST" action="">
    					        <input type="hidden" name="cpf" id="cpf" value="<?php echo $_GET["cpf"]; ?>">
    					        <label for="opcao1">1ª Opção</label>
    					        <select id="opcao1" name="opcao1">
    					            <option value=""></option>
            						<?php
            						//Loop de produtos variáveis
            						foreach ($rsProdutos as $row) {					
            							echo '<option value="'.$row["id"].'">'.ccase($row["nome"]).' - R$'.number_format($row["preco"],2,",",".").'</option>';
            						}
            						?>
        						</select>
        						<label for="opcao2">2ª Opção</label>
        						<select id="opcao2" name="opcao2">
        						    <option value=""></option>
        						    <?php
        						    //Loop de produtos variáveis
        						    foreach ($rsProdutos as $row) {					
            							echo '<option value="'.$row["id"].'">'.ccase($row["nome"]).' - R$'.number_format($row["preco"],2,",",".").'</option>';
            						}
            						?>
        						</select>
        						<input type="submit" value="Salvar" id="salvar" name="salvar" />
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
