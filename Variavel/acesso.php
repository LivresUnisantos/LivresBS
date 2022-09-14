<?php
session_start();
$logado=false;
if (isset($_SESSION["cpf"])) {
    if ($_SESSION["cpf"] != "") {
        $logado=true;
    }
}

if (!$logado) {
    $origem = urlencode($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
    header('Location:logar.php?origem='.$origem);
}
?>