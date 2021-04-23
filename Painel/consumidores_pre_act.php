<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);

if (isset($_GET["act"])) {
    
    if ($_GET["act"] == "senha") {
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $codigo = gerarCodigoSenha();
            $sqlCodigo = "INSERT INTO UsuariosCodigoSenha (idUsuario,codigo) VALUES (".$id.",'".$codigo."')";
            $st = $conn->prepare($sqlCodigo);
            if (!$st->execute()) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            } else {
                header("Location: https://www.livresbs.com.br/Consumidor/nova_senha.php?codigo=".$codigo);
            }
        }
    }
}
?>