<?php
session_start();
$logado=false;
if (isset($_SESSION["logado"])) {
    if ($_SESSION["logado"]=="sim") {
        $logado=true;
    }
}
?>
<?php
    if (!$logado) {
        $origem = urlencode($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
        header("Location:".$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/Painel/logar.php?origem='.$origem);
/*
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
                <form class="login-form" id="login" name="login" method="POST" action="<?php echo $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/Painel/logar.php?origem='.urlencode($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']); ?>">
    			    <input type="hidden" id="tipoL" name="tipo" value="login" />
    				<p><label for="login">Email</label><input type="text" placeholder="Email" name="login" id="login" /></p>
    				<p><label for="senha">Senha</label><input type="password" placeholder="Senha" name="senha" id="senha" /></p>
    				<button>Entrar</button>
    			</form>
    		</div>
    	</div>
    </body>
</html>
*/
exit();
} else {
    if (isset($levelRequired)) {        
        if ($_SESSION["level"] < $levelRequired) {
            echo 'Você não tem permissão de acesso à esta página';
            exit();
        }
    }
}
?>