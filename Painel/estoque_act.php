<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";
require_once "helpers.php";

if (isset($_POST["idProduto"]) && isset($_POST["estoque"])) {
    $livres = new Livres();
    $conn = $livres->conn();

    $idProdutos = explode("@",$_POST["idProduto"]);
    $estoques = explode("@", $_POST["estoque"]);
    $ultimas = explode("@", $_POST["ultima"]);
    $proximas = explode("@", $_POST["proxima"]);
    
    if (count($idProdutos) != count($estoques) || count($idProdutos) != count($ultimas) || count($idProdutos) != count($proximas)) {
        echo "Erro ao salvar dados. Arrays com dimensões erradas";
        echo count($idProdutos);
        echo "<br><br>";
        echo count($estoques);
        echo "<br><br>";
        echo count($ultimas);
        echo "<br><br>";
        echo count($proximas);
    } else {
        $erros = "";
        for($i = 0; $i < count($idProdutos); $i++) {
            $idProduto = $idProdutos[$i];
            $estoque = $estoques[$i];
            $ultima = $ultimas[$i];
            $proxima = $proximas[$i];
            
            if ($estoque == "") $estoque = 0;
            
            if ($ultima == "") {
                $ultima = 'NULL';
            } else {
                $ultima = "'".date("Y-m-d H:i:s",strtotime($ultima))."'";
            }
            if ($proxima == "") {
                $proxima = 'NULL';
            } else {
                $proxima = "'".date("Y-m-d H:i:s",strtotime($proxima))."'";
            }
            
            $sql = "SELECT * FROM produtos WHERE id = ".$idProduto;
            $st = $conn->prepare($sql);
            $st->execute();
            
            if ($st->rowCount() == 0) {
                $erros .= "Produto não encontrado (".$idProduto.")";
                setlog("log_estoque.txt",$_SESSION["login"]." tentou alterar estoque de produto inexistente (id=".$idProduto.")",$sql);
            } else {
                $rs = $st->fetch();
                $estoqueAnterior = $rs["estoque"] ;
                $produto = $rs["nome"];
                $produtor = $rs["produtor"];
                $estoque = str_replace(",",".",$estoque);
                $sql = "UPDATE produtos SET estoque = ".$estoque.", data_ultimaentrada = ".$ultima.", data_proximovencimento = ".$proxima." WHERE id = ".$idProduto;
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    setlog("log_estoque.txt",$_SESSION["login"]." alterou datas/estoque do ".$produto." (".$produtor.") de ".$estoqueAnterior." para ".$estoque,$sql);
                } else {
                    $erros .= "Erro ao salvar os dados (".$sql.")";
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