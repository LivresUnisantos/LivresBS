<?php
$levelRequired=15000;
include "../config.php";
include "acesso.php";
include "helpers.php";

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link rel="stylesheet" href="painel.css">
        <style>
            table, td {
                border: 1px solid black;
                border-collapse: collapse;
                text-align: center;
            }
            .firstLine, .firstLine a {
                background: #11479e;
                color:#fff;
                font-weight:bold;
            }
        </style>
    </head>
    <body>
<?php
echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);

//Iniciar array de produtos
$totalGrupos = getTotalGrupos($conn);
//$diasEntregas = getDiasEntregas($conn);
if (isset($_GET["ordenar"])) {
    $ordenar = $_GET["ordenar"];
} else {
    $ordenar = "nome";
}
if ($ordenar != "nome" && $ordenar != "produtor" && $ordenar != "categoria") {
    $ordenar = "nome";
}
$sql = "SELECT * FROM produtos ORDER BY ".$ordenar;
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
foreach ($rs as $row) {
    $produtos[$row["id"]]["nome"] = $row["nome"];
    $produtos[$row["id"]]["unidade"] = $row["unidade"];
    $produtos[$row["id"]]["categoria"] = $row["categoria"];
    $produtos[$row["id"]]["produtor"] = $row["produtor"];
    $produtos[$row["id"]]["previsao"] = $row["previsao"];
    for ($i = 1; $i <= $totalGrupos; $i++) {
        $produtos[$row["id"]]["quantidade"]["Semanal"][$i] = 0;
        $produtos[$row["id"]]["quantidade"]["Quinzenal"][$i] = 0;
        $produtos[$row["id"]]["quantidade"]["Mensal"][$i] = 0;
    }
}
//Acrescentar demanda no array de produtos
$sql = "SELECT * FROM Pedidos LEFT JOIN Consumidores ON Consumidores.id = Pedidos.IDConsumidor";
$sql .= " WHERE Consumidores.ativo = 1 AND Pedidos.Quantidade > 0";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
foreach ($rs as $row)  {
    $produtos[$row["IDProduto"]]["quantidade"][$row["Frequencia"]][$row["comunidade"]] += $row["Quantidade"];
}
echo '<table>';
echo '<tr class="firstLine">';
echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
for ($i = 1; $i <= $totalGrupos; $i++) {
    echo '<td colspan="2">Grupo '.$i.'</td>';
}
 //colspan ainda forçado manualmente, porque preciso lidar com entregas sábados e terças
echo '<td colspan="2">Entrega Prevista</td>';
echo '<td colspan="'.($totalGrupos+1).'">Mensal</td>';
echo '</tr>';
echo '<tr class="firstLine">';
echo '<td><a href="?ordenar=nome">Produto</a></td>';
echo '<td>Unidade</td>';
echo '<td><a href="?ordenar=categoria">Categoria</a></td>';
echo '<td><a href="?ordenar=produtor">Produtor</a></td>';
echo '<td>Em linha?</td>';
for ($i = 1; $i <= $totalGrupos; $i++) {
    echo '<td>Semanal</td>';
    echo '<td>Quinzenal</td>';
}
echo '<td>G1 S + G2 Q + G3 S</td>';
echo '<td>G1 Q + G2 S + G3 Q</td>';
for ($i = 1; $i <= $totalGrupos; $i++) {
    echo '<td>G'.$i.'</td>';
}
/*
for ($i = 1; $i <= $totalGrupos; $i++) {
    echo '<td>G'.$i.'</td>';
}*/
echo '<td>';
for ($i = 1; $i <= $totalGrupos; $i++) {
    if ($i > 1) {
        echo "+";
    }
    echo "G".$i;
}
echo '</td>';
echo '</tr>';
$count=0;
foreach ($produtos as $idProduto=>$produto) {
    $count++;
    if ($count % 2 == 0) {
        echo '<tr bgcolor="#d1f1ff">';
    } else {
        echo '<tr>';
    }
    echo '<td><a href="demanda_consumidor.php?id='.$idProduto.'" target="_blank">'.$produto["nome"].'</td>';
    echo '<td>'.$produto["unidade"].'</td>';
    echo '<td>'.$produto["categoria"].'</td>';
    echo '<td>'.$produto["produtor"].'</td>';
    echo '<td>'.(strtotime($produto["previsao"]) <= strtotime(date('Y-m-d')) ? "Desde ".date("d/m/Y",strtotime($produto["previsao"])) : "Não").'</td>';
    //por grupo e frequencia
    for ($i = 1; $i <= $totalGrupos; $i++) {
        echo '<td>'.$produto["quantidade"]["Semanal"][$i].'</td>';
        echo '<td>'.($produto["quantidade"]["Semanal"][$i]+$produto["quantidade"]["Quinzenal"][$i]).'</td>';
    }
    //por entrega
    echo '<td>'.($produto["quantidade"]["Semanal"][1]+$produto["quantidade"]["Quinzenal"][2]+$produto["quantidade"]["Semanal"][2]+$produto["quantidade"]["Semanal"][3]).'</td>';
    echo '<td>'.($produto["quantidade"]["Quinzenal"][1]+$produto["quantidade"]["Semanal"][1]+$produto["quantidade"]["Semanal"][2]+$produto["quantidade"]["Quinzenal"][3]+$produto["quantidade"]["Semanal"][3]).'</td>';
    //mensal
    for ($i = 1; $i <= $totalGrupos; $i++) {
        echo '<td>'.$produto["quantidade"]["Mensal"][$i].'</td>';
    }
    echo '<td>';
    $soma=0;
    for ($i = 1; $i <= $totalGrupos; $i++) {
        $soma += $produto["quantidade"]["Mensal"][$i];
    }
    echo $soma;
    echo '</td>';
    echo "</tr>";
}
echo '</table>';
?> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>
    </body>
</html>