<?php
$levelRequired=15000;
include "../config.php";
include "acesso.php";
include "helpers.php";
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
        <style>
            table, td {
                border: 1px solid black;
                border-collapse: collapse;
                text-align: center;
            }
            .firstLine {
                background: #11479e;
                color:#fff;
                font-weight:bold;
            }
        </style>
    </head>
    <body>
<?php
include "menu.php";
if (!isset($_GET["id"])) {
    exit("Produto não selecionado");
}
$idProduto = $_GET["id"];
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
//Iniciar array de produtos
$sql = "SELECT * FROM produtos WHERE id = ".$idProduto;
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetch();
echo "<p>Demanda para o produto ".$rs["nome"].". Quantidades exibidas em ".$rs["unidade"].".</p>";
echo "<p>Exibindo apenas pedidos de consumidores ativos</p>";
//Acrescentar demanda no array de produtos
$sql = "SELECT * FROM Pedidos LEFT JOIN Consumidores ON Consumidores.id = Pedidos.IDConsumidor";
$sql .= " WHERE Consumidores.ativo = 1 AND Pedidos.Quantidade > 0 AND Pedidos.IDProduto = ".$idProduto." ORDER BY comunidade,frequencia,consumidor";
$st = $conn->prepare($sql);
$st->execute();

if ($st->rowCount() > 0) {
    $rs=$st->fetchAll();
    echo '<table>';
    echo '<tr class="firstLine">';
    echo '<td>Consumidor</td>';
    echo '<td>Grupo</td>';
    echo '<td>Quantidade</td>';
    echo '<td>Frequência</td>';
    echo '<td></td>';
    echo '</tr>';
    $count=0;
    foreach ($rs as $row)  {
        $count++;
        if ($count % 2 == 0) {
            echo '<tr bgcolor="#d1f1ff">';
        } else {
            echo '<tr>';
        }
        echo '<td>'.ucwords(strtolower($row["consumidor"])).'</td>';
        echo '<td>G'.$row["comunidade"].'</td>';
        echo '<td>'.$row["Quantidade"].'</td>';
        echo '<td>'.$row["Frequencia"].'</td>';
        echo '<td><a href="editar_cesta.php?cpf='.$row["cpf"].'">Editar Cesta</a></td>';
        echo "</tr>";
    }
    echo '</table>';
}
?> 
    </body>
</html>