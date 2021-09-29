<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";
require_once "helpers.php";

if (isset($_GET["idProduto"]) && isset($_GET["ativo"]) && isset($_GET["idLista"])) {
    $livres = new Livres();
    $conn = $livres->conn();

    $idProdutos = explode("@",$_GET["idProduto"]);
    $ativos = explode("@", $_GET["ativo"]);
    $idLista = $_GET["idLista"];
    
    if (count($idProdutos) != count($ativos)) {
        echo "Erro ao salvar dados. Arrays com dimensões erradas";
        echo count($idProdutos);
        echo "<br><br>";
        echo count($ativos);
    } else {
        $erros = "";
        for($i = 0; $i < count($idProdutos); $i++) {
            $idProduto = $idProdutos[$i];
            $ativo = $ativos[$i];
            $sql = "SELECT * FROM listas_itens WHERE id_produto = ".$idProduto." AND id_lista = ".$idLista;
            $st = $conn->prepare($sql);
            $st->execute();
            if ($st->rowCount() == 0) {
                $sql = "INSERT INTO listas_itens (id_produto, id_lista, ativo) VALUES (".$idProduto.",".$idLista.",".$ativo.")";
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    setlog("log_listas.txt",$_SESSION["login"]." cadastrou e ativo produto (id=".$idProduto.")",$sql);
                } else {
                    $erros .= "Falha ao acrescentar produto (".$idProduto.")";
                    setlog("log_listas.txt",$_SESSION["login"].": Erro ao cadastrar produto(id=".$idProduto.") na lista (id=".$idLista.")",$sql);
                }
            } else {
                $rs = $st->fetch();
                $id = $rs["id"];
                $sql = "UPDATE listas_itens SET ativo = ".$ativo." WHERE id = ".$id;
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    setlog("log_listas.txt",$_SESSION["login"]." alterou status do produto (id=".$idProduto.") de ".$rs["ativo"]." para ".$ativo,$sql);
                } else {
                    $erros .= "Erro ao salvar os dados (".$sql.")";
                    setlog("log_listas.txt",$_SESSION["login"].": Erro ao alterar status do ".$produto." (".$idProduto.") de ".$rs["ativo"]." para ".$ativo,$sql);
                }
            }
        }
        if ($erros != "") {
            echo $erros;
        } else {
            echo "ok";
        }
    }
} else {
    echo "Erro ao salvar os dados";
}
?>