<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);

if (isset($_POST["act"])) {
    //Habilitar/desabilitar página de pedidos
    if ($_POST["act"] == "PaginaPedidos") {
        $sql = "UPDATE Parametros SET valor = '".$_POST["valor"]."' WHERE parametro = 'PedidosAvulsos".$_POST["grupo"]."'";
        $st = $conn->prepare($sql);
        if ($st->execute()) {
            echo "Alterado";
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
    }
    //Apagar pedidos feitos
    if ($_POST["act"] == "RemoverPedidos") {
        $sql = "UPDATE PedidosAvulsos SET pedido_inativo = 1 WHERE pedido_inativo = 0";
        $st = $conn->prepare($sql);
        if ($st->execute()) {
            echo "Alterado";
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
    }
}
?>