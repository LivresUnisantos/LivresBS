<?php
session_start();
$_SESSION["usuario_logado"]="";
$_SESSION["usuario_cpf"]="";
$_SESSION["usuario_id"]="";
session_destroy();
header("Location:index.php");
?>