<?php
include "acesso.php";
include "../config.php";
include "../Painel/password.php";
include "../Painel/helpers.php";
include "../mail.php";
?>
<!DOCTYPE html>
<!--https://codepen.io/colorlib/full/rxddKy-->
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Livres BS - Consumo Consciente</title>
	<link rel="stylesheet" href="../css/foundation.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/theme-default.min.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="estilo.css">
	<script>
		window.console = window.console || function(t) {};
	</script>
	<script>
		if (document.location.search.match(/type=embed/gi)) {
			window.parent.postMessage("resize", "*");
		}
	</script>
</head>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$alerta="";
$pedidoInativo=false;

$sql = "SELECT * FROM Usuarios WHERE id = ".$_SESSION["usuario_id"];
$st = $conn->prepare($sql);
$st->execute();
$rs = $st->fetch();
$grupo = $rs["grupo"];
$grupo = strtoupper($grupo);
$grupo = str_replace("-","",$grupo);

$sqlAberto = "SELECT * FROM Parametros WHERE parametro = 'PedidosAvulsos".$grupo."' AND valor='1'";
$st = $conn->prepare($sqlAberto);
$st->execute();
if ($st->rowCount() == 0) {
    $alerta = "Pedidos para ".ucwords(strtolower($rs["grupo"]))." não estão abertos no momento.";
    setLog("log.txt","Usuário tentou fazer pedido, mas pedidos ainda não estão abertos.",$sqlAberto);
    $pedidoInativo = true;
}

if (strlen($alerta) == 0) {
    $sqlExistente = "SELECT * FROM PedidosAvulsos WHERE idUsuario = ".$rs["id"]." AND pedido_inativo = 0";
    $st = $conn->prepare($sqlExistente);
    $st->execute();
    if ($st->rowCount() > 0) {
        $rsExistente = $st->fetchAll();
        //$pedidoInativo=true;
        $alerta = "Você já possui o pedido feito. Faça pedido apenas de novos produtos não feitos anteriormente. Consulte abaixo seu pedido já realizado:";
        foreach ($rsExistente as $row) {
            $alerta .= "<p>".$row["pedido"]."</p>";
            setLog("log.txt","Usuário com pedido já feito logou para novo pedido",$sqlExistente);
        }//
    } else {
        $pedidoInativo=false;
    }
    
    
    if (isset($_POST["pedido"])) {
        if (!$pedidoInativo) {
            $endereco=ucwords(strtolower(trim($_POST["endereco"])));//
            $telefone=trim($_POST["telefone"]);
            $telefone=str_replace(".","",$telefone);
            $telefone=str_replace(",","",$telefone);
            $telefone=str_replace("-","",$telefone);
            $telefone=str_replace(" ","",$telefone);
            $pedido = nl2br(trim($_POST["pedido"]));
            $pedido=str_replace("'","''",$pedido);
            $grupo=$rs["grupo"];
            $nome=$rs["nome"];
            $cpf=$rs["cpf"];
            $email=$rs["email"];
            $id=$rs["id"];
            
            $grupo = strtoupper($grupo);
            $grupo = str_replace("-","",$grupo);
            
            $entrega = $_POST["entrega"];
            
            $sqlPedido = "INSERT INTO PedidosAvulsos (idUsuario,nome,email,cpf,grupo,endereco,entrega,telefone,pedido) VALUES (".$id.",'".$nome."','".$email."','".$cpf."','".$grupo."','".$endereco."','".$entrega."','".$telefone."','".$pedido."')";
            $st = $conn->prepare($sqlPedido);
            if($st->execute()) {
                $alerta = "Pedido realizado";
                $alerta .= "<p>".$pedido."</p>";
                //Enviar e-mail de confirmação
                $assunto = "Livres BS - Confirmação Pedido";
                $mensagemHTML = "<p>Olá ".$nome."</p>";
                $mensagemHTML .= "<p>Este é um e-mail de confirmação do seu pedido realizado na página do Livres BS.<br>";
                $mensagemHTML .= "Confirme abaixo o seu pedido e em caso de dúvidas, procure um de nossos coordenadores.</p>";
                $mensagemHTML .= $pedido;
                $mensagemHTML .= "<p>Atenciosamente,<br>Equipe Livres</p>";
                
                $mensagemTexto = "Olá ".$nome."\r\n";
                $mensagemTexto .= "\r\nEste é um e-mail de confirmação do seu pedido realizado na página do Livres BS.\r\n";
                $mensagemTexto .= "Confirme abaixo o seu pedido e em caso de dúvidas, procure um de nossos coordenadores.\r\n";
                $mensagemTexto .= str_replace("<br>","/n/r",$pedido);

                $mensagemTexto .= "\r\n\r\nAtenciosamente,\r\nEquipe Livres";
                sendMail(array("nome" => $nome,"email" => $email),$assunto,$mensagemHTML,$mensagemTexto,"utf-8");
                setLog("log.txt","Novo pedido realizado",$sqlPedido);
            } else {
                $alerta = "Falha ao cadastrar seu pedido. Tente novamente.";
                setLog("log.txt","Falha em tentativa de realizar novo pedido",$sqlPedido);
            }
        }
    }
}
?>
<body translate="no">
	<div class="container2">
	    <a href="deslogar.php">Sair</a>
	    <?php
	    if (strlen($alerta)) {
	        echo '<div class="alerta">';
	        echo $alerta;
	        echo '</div>';
	    }
	    if (!$pedidoInativo) {
	    ?>
    		<div class="form">
    		    <h5>Confirme seus dados e faça seu pedido</h5>
    		    <?php
    		    //Obter o link
    		    $sqlLink = "SELECT * FROM listas_produtos WHERE nome_lista = '".$rs["grupo"]."'";
                $st = $conn->prepare($sqlLink);
                $st->execute();
                if ($st->rowCount() == 0) {
                    $link = "";
                } else {
                    $rsLink = $st->fetch();
                    $link = "https://livresbs.com.br/produtos/".$rsLink["id"];
                }
    		    /*if (strtolower($rs["grupo"]) == "aproate") {
    		        echo '<a href="lista_produtos_APROATE_202004141301.pdf" target="_blank">Consulte a lista de produtos disponíveis clicando aqui</a>';
    		    } else {
    		        if (strtolower($rs["grupo"]) == "pre-comunidade") {
    		            echo '<a href="lista_produtos_PRECOMUNIDADE_202004141301.pdf" target="_blank">Consulte a lista de produtos disponíveis clicando aqui</a>';
    		        } else {
    		            echo 'Não há lista de produtos disponível para '.strtoupper($rs["grupo"]).'. Consulte um coordenador do Livres.';
    		        }
    		    }*/
    		    if (strlen($link) > 0) {
    		        echo '<a href="'.$link.'" target="_blank">Consulte a lista de produtos disponíveis clicando aqui</a>';
    		    } else {
                    echo 'Não há lista de produtos disponível para '.strtoupper($rs["grupo"]).'. Consulte um coordenador do Livres.';
    		    }
    		    ?>
    			<form class="" method="POST" name="pedido" id="pedido" action="">
    				<p><label for="nome">Nome</label><input type="text" disabled="disabled" id="nome" name="nome" placeholder="Nome Completo" data-validation="length" data-validation-length="2-100" value="<?php echo $rs["nome"];?>" /></p>
    				<p><label for="email">Email</label><input type="text" disabled="disabled" id="email" name="email" placeholder="Email" data-validation="email" value="<?php echo $rs["email"];?>" /></p>
    				<p><label for="cpf">CPF</label><input type="text" disabled="disabled" id="cpf" name="cpf" placeholder="CPF" data-validation="cpf" value="<?php echo $rs["cpf"];?>" /></p>
    				<p><label for="grupo">Grupo</label><input type="text" disabled="disabled" id="grupo" name="grupo" placeholder="grupo" value="<?php echo $rs["grupo"]; ?>" /></p>
    				<p><label for="endereco">Endereço</label><input type="text" id="endereco" name="endereco" placeholder="Endereço" data-validation="length" data-validation-length="min10" value="<?php echo $rs["endereco"];?>" /></p>
    				<p>
    				    <label for="telefone">Entrega ou retirada?</label>
    				    <select id="entrega" name="entrega" data-validation="length" data-validation-length="min1" data-validation-error-msg="Selecione a opção de entrega ou retirada">
    				        <option value=""></option>
    				        <option value="entrega">Quero Entrega</option>
    				        <option value="retirada">Irei retirar</option>
    				        <option value="indefinido">Não sei ainda</option>
    				    </select>
    				</p>
    				<p><label for="telefone">Telefone</label><input type="text" id="telefone" name="telefone" placeholder="Telefone/Whatsapp" data-validation="brphone" data-validation-error-msg="Digite somente números e DDD" value="<?php echo $rs["telefone"];?>" /></p>
    				<p><label for="pedido">Pedido</label><textarea rows="10" cols="20" id="pedido" name="pedido" placeholder="Pedido" data-validation="length" data-validation-length="min1"></textarea></p>
    				<button>Fazer Pedido</button>
    				<p class="message1">Dúvidas no preenchimento? <a href="/Consumidor/instrucoes" target="_blank">Veja instruções aqui</a></p>
    			</form>
    		</div>
    	<?php
	    }
	    ?>
	</div>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js'></script>
	<script id="rendered-js">
		$(document).ready(function() {
			$.validate({
				lang: 'pt',
				modules : 'brazil, security',
				form : '#pedido',
				errorMessagePosition: 'inline'
			});
		});
	</script>
</body>
</html>
