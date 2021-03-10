<?php
$levelRequired=10000;
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
    <link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="painel.css">
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
            .red {
                color:#ff0000;
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

if ($_SESSION["level"] >= 15000) {
    if (isset($_GET["imprimir"])) {
        echo '<a href="produtos.php">Habilitar campos para edição</a>';
    } else {
        echo '<a href="?imprimir=1">Habilitar versão para impressão</a>';
    }
}
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
if (isset($_POST["add_nome"])) {
    echo '<p><span class="red">';
    //Cadastrar novo produto
    $nome = $_POST["add_nome"];
    $nome = ucwords(strtolower($nome));
    $nome=addslashes($nome);
    $categoria = $_POST["add_categoria"];
    $unidade = $_POST["add_unidade"];
    $produtor = $_POST["add_produtor"];
    if ($nome=="" || $categoria =="" || $unidade == "" || $produtor == "") {
        echo "Produto não cadastrado. Preencha todos os campos.";
    } else {
        $sql = "SELECT * FROM produtos WHERE nome = '".$nome."'";
        $st = $conn->prepare($sql);
        $st->execute();
        if ($st->rowCount()>0) {
            echo "Produto não cadastrado. Já existe um produto com esse nome.";
        } else {
            $sql = "INSERT INTO produtos (nome,categoria,unidade,produtor,prazo, unidade2) VALUES ('".$nome."','".$categoria."','".$unidade."','".$produtor."','Imediato','".$unidade."')";
            $st = $conn->prepare($sql);
            if ($st->execute()) {
                echo "Produto '".$nome."' cadastrado. Agora edite o produto para cadastrar seu preço e data de disponibilidade correta.";
            } else {
                echo "Erro ao cadastrar produto '".$nome."'";
            }
        }
    }
    echo '</span></p>';
}
if (isset($_POST["idProduto"])) {
    //Salvar alterações nos produtos
    $previsao = $_POST["previsao"];
    $produtor = $_POST["produtor"];
    $preco_produtor = $_POST["preco_produtor"];
    $preco = $_POST["preco"];
    $preco_lojinha = $_POST["preco_lojinha"];
    $preco_mercado = $_POST["preco_mercado"];
    $preco_pre = $_POST["preco_pre"];
    $cesta_mensal = $_POST["cesta_mensal"];
    $carrinho = $_POST["carrinho"];
    $idProduto = $_POST["idProduto"];
    
    $preco_produtor=str_replace(",",".",$preco_produtor);
    $preco=str_replace(",",".",$preco);
    $preco_lojinha=str_replace(",",".",$preco_lojinha);
    $preco_mercado=str_replace(",",".",$preco_mercado);
    $preco_pre=str_replace(",",".",$preco_pre);
    
    if ($previsao == "") { $previsao = "null"; }
    if ($preco_produtor == "") { $preco_produtor = "null"; }
    if ($preco == "") { $preco = "null"; }
    if ($preco_lojinha == "") { $preco_lojinha = "null"; }
    if ($preco_mercado == "") { $preco_mercado = "null"; }
    if ($preco_pre == "") { $preco_pre = "null"; }

    $sql = "UPDATE produtos SET previsao = '".$previsao."', produtor='".$produtor."', preco_produtor=".$preco_produtor.", preco=".$preco.",
            preco_lojinha=".$preco_lojinha.", preco_mercado=".$preco_mercado.", preco_pre=".$preco_pre.",
            mensal = ".$cesta_mensal.", carrinho = ".$carrinho." WHERE id = ".$idProduto;
    $st = $conn->prepare($sql);
    $st->execute();
    setLog("log.txt","Atualização produto.",$sql);
}
//Array de datas
$sql = "SELECT * FROM Calendario ORDER BY data ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs = $st->fetchAll();
foreach ($rs as $row) {
    $datas[] = $row["data"];
}
//Array de produtores
$sql = "SELECT * FROM Produtores ORDER BY Produtor";
$st = $conn->prepare($sql);
$st->execute();
foreach ($st->fetchAll() as $row) {
    $arrProdutores[$row["id"]] = $row["Produtor"];
}
$sql = "SELECT * FROM produtos ORDER BY nome ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
//echo date();
echo '<input type="text" id="filtro" placeholder="Digite para filtrar" />';
echo '<table>';
echo '<tbody>';
echo '<tr class="firstLine">';
echo '<td>Nome</td>';
echo '<td>Categoria</td>';
echo '<td>Unidade</td>';
echo '<td>Produtor</td>';
echo '<td>Em linha?</td>';
echo '<td>Preço Produtor</td>';
echo '<td>Preço Comboio</td>';
echo '<td>Preço Consumo Consciente</td>';
echo '<td>Preço Livre Mercado</td>';
echo '<td>Preço Pré-comunidade</td>';
echo '<td>Cesta Mensal?</td>';
echo '<td>Carrinho?</td>';
if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
    echo '<td>Salvar</td>';
}
echo '</tr>';
echo '</tbody>';
echo '<tbody id="tabela_produtos">';
$count=0;
foreach ($rs as $row) {
    $count++;
    if ($count % 2 == 0) {
        echo '<tr bgcolor="#d1f1ff">';
    } else {
        echo '<tr>';
    }
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="idProduto" id="idProduto" value="'.$row["id"].'" />';
    echo '<td>'.$row["nome"].'</td>';
    echo '<td>'.$row["categoria"].'</td>';
    echo '<td>'.$row["unidade"].'</td>';
    if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
        echo '<td>';
        echo '<select id="produtor_'.$row["id"].'" name="produtor">';
        foreach ($arrProdutores as $produtor) {
            if ($produtor == $row["produtor"]) {
                echo '<option value="'.$produtor.'" selected="selected">'.$produtor.'</option>';
            } else {
                echo '<option value="'.$produtor.'">'.$produtor.'</option>';
            }
        }
        echo '</select>';
        echo '</td>';
    } else {
        echo '<td>'.$row["produtor"].'</td>';
    }
    if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
        echo '<td>';
        echo '<select id="previsao_'.$row["id"].'" name="previsao">';
        echo '<option></option>';
        foreach ($datas as $data) {
            echo '<option value="'.$data.'"';
            if (strtotime($data) == strtotime($row["previsao"])) {
                echo 'selected = "selected"';
            }
            echo '">'.date("d/m/Y",strtotime($data)).'</option>';
        }
        echo '</select>';
        echo '</td>';
    } else {
        echo '<td>'.(strtotime($row["previsao"]) <= strtotime(date('Y-m-d')) ? "Desde ".date("d/m/Y",strtotime($row["previsao"])) : "Não").'</td>';
    }
    if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
        echo '<td><input size="5" type="text" id="preco_produtor_'.$row["id"].'" name="preco_produtor" value="'.str_replace(".",",",$row["preco_produtor"]).'" /></td>';
        echo '<td><input size="5" type="text" id="preco_'.$row["id"].'" name="preco" value="'.str_replace(".",",",$row["preco"]).'" /></td>';
        echo '<td><input size="5" type="text" id="preco_lojinha_'.$row["id"].'" name="preco_lojinha" value="'.str_replace(".",",",$row["preco_lojinha"]).'" /></td>';
        echo '<td><input size="5" type="text" id="preco_mercado_'.$row["id"].'" name="preco_mercado" value="'.str_replace(".",",",$row["preco_mercado"]).'" /></td>';
        echo '<td><input size="5" type="text" id="preco_pre_'.$row["id"].'" name="preco_pre" value="'.str_replace(".",",",$row["preco_pre"]).'" /></td>';
    } else {
        echo '<td>R$'.number_format($row["preco_produtor"],2,",",".").'</td>';
        echo '<td>R$'.number_format($row["preco"],2,",",".").'</td>';
        echo '<td>R$'.number_format($row["preco_lojinha"],2,",",".").'</td>';
        echo '<td>R$'.number_format($row["preco_mercado"],2,",",".").'</td>';
        echo '<td>R$'.number_format($row["preco_pre"],2,",",".").'</td>';
    }
    if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
        echo '<td>';
        echo '<select name="cesta_mensal" id="cesta_mensal_'.$row["id"].'">';
        echo '<option value="0" '.($row["mensal"]==0 ? 'selected="selected"' : '').'>Não</option>';
        echo '<option value="1" '.($row["mensal"]==1 ? 'selected="selected"' : '').'>Sim</option>';
        echo '</select>';
        echo '</td>';
    } else {
        echo '<td>'.($row["mensal"]==1 ? "sim" : "não").'</td>';
    }
    if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
        echo '<td>';
        echo '<select name="carrinho" id="carrinho_'.$row["id"].'">';
        echo '<option value="0" '.($row["carrinho"]==0 ? 'selected="selected"' : '').'>Não</option>';
        echo '<option value="1" '.($row["carrinho"]==1 ? 'selected="selected"' : '').'>Sim</option>';
        echo '</select>';
        echo '</td>';
    } else {
        echo '<td>'.($row["carrinho"]==1 ? "sim" : "não").'</td>';
    }
    if (!isset($_GET["imprimir"]) && $_SESSION["level"] >= 15000) {
        echo '<td><input type="submit" id="submit_'.$row["id"].'" name="submit" value="Salvar"></td>';
    }
    echo "</form>";
    echo "</tr>";
}
echo '</tbody>';
echo '</table>';
if ($_SESSION["level"] >= 15000 && !isset($_GET["imprimir"])) {
?>
<p></p><p></p>
<form method="POST" action="">
    <table>
        <tr>
            <td colspan="2">Cadastrar Novo Produto</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td>Nome Produto</td>
            <td><input type="text" name="add_nome" id="add_nome"></td>
        </tr>
        <tr>
            <td>Categoria</td>
            <td>
                <?php
                $sql = "SELECT * FROM CategoriasProdutos ORDER BY Categoria";
                $st = $conn->prepare($sql);
                $st->execute();
                $rs = $st->fetchAll();
                echo '<select id="add_categoria" name="add_categoria">';
                echo '<option value=""></option>';
                foreach ($rs as $row) {
                    echo '<option value="'.$row["Categoria"].'">'.$row["Categoria"].'</option>';
                }
                echo "</select>";
                ?>
            </td>
        </tr>
        <tr>
            <td>Unidade</td>
            <td>
                <select id="add_unidade" name="add_unidade">
                    <option value=""></option>
                    <option value="unidade">unidade</option>
                    <option value="kg">kg</option>
                    <option value="dúzia">dúzia</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Produtor</td>
            <td>
                <?php
                $sql = "SELECT * FROM Produtores ORDER BY Produtor";
                $st = $conn->prepare($sql);
                $st->execute();
                $rs = $st->fetchAll();
                echo '<select id="add_produtor" name="add_produtor">';
                echo '<option value=""></option>';
                foreach ($rs as $row) {
                    echo '<option value="'.$row["Produtor"].'">'.$row["Produtor"].'</option>';
                }
                echo "</select>";
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" name="add" id="add" value="Cadastrar" />
            </td>
        </tr>
</form>
<?php
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>

<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.js"></script>
<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.pt-BR.js"></script>

<script>
$(function () {
	//DATEPICKER CONFIG
	var start = new Date(), prevDay, startHours = 0;

	// ÚLTIMO HORÁRIO
	start.setHours(0);
	start.setMinutes(0);

	var dataPicker = $('.jwc_datepicker_start').datepicker({
		timepicker: true,
		startDate: start,
		minHours: startHours
	}).data('datepicker');
});
</script>
<script>
    $(document).ready(function(){
      $("#filtro").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tabela_produtos tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
    });
</script>
    </body>
</html>