<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config.php";
include "password.php";

session_start();

if (isset($_GET["origem"])) {
	$origem = urldecode($_GET["origem"]);
} else {
	$origem = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"]."/Painel";
}

if (isset($_SESSION["logado"])) {
    if ($_SESSION["logado"] == "sim") {
        header("Location: ".$origem);
    }   
}

//$origem = $_SERVER["HTTP_REFERER"];
if (isset($_POST["login"]) && isset($_POST["senha"])) {
    $login= $_POST["login"];
    $senha= $_POST["senha"];
    $conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
    $sql = "SELECT * FROM Admins WHERE login = '".$login."'";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 1) {
        $rs = $st->fetchAll();
        $senhaDB = $rs[0]["password"];
        if (password_verify($senha,$senhaDB)) {
            $_SESSION["logado"] = "sim";
            $_SESSION["level"] = $rs[0]["level"];
            $_SESSION["login"] = $rs[0]["login"];
            $_SESSION["id"] = $rs[0]["id"];
            header("Location:".$origem);
        } else {
            $msg = "Email/Senha incorreto(s).";
        }
    } else {
        $msg = "Email/Senha incorreto(s)!";
    }
} else {
    $msg = "Preencha email e senha.";
}
if (strlen($msg) > 0) {
    if (isset($_GET["origem"])) {
        $origem = $_GET["origem"];
    } else {
        $origem = urlencode($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
    }
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
    	<meta charset="UTF-8">
    	<title>Livres BS - Consumo Consciente</title>
    	<link rel="stylesheet" href="../css/foundation.css">
    	<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/theme-default.min.css" rel="stylesheet" type="text/css" />
    	<link rel="stylesheet" href="login.css">
    	<script>
    		window.console = window.console || function(t) {};
    	</script>
    	<script>
    		if (document.location.search.match(/type=embed/gi)) {
    			window.parent.postMessage("resize", "*");
    		}
    	</script>
    </head>
    <body translate="no">
    	<div class="container">
    		<div class="form">
                <form class="login-form" id="login" name="login" method="POST" action="<?php echo $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/Painel/logar.php?origem='.$origem; ?>">
                    
    			    <input type="hidden" id="tipoL" name="tipo" value="login" />
    				<p><label for="login">Email</label><input type="text" placeholder="Email" name="login" id="login" /></p>
    				<p><label for="senha">Senha</label><input type="password" placeholder="Senha" name="senha" id="senha" /></p>
    				<button>Entrar</button>
    			</form>
    		</div>
    	</div>
    </body>
</html>
<?php
}
?>