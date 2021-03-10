<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config.php";
include "password.php";
if (isset($_GET["origem"])) {
	$origem = urldecode($_GET["origem"]);
} else {
	$origem = $_SERVER["HTTP_HOST"]."/Painel";
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
            session_start();
            $_SESSION["logado"] = "sim";
            $_SESSION["level"] = $rs[0]["level"];
            $_SESSION["login"] = $rs[0]["login"];
            $_SESSION["id"] = $rs[0]["id"];
            header("Location:".$origem);
        } else {
            echo "Email/Senha incorreto(s).";
        }
    } else {
        echo "Email/Senha incorreto(s)!";
    }
} else {
    echo "Preencha email e senha.";
}
?>