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
if (isset($_GET["codigo"])) {
    $codigo = $_GET["codigo"];
    $sql = "SELECT * FROM UsuariosCodigoSenha WHERE codigo = '".$codigo."'";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 1) {
        $rs = $st->fetch();
        $idCodigo = $rs["id"];
        $idUsuario = $rs["idUsuario"];
        $dataCriacao = strtotime($rs["criado_em"]);

        $limite = strtotime('+1 days', $dataCriacao);
        $agora = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));

        if (strtotime($agora) > strtotime($limite)) {
            $alerta = "Código inválido, inexistente ou expirado. Solicite recuperação de senha novamente.";
        } else {
            if (isset($_POST["senha1"])) {
                $senha1 = $_POST["senha1"];
                $senha2 = $_POST["senha2"];
                $senha = password_hash ($senha1,PASSWORD_DEFAULT);
                if ($senha1 != $senha2) {
                    $alerta = "As senhas precisam coincidir";
                } else {
                    $sqlDelete = "DELETE FROM UsuariosCodigoSenha WHERE id = ".$idCodigo;
                    $st = $conn->prepare($sqlDelete);
                    if ($st->execute()) {
                        $sqlUpdate = "UPDATE Usuarios SET senha = '".$senha."' WHERE id = ".$idUsuario;
                        $st = $conn->prepare($sqlUpdate);
                        if ($st->execute()) {
                            $alerta = 'Senha alterada!<br><a href="index.php">Entrar</a>';
                        } else {
                            $alerta = 'Falha ao atualizar sua senha. Recupere sua senha novamente <br><a href="recuperar_senha.php">aqui</a>';
                        }
                    } else {
                        $alerta = "Falha ao alterar senha. Tente novamente.";
                    }
                }
            }
        }
    } else {
        $alerta = "Código inválido, inexistente ou expirado. Solicite recuperação de senha novamente.";
    }
}
?>
<body translate="no">
	<div class="container">
		<div class="form">
		    <?php
		    if (strlen($alerta) > 0) {
		        echo '<span style="color:#FF0000;">'.$alerta.'</span>';
		    } else {
			?>
			<p>Defina sua nova senha</p>
			<form class="senha-form" method="POST" name="senha" id="senha" action="">
			    <input type="hidden" id="tipoS" name="tipo" value="senha" />
				<p><input type="password" id="senha1" name="senha1" placeholder="Senha" data-validation="length" data-validation-length="min6" data-validation-error-msg="Mínimo 6 caracteres" /></p>
				<p><input type="password" id="senha2" name="senha2" placeholder="Confirmar Senha" data-validation="confirmation" data-validation-confirm="senha1" data-validation-error-msg="As duas senhas precisam ser iguais" /></p>
				<button>Recuperar Senha</button>
				<p class="message">
					Já cadastrado? <a href="#">Entrar</a>
				</p>
			</form>
			<?php
		    }
		    ?>
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