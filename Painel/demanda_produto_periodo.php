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
/*$sql = "SELECT * FROM produtos ORDER BY ".$ordenar;
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
}*/
//Acrescentar demanda no array de produtos
/**/
//
if (isset($_GET["inicio"]) && isset($_GET["fim"])) {
    $form = '<form method="GET" action="">';
    $form .= '<input type="date" id="inicio" name="inicio" value="'.$_GET["inicio"].'" />';
    $form .= '<input type="date" id="fim" name="fim" value="'.$_GET["fim"].'" />';
    $form .= '<input type="submit" name="Enviar" id="Enviar" value="Enviar" />';
    $dtI = $_GET["inicio"];
    $dtF = $_GET["fim"];
    $form .= "</form>";
} else {
    $form = '<form method="GET" action="">';
    $form .= '<input type="date" id="inicio" name="inicio" value="" />';
    $form .= '<input type="date" id="fim" name="fim" value="" />';
    $form .= '<input type="submit" name="Enviar" id="Enviar" value="Enviar" />';
    $form .= "</form>";
}

if (!isset($dtI) || !isset($dtF)) {
    echo $form;
    exit();
}

$sql = "SELECT * FROM Parametros WHERE parametro = 'grupos'";
$st = $conn->prepare($sql);
$st->execute();
$rs = $st->fetch();
$grupos = $rs["valor"];

for ($i = 1; $i <= $grupos; $i++) {
    $demanda[$i]["Semanal"] = 0;
    $demanda[$i]["Quizenal"] = 0;
    $demanda[$i]["Mensal"] = 0;
}

$sql = "SELECT * FROM Calendario WHERE data >= '".$dtI."' AND data <= '".$dtF."' ";

$st = $conn->prepare($sql);
$st->execute();
$rs = $st->fetchAll();

foreach ($rs as $row) {
    for ($i = 1; $i <= $grupos; $i++) {
        $freq = $row[$i."acomunidade"];
        if (substr($freq,0,1) == "1") {
            $demanda[$i]["Semanal"]++;
        }
        if (substr($freq,1,1) == "1") {
            $demanda[$i]["Quinzenal"]++;
        }
        if (substr($freq,2,1) == "1") {
            $demanda[$i]["Mensal"]++;
        }
    }
}
//

echo '<table>';
echo '<tr class="firstLine">';
echo "<td>Grupo</td>";
echo "<td>Semanal</td>";
echo "<td>Quinzenal</td>";
echo "<td>Mensal</td>";
echo '</tr>';
echo '<tbody>';
for ($i = 1; $i <= $grupos; $i++) {
    if ($demanda[$i]["Semanal"] == "") $demanda[$i]["Semanal"] = 0;
    if ($demanda[$i]["Quinzenal"] == "") $demanda[$i]["Quinzenal"] = 0;
    if ($demanda[$i]["Mensal"] == "") $demanda[$i]["Mensal"] = 0;
    echo '<tr>';
    echo '<td>Grupo '.$i.'</td>';
    echo '<td>'.$demanda[$i]["Semanal"].'</td>';
    echo '<td>'.$demanda[$i]["Quinzenal"].'</td>';
    echo '<td>'.$demanda[$i]["Mensal"].'</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';

echo '<p>'.$form.'</p>';

$sql = 'SELECT ped.IDProduto, prod.nome, prod.categoria, prod.unidade, prod.produtor, ped.Frequencia, cons.comunidade, SUM(ped.Quantidade) as QtTotal, prod.previsao FROM Pedidos ped';
$sql .= ' LEFT JOIN Consumidores cons ON ped.IDConsumidor = cons.id LEFT JOIN produtos prod ON ped.IDProduto = prod.id';
$sql .= " WHERE ped.Frequencia <> '' AND prod.nome NOT LIKE '%zzz%' AND cons.ativo = 1";
$sql .= ' GROUP BY prod.id, ped.Frequencia, cons.comunidade ORDER BY prod.nome';

$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
foreach ($rs as $row)  {
    $produtos[$row["IDProduto"]]["Quantidade"] += $row["QtTotal"] * $demanda[$row["comunidade"]][$row["Frequencia"]];
    $produtos[$row["IDProduto"]]["Produto"] = [
        "nome" => $row["nome"],
        "categoria" => $row["categoria"],
        "unidade" => $row["unidade"],
        "produtor" => $row["produtor"],
        "emlinha" => ($row["previsao"] <= $dtI) ? "Sim" : "Não"
    ];
}

//
echo '<input type="text" id="filtro" placeholder="Digite para filtrar" />';
echo '<label for="quantidade">Apenas quantidade não zerada</label><input type="checkbox" id="quantidade" name="checkbox" value="" />';
echo '<label for="ativos">Apenas ativos</label><input type="checkbox" id="ativo" name="checkbox" value="" />';
echo '<table>';
echo '<tr class="firstLine">';
echo "<td>Produto</td>";
echo "<td>Unidade</td>";
echo "<td>Categoria</td>";
echo "<td>Produtor</td>";
echo "<td>Em linha</td>";
echo "<td>Total</td>";
echo '</tr>';
echo '<tbody id="tabela_produtos">';
$count=0;
echo "<pre>";
//print_r($produtos);
echo "</pre>";
foreach ($produtos as $id => $produto) {
    $count++;
    if ($count % 2 == 0) {
        echo '<tr bgcolor="#d1f1ff">';
    } else {
        echo '<tr>';
    }
    echo '<td><a href="https://livresbs.com.br/Painel/demanda_consumidor.php?id='.$id.'" target="_blank">'.$produto["Produto"]["nome"].'</a></td>';
    echo '<td>'.$produto["Produto"]["unidade"].'</td>';
    echo '<td>'.$produto["Produto"]["categoria"].'</td>';
    if ($produto["Produto"]["produtor"] == 'Coopeg - Cooperativa de Produtores Ecologistas de Garibaldi') {
        echo '<td>Coopeg</td>';
    } else {
        echo '<td>'.$produto["Produto"]["produtor"].'</td>';
    }
    echo '<td>'.$produto["Produto"]["emlinha"].'</td>';
    echo '<td>'.$produto["Quantidade"].'</td>';
    echo "</tr>";
}
echo '</tbody>';
echo '</table>';
?> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>
<script>
    $(document).ready(function(){
        $("#filtro").on("keyup", function() {
            /*var value = $(this).val().toLowerCase();
            $("#tabela_produtos tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
            */
            filtroCheckBox();
        });
    
        $("#quantidade").on("click", function() {
            filtroCheckBox();
        });
        
        $("#ativo").on("click", function() {
            filtroCheckBox();
        });
        
        
        function filtroCheckBox() {
            apenasNaoZero = $("#quantidade").is(':checked');
            apenasAtivo = $("#ativo").is(':checked');
            value = $("#filtro").val().toLowerCase();
            $("#tabela_produtos tr").filter(function() {
                qt = $(this).children("td")[5].innerHTML;
                ativo = $(this).children("td")[4].innerHTML.toLowerCase();
                ativo = (ativo == "sim") ? true : false;
                
                checkNaoZero = (apenasNaoZero && qt <= 0) ? false : true;
                checkAtivo = (apenasAtivo && !ativo) ? false : true;
                checkTexto = $(this).text().toLowerCase().indexOf(value) > -1;
                
                $(this).toggle(checkNaoZero && checkAtivo && checkTexto);
            });
        }
    });
</script>
</body>
</html>