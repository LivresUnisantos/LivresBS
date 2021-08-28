<?php
session_start();
if (strlen($_SERVER['QUERY_STRING']) > 0) {
    if ($_SERVER['QUERY_STRING'] == "pre") {
        $_SESSION["grupo"] = "pre-comunidade";
    }
    if ($_SERVER['QUERY_STRING'] == "aproate") {
        $_SESSION["grupo"] = "aproate";
    }
} else {
    $_SESSION["grupo"] = "";
}
//echo $_SESSION["grupo"];
if (!isset($_SESSION["usuario_logado"])) {
    header("Location: login.php");
} else {
    if ($_SESSION["usuario_logado"] != "sim") {
       header("Location: login.php"); 
    }
}
?>