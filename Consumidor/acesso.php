<?php
session_start();
if (!isset($_SESSION["usuario_logado"])) {
    header("Location: login.php");
} else {
    if ($_SESSION["usuario_logado"] != "sim") {
       header("Location: login.php"); 
    }
}
?>