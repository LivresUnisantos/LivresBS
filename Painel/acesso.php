<?php
session_start();
$logado=false;
if (isset($_SESSION["logado"])) {
    if ($_SESSION["logado"]=="sim") {
        $logado=true;
    }
}
if (!$logado) {
    /*echo '<pre>';
    print_r($_SERVER);
    echo '</pre>';*/
    echo '<form action="'.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/Painel/logar.php?origem='.urlencode($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']).'" method="POST">';
    echo '<label for="login">Email</label>';
    echo '<input type="text" name="login" id="login" />';
    echo '<label for="senha">Senha</label>';
    echo '<input type="password" name="senha" id="senha" />';
    echo '<input type="submit" value="Enviar"/>';
    echo '</form>';
    exit();
} else {
    if ($_SESSION["level"] == 1000) {
        //header("Location: ./montagem");
    }
    if (isset($levelRequired)) {        
        if ($_SESSION["level"] < $levelRequired) {
            echo 'Você não tem permissão de acesso à esta página';
            exit();
        }
    }
}
?>