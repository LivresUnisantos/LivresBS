<?php
session_start();
$_SESSION["logado"]="";
$_SESSION["level"]="";
$_SESSION["login"]="";
$_SESSION["data_consulta"]="";
session_destroy();
header("Location:index.php");
?>