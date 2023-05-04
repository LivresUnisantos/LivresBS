<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
include "../config.php";
include "helpers.php";
include "acesso.php";
include "menu.php";
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
?>
<form method="GET" action="">
    <label for="cpf">CPF</label>
    <input type="text" name="cpf" id="cpf" value="<?php (isset($_GET["cpf"])) ? $_GET["cpf"] : "" ?>" />
    <input type="submit" name="enviar" id="enviar" value="Enviar" />
</form>
<?php
//Salvar alterações no pedido
if (isset($_POST["idPedido"])) {
    $idPedido = $_POST["idPedido"];
    $quantidade = $_POST["quantidade"];
    $frequencia = $_POST["frequencia"];
    $sql = "UPDATE Pedidos SET frequencia = '".$frequencia."', quantidade = ".$quantidade." WHERE id = ".$idPedido;
    setLog("log.txt","Atualização Pedido Consumidor",$sql);
    $st = $conn->prepare($sql);
    $st->execute();
    echo "<p>Pedido Atualizado</p>";
}
//Realizar inclusões no pedido
if (isset($_POST["incluirProdutoIdProduto"])) {
    $idProduto = $_POST["incluirProdutoIdProduto"];
    $idConsumidor = $_POST["incluirProdutoIdConsumidor"];
    if ($idProduto != "" && $idConsumidor != "") {
        //Obter nome do produto para fim de exibição
        $sql = "SELECT id, nome FROM produtos WHERE id = ".$idProduto;
        $st = $conn->prepare($sql);
        $st->execute();
        $rs = $st->fetch();
        $nomeProduto = $rs["nome"];
        
        $sql = 'SELECT * FROM Pedidos WHERE IDConsumidor = '.$idConsumidor.' AND IDProduto = '.$idProduto;
        $st = $conn->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            echo '<p>Consumidor já possui o produto '.$nomeProduto.' cadastrado. Nenhuma alteração no pedido foi realizada.</p>';
        } else {
            $sql = "INSERT INTO Pedidos (IDConsumidor,IDProduto) VALUES (".$idConsumidor.",".$idProduto.")";
            setLog("log.txt","Inclusão Produto Pedido Consumidor",$sql);
            $st = $conn->prepare($sql);
            $st->execute();
            echo '<p>Produto '.$nomeProduto.' incluído. Não esqueça de preencher agora frequência e quantidade.</p>';
        }
    }
}
if (!isset($_GET["cpf"]) || $_GET["cpf"] == "") {
    exit();
}
$sql = "SELECT * FROM Consumidores WHERE cpf = ".$_GET["cpf"]." ORDER BY id DESC";
$st = $conn->prepare($sql);
$st->execute();

if ($st->rowCount() == 0) {
    echo "<p>Consumidor não encontrado</p>";
    exit();
} else {
    if ($st->rowCount() > 1) {
        echo '<p><span style="color:#FF0000;">Consumidor preencheu questionário mais de uma vez. Você irá editar a última resposta dele (que é a mesma que aparece na consulta de cesta).</span></p>';
    }
}
$rs=$st->fetch();
$idConsumidor=$rs["id"];
echo "Consumidor: ". ucwords(strtolower($rs["consumidor"]))."<br>";
echo "CPF: ".substr($rs["cpf"],0,3).".".substr($rs["cpf"],3,3).".".substr($rs["cpf"],6,3)."-".substr($rs["cpf"],9,2)."<br>";

$sql = "SELECT Pedidos.id AS idPedido, produtos.nome AS produto, Pedidos.Quantidade AS quantidade, produtos.unidade AS unidade, produtos.mensal AS mensal,";
$sql .= " Pedidos.Frequencia AS frequencia, produtos.previsao AS previsao, produtos.produtor AS produtor FROM Pedidos LEFT JOIN produtos ON produtos.id = Pedidos.IdProduto WHERE IDConsumidor = ".$idConsumidor;
$sql .= " ORDER BY produto";
$st = $conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0) {
    echo '<table border="1">';
    echo '<tr>';
    echo '<td>Produto</td>';
    echo '<td>Unidade</td>';
    echo '<td>Produtor</td>';
    echo '<td>Em Linha</td>';
    echo '<td>Quantidade</td>';
    echo '<td>Frequência</td>';
    echo '<td>Salvar</td>';
    echo '</tr>';
    $rs = $st->fetchAll();
    foreach ($rs as $row) {
        //Definir quantidades de acordo com unidade de medida
        $limiteQuantidades = defineLimitesQuantidade($row["unidade"]);
        echo '<tr>';
        echo '<form method="POST" action="">';
        echo '<td>'.$row["produto"].'</td>';
        echo '<td>'.$row["unidade"].'</td>';
        echo '<td>'.$row["produtor"].'</td>';
        echo '<td>';
        if (strtotime($row["previsao"]) <= strtotime(date('Y-m-d'))) {
            echo 'Sim';
        } else {
            echo "Não";
        }
        echo '</td>';
        //Quantidades
        echo '<td>';
        echo '<input type="hidden" name="idPedido" id="idPedido_"'.$row["idPedido"].'" value="'.$row["idPedido"].'" />';
        echo '<select id="quantidade_'.$row["idPedido"].'" name="quantidade">';
        //if abaixo serve para evitar loop infinito
        if ($limiteQuantidades["minimo"]<$limiteQuantidades["maximo"] && $limiteQuantidades["incremento"] > 0) {
            for ($i=$limiteQuantidades["minimo"]; $i<=$limiteQuantidades["maximo"]; $i+=$limiteQuantidades["incremento"]) {
                if (number_format(floatval($i),3,",",".") == number_format(floatval($row["quantidade"]),3,",",".")) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = "";
                }
                echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
            }
        }
        echo '</select>';
        echo '</td>';
        //Frequências
        echo '<td>';
        echo '<select id="frequencia_'.$row["idPedido"].'" name="frequencia">';
        $freqSel["Semanal"] = "";
        $freqSel["Quinzenal"] = "";
        $freqSel["Mensal"] = "";
        $freqSel[$row["frequencia"]] = ' selected="selected"';
        echo '<option value="Semanal"'.$freqSel["Semanal"].'>Semanal</option>';
        echo '<option value="Quinzenal"'.$freqSel["Quinzenal"].'>Quinzenal</option>';
        //if ($row["mensal"] == 1) {
            echo '<option value="Mensal"'.$freqSel["Mensal"].'>Mensal</option>';
        //}
        echo '</select>';
        echo '</td>';
        echo '<td><input type="submit" name="submit" id="submit_'.$row["idPedido"].'" value="Salvar" />';
        echo '</form>';
        echo '</tr>';
    }
    echo '</table>';
    //Tabela para incluir produtos
    echo "<p><b>Incluir produtos no pedido</b></p>";
    $sql = "SELECT * FROM produtos ORDER BY nome";
    $st = $conn->prepare($sql);
    $st->execute();
    $rs = $st->fetchAll();
    echo '<form method="POST" action="">';
    echo '<select name="incluirProdutoIdProduto" id="incluirProdutoIdProduto">';
    foreach ($rs as $row) {
        echo '<option value="'.$row["id"].'">';
        echo $row["nome"]. ' - ';
        if (strtotime($row["previsao"]) <= strtotime(date('Y-m-d'))) {
            echo "Em linha";
        } else {
            echo "Compromisso";
        }
        echo " (".$row["produtor"].")";
        echo '</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="incluirProdutoIdConsumidor" id="incluirProdutoIdConsumidor" value="'.$idConsumidor.'" />';
    echo '<input type="submit" name="submitIncluirProduto" id="submitIncluirProduto" value="Incluir" />';
    echo '</form>';
    /*echo '<table border="1">';
    echo '<tr>';
    echo '<td>Produto</td>';
    echo '<td>Quantidade</td>';
    echo '<td>Frequência</td>';
    echo '</tr>';
    foreach ($rs as $row) {
        $limiteQuantidades = defineLimitesQuantidade($row["unidade"]);
        echo '<tr>';
        echo '<td>'.$row["nome"].'</td>';
        echo '<td>';
        //if abaixo serve para evitar loop infinito
        echo '<select id="incluirQuantidade" name="incluirQuantidade">';
        if ($limiteQuantidades["minimo"]<$limiteQuantidades["maximo"] && $limiteQuantidades["incremento"] > 0) {
            for ($i=$limiteQuantidades["minimo"]; $i<=$limiteQuantidades["maximo"]; $i+=$limiteQuantidades["incremento"]) {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
        echo '</select>';
        echo '</td>';
        echo '<td>';
        echo '<select id="incluirFrequencia" name="incluirFrequencia">';
        echo '<option value="Semanal">Semanal</option>';
        echo '<option value="Semanal">Quinzenal</option>';
        if ($row["mensal"] == 1) {
            echo '<option value="Semanal">Mensal</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    */
} else {
    echo "Consumidor não possui pedido.";
}
?>
    </body>
</html>