<?php
session_start();

$_SESSION["cpf"] = "";
$_SESSION["id_consumidor"] = "";
$_SESSION["grupo"] = "";

unset($_SESSION["cpf"]);
unset($_SESSION["id_consumidor"]);
unset($_SESSION["grupo"]);

header("Location: index.php");
?>