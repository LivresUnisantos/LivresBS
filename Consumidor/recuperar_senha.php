<?php
include "../config.php";
include "../Painel/password.php";
include "../Painel/helpers.php";
include "../mail.php";
session_start();
//redirecionar usuário caso ele já esteja logado
if (isset($_SESSION["usuario_logado"])) {
    if ($_SESSION["usuario_logado"] == "sim") {
        header("Location: index.php");
    }
}
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
if (isset($_SESSION["alerta"])) {
    $alerta = $_SESSION["alerta"];
    $_SESSION["alerta"] = "";
} else {
    $alerta="";
}
if (isset($_POST["cpf"])) {
    $cpf=trim($_POST["cpf"]);
    $cpf=str_replace(".","",$cpf);
    $cpf=str_replace(",","",$cpf);
    $cpf=str_replace("-","",$cpf);
    $cpf=str_replace(" ","",$cpf);
    $sql = "SELECT * FROM Usuarios WHERE cpf = '".$cpf."'";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 1) {
        $rs = $st->fetch();
        //Gerar código de recuperação de senha do usuário
        $codigo = gerarCodigoSenha();
        $sqlCodigo = "INSERT INTO UsuariosCodigoSenha (idUsuario,codigo) VALUES (".$rs["id"].",'".$codigo."')";
        $st = $conn->prepare($sqlCodigo);
        if (!$st->execute()) {
            $alerta = "Falha ao recuperar senha. Tente novamente";
        } else {
            //Enviar e-mail com link para recuperação de senha
            $assunto = "Livres BS - Recuperação de senha";
            $mensagemHTML = "<p>Olá ".$rs["nome"]."</p>";
            $mensagemHTML .= "Você está recebendo este e-mail porque foi solicitado na página do Livres BS a recuperação de sua senha.<br>";
            $mensagemHTML .= "Caso não tenha feito esta solicitação, basta ignorar este e-mail<br><br>";
            $mensagemHTML .= "<p>Para gerar uma nova senha basta clicar no link abaixo ou copiar o endereço e colar em seu navegador.<br>";
            $mensagemHTML .= "O link tem validade de 24 horas.</p>";
            $mensagemHTML .= '<a href="https://www.livresbs.com.br/Consumidor/nova_senha.php?codigo='.$codigo.'">https://www.livresbs.com.br/Consumidor/nova_senha.php?codigo='.$codigo.'</a>';
            
            $mensagemTexto = "Olá ".$rs["nome"];
            $mensagemTexto .= "Você está recebendo este e-mail porque foi solicitado na página do Livres BS a recuperação de sua senha.";
            $mensagemTexto .= "Caso não tenha feito esta solicitação, basta ignorar este e-mail";
            $mensagemTexto .= "Para gerar uma nova senha basta copiar o endereço e colar em seu navegador.";
            $mensagemTexto .= "O link tem validade de 24 horas.";
            $mensagemTexto .= 'https://www.livresbs.com.br/Consumidor/nova_senha.php?codigo='.$codigo;
            
            if (sendMail(array("nome" => $rs["nome"],"email" => $rs["email"]),$assunto,$mensagemHTML,$mensagemTexto,"utf-8") == true) {
                $alerta= "Email de recuperação enviado para ".hashEmail($rs["email"]);
            } else {
                $alerta = "Falha ao recuperar senha. Erro 0x01. Tente novamente";
            }
        }
    } else {
        $alerta = "CPF não encontrado";
    }
}
?>
<body translate="no">
	<div class="container">
		<div class="form">
		    <?php
		    if (strlen($alerta) > 0) {
		        echo '<span style="color:#FF0000;">'.$alerta.'</span>';
		    }
			?>
			<form class="senha-form" method="POST" name="senha" id="senha" action="">
			    <input type="hidden" id="tipoS" name="tipo" value="senha" />
				<p><input type="text" placeholder="CPF" name="cpf" id="cpfL" data-validation="cpf" data-validation-error-msg="CPF inválido"/></p>
				<button>Recuperar Senha</button>
				<p class="message">
					Já cadastrado? <a href="index.php">Entrar</a>
				</p>
			</form>
		</div>
	</div>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js'></script>
	<script id="rendered-js">
		$(document).ready(function() {
			$.validate({
				lang: 'pt',
				modules : 'brazil, security',
				form : '#registro, #login, #senha',
				errorMessagePosition: 'inline'
			});
		});
	</script>
</body>
</html>