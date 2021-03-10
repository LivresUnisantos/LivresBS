<?php
include "../config.php";
include "../Painel/password.php";
include "../Painel/helpers.php";
session_start();
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
if (isset($_POST["tipo"])) {
    if (isset($_GET["origem"])) {
    	$origem = urldecode($_GET["origem"]);
    } else {
    	$origem = "index.php";
    }
    $cpf=trim($_POST["cpf"]);
    $cpf=str_replace(".","",$cpf);
    $cpf=str_replace(",","",$cpf);
    $cpf=str_replace("-","",$cpf);
    $cpf=str_replace(" ","",$cpf);
    $senha= $_POST["senha"];
    if ($_POST["tipo"] == "login") {
        if (strlen($cpf) > 0 && strlen($senha) > 0) {
            $sql = "SELECT * FROM Usuarios WHERE cpf = '".$cpf."'";
            $st = $conn->prepare($sql);
            $st->execute();
            if ($st->rowCount() == 1) {
                $rs = $st->fetchAll();
                $senhaDB = $rs[0]["senha"];
                if (password_verify($senha,$senhaDB)) {
                    session_start();
                    $_SESSION["usuario_logado"]="sim";
                    $_SESSION["usuario_cpf"]=$rs[0]["cpf"];
                    $_SESSION["usuario_id"]=$rs[0]["id"];
                    setLog("log.txt","Usuário logado. CPF: ".$cpf." / Email: ".$rs[0]["email"],"");
        			echo '<script>window.location = "'.$origem.'";</script>';
                } else {
                    $alerta = "CPF/Senha incorreto(s).";
                    setLog("log.txt",$cpf." tentou logar com cpf/senha incorreto","");
                }
            } else {
                $alerta = "Seu cadastro não existe, clique no botão 'cadastre-se' abaixo'! Verifique que digitou seu cpf corretamente no login.";
                setLog("log.txt",$cpf." tentou logar com cpf inexistente","");
            }
        } else {
            $alerta = "Preencha CPF e senha.";
            setLog("log.txt","usuario tentou logar com campo em branco","");
        }
    }
    if ($_POST["tipo"] == "cadastro") {
        $senha1 = $_POST["senha1"];
        $senha2 = $_POST["senha2"];
        if (strlen($senha1) == 0 || $senha1 != $senha2) {
            $alerta = "As duas senhas digitadas precisam ser iguais";
        } else {
            $nome = ucwords(strtolower(trim($_POST["nome"])));
            $email=trim(strtolower($_POST["email"]));
            $cpf=trim($_POST["cpf"]);
            $cpf=str_replace(".","",$cpf);
            $cpf=str_replace(",","",$cpf);
            $cpf=str_replace("-","",$cpf);
            $cpf=str_replace(" ","",$cpf);
            $grupo=$_POST["grupo"];
            $endereco=ucwords(strtolower(trim($_POST["endereco"])));//
            $telefone=trim($_POST["telefone"]);
            $telefone=str_replace(".","",$telefone);
            $telefone=str_replace(",","",$telefone);
            $telefone=str_replace("-","",$telefone);
            $telefone=str_replace(" ","",$telefone);
            $senha = password_hash ($senha1,PASSWORD_DEFAULT);
            
            $sqlCheck = "SELECT * FROM Usuarios WHERE email = '".$email."' OR cpf = '".$cpf."'";
            $st = $conn->prepare($sqlCheck);
            $st->execute();
            
            if ($st->rowCount() > 0) {
                $alerta = "Email ou CPF já cadastrado";
                setLog("log.txt","Usuario tentou cadastrar com cpf/email já cadastrado. CPF: ".$cpf." / Email: ".$email,"");
            } else {
                $sql = "INSERT INTO Usuarios (nome, email, cpf, grupo, endereco, telefone,senha) VALUES ('".$nome."','".$email."','".$cpf."','".$grupo."','".$endereco."','".$telefone."','".$senha."')";
                $st=$conn->prepare($sql);
                $st->execute();
                $idConsumidor=$conn->lastInsertId();
                
                if ($idConsumidor == 0) {
                	$alerta = "Erro ao cadastrar seus dados. Por favor, tente novamente.";
                	setLog("log.txt","Falha ao cadastrar usuário. CPF: ".$cpf." / Email: ".$email,"");
                } else {
                    $_SESSION["alerta"] = "Usuário cadastrado. Faça login.";
                    setLog("log.txt","Usuário cadastrado. CPF: ".$cpf." / Email: ".$email,"");
                    echo '<script>window.location = "login.php";</script>';
                }
            }
        }
    }
    if ($_POST["tipo"] == "senha") {
        
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
			<form class="register-form" method="POST" name="registro" id="registro" action="">
			    <input type="hidden" id="tipo" name="tipo" value="cadastro" />
				<p><input type="text" id="nome" name="nome" placeholder="Nome Completo" data-validation="length" data-validation-length="2-100" /></p>
				<p><input type="text" id="email" name="email" placeholder="Email" data-validation="email" /></p>
				<p><input type="text" id="cpf" name="cpf" placeholder="CPF" data-validation="cpf" /></p>
				<?php
				$grupos = array("APROATE","AOVALE","pré-comunidade","não sei");
				if (isset($_GET["grupo"])) {
				    echo '<p><select id="grupo" name="grupo" data-validation="length" data-validation-length="min1">
				        <option value=""></option>';
				        foreach ($grupos as $grupo) {
				            if (strtolower($_GET["grupo"]) == strtolower($grupo)) {
				                echo '<option value="'.$grupo.'" selected="selected">'.$grupo.'</option>';
				            } else {
    				            echo '<option value="'.$grupo.'">'.$grupo.'</option>';
				            }
				        }
    				echo '</select></p>';
				} else {
    				echo '<p><select id="grupo" name="grupo" data-validation="length" data-validation-length="min1">
    				    <option value=""></option>
    				    <option value="APROATE">APROATE</option>
    				    <option value="AOVALE">AO VALE</option>
    				    <option value="pré-comunidade">Pré-comunidade</option>
    				    <option value="Não sei">Não sei</option>
    				</select></p>';
				}
				?>
				<p><input type="text" id="endereco" name="endereco" placeholder="Endereço" data-validation="length" data-validation-length="min10" /></p>
				<p><input type="text" id="telefone" name="telefone" placeholder="Telefone/Whatsapp" data-validation="brphone" data-validation-error-msg="Digite somente números e DDD" /></p>
				<p><input type="password" id="senha1" name="senha1" placeholder="Senha" data-validation="length" data-validation-length="min6" data-validation-error-msg="Mínimo 6 caracteres" /></p>
				<p><input type="password" id="senha2" name="senha2" placeholder="Confirmar Senha" data-validation="confirmation" data-validation-confirm="senha1" data-validation-error-msg="As duas senhas precisam ser iguais" /></p>
				<button>Cadastrar</button>
				<p class="message">
					Já cadastrado? <a href="#">Entrar</a>
				</p>
			</form>
			<form class="login-form" id="login" name="login" method="POST" action="">
			    <input type="hidden" id="tipoL" name="tipo" value="login" />
				<p><label for="cpf">CPF</label><input type="text" placeholder="CPF" name="cpf" id="cpfL" data-validation="cpf" data-validation-error-msg="CPF inválido"/></p>
				<p><label for="senha">Senha</label><input type="password" placeholder="Senha" name="senha" id="senhaL" data-validation="length" data-validation-length="min1" data-validation-error-msg="Preencha sua senha" /></p>
				<button>Entrar</button>
				<p class="message">Não cadastrado? <a href="#">Cadastre-se</a></p>
			</form>
			<p class="message1">Esqueceu sua senha? <a href="recuperar_senha.php">Recupere aqui</a></p>
			<!--
			<p class="message">Esqueceu sua senha?</p>
			<form class="senha-form" id="senha" name="senha" method="POST" action="">
			    <input type="hidden" id="tipoS" name="tipo" value="senha" />
			    <p><input type="text" placeholder="CPF" name="cpf" id="cpfL" data-validation="cpf" data-validation-error-msg="CPF inválido"/></p>
			    <button>Recuperar</button>
			</form>
			-->
		</div>
	</div>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js'></script>
	<script id="rendered-js">
		$('.message a').click(function () {
			$('form').animate({ height: "toggle", opacity: "toggle" }, "slow");
		});
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