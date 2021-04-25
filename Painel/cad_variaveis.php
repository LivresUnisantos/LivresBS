<?php
$levelRequired=11000;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config.php";
include "acesso.php";
include "helpers.php";
/*if (!isset($_GET["imprimir"])) {
    include "menu.php";
}*/
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        function apagar(destino) {
            var r = confirm("Deseja realmente apagar?");
            if (r == true) {
                window.location.href= destino;
            }
        }
	</script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<link rel="stylesheet" href="painel.css">
	<style>
		table, td {
			border: 1px solid #bbd6ee;
			border-collapse: collapse;
			text-align: center;
			font-family: Calibri;
			<?php
            if (isset($_GET["imprimir"])) {
                echo 'font-size: 10px;';
            }
            ?>
		}
		.firstLine {
			background: #5b9bd5;
			color:#fff;
			font-weight:bold;
		}
		.lineColor {
		    background: #ddebf7;
		}
		.lineBlank {
		    background: #fff;
		}
		#previewImage {
		    position:absolute;
		    left: 100px;
		    z-index: 1;
		    display:none;
		    background: #fff;
		    text-align:center;
		    width:600px;
		}
		#downloadImage {
		    display:none;
		}
	</style>
    <title>Livres - Comboio Orgânico</title>
  </head>
  <body>
  <?php
echo $twig->render('menu.html', [
	"titulo" 			=> "LivresBS",
	"menu_datas" 		=> $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
	"frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);
?>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
if (!isset($_SESSION["data_id"])) {
	echo "Selecione uma data";
} else {
    $msg = "";
    $nome="";
    $preco="";
    $preco_impessoal="";
	if (strlen($_SESSION["data_id"]) > 0) {
	    //Apagar produto escolhido
	    if (isset($_GET["id"])) {
	        $sql = "DELETE FROM produtosVar WHERE id = ".$_GET["id"];
	        $st = $conn->prepare($sql);
	        $st->execute();
	        setlog('log.txt','Produto removido variáveis ('.$_GET["id"].')',$sql);
	    }
	    //Cadastrar produto enviado
	    if (isset($_POST["data"])) {
			$data = $_POST["data"];
	        $produto = $_POST["produto"];
	        $estoque = $_POST["estoque"];
	        $estoque = str_replace(",",".",$estoque);
	        if (strlen($produto) > 0 && strlen($estoque) > 0) {
				$sql = "SELECT * FROM produtosVar WHERE idCalendario = ".$data." AND idProduto = ".$produto;
				$st = $conn->prepare($sql);
				$st->execute();

				if ($st->rowCount() > 0) {
					$msg = "Produto já cadastrado para este dia";
				} else {
					$sql = "INSERT INTO produtosVar (idCalendario,idProduto,estoque) VALUES (".$data.",".$produto.",".$estoque.")";
					$st = $conn->prepare($sql);
					$st->execute();
					$nome="";
					$preco="";
					$preco_impessoal="";
					$msg = "Produto cadastrado com sucesso. Confirme as informações na tabela acima.";
					setlog('log.txt','Cadastro produto variáveis ('.$produto.')',$sql);
				}
	        } else {
	            $msg = "Preencha todos os campos para que o produto seja cadastrado";
	        }
	    }
	    //Mostrar produtos cadastrados
	    $sql = "SELECT produtosVar.estoque as estoque, produtos.nome AS nome, produtos.preco_mercado AS preco_mercado, produtos.preco AS preco,produtos.id AS idProduto, produtosVar.id AS idRelacao ";
	    $sql .= "FROM produtosVar LEFT JOIN produtos ON produtosVar.idProduto = produtos.id WHERE idCalendario = ".$livres->dataPelaString($_SESSION["data_consulta"])." ORDER BY produtos.nome ASC";
        $st = $conn->prepare($sql);
		$st->execute();
		echo "Produtos variáveis para entrega de ".Datetime::createfromformat('Y-m-d H:i', $_SESSION["data_consulta"])->format('d/m/Y');
        if ($st->rowCount() > 0) {
            $rs=$st->fetchAll();
            echo "<table>";
            echo "<tr><td>Produto</td><td>Preço Livre Mercado</td><td>Preco Comunidade</td>";
            echo "<td>Estoque</td><td>Apagar</td></tr>";
            foreach ($rs as $row) {
                echo '<tr>';
                echo '<td>'.$row["nome"].'</td>';
                echo '<td>R$'.number_format($row["preco_mercado"],2,",",".").'</td>';
                echo '<td>R$'.number_format($row["preco"],2,",",".").'</td>';
                echo '<td>'.$row["estoque"].'</td>';
                echo '<td><a href="javascript:apagar(\'?data='.$_SESSION["data_id"].'&id='.$row["idRelacao"].'\')">Apagar</a></td>';
                echo "</tr>";
            }
            echo "</table>";
        }
	    //Formulário de cadastro de produto
	    ?>
	    <p></p>
	    <form method="POST" action="">
	        <input type="hidden" name="data" id="data" value="<?php echo $livres->dataPelaString($_SESSION["data_consulta"]); ?>" />
	        <span style="color:#FF0000;"><?php echo $msg; ?></span>
	        <table>
	            <tr><td colspan="2">Cadastrar Novo Produto</td></tr>
                <tr><td>Nome Produto</td><td>
                    <select id="produto" name="produto">
                        <option value=""></option>
                    <?php
                    $sqlProdutos = "SELECT * FROM produtos ORDER BY nome ASC";
                    $st = $conn->prepare($sqlProdutos);
                    $st->execute();
                    $rsProdutos=$st->fetchAll();
                    foreach ($rsProdutos as $rowProdutos) {
                        echo '<option value="'.$rowProdutos["id"].'">'.$rowProdutos["nome"].'('.$rowProdutos["unidade"].') - '.number_format($rowProdutos["preco"],2,",",".").'</option>';
                    }
                    ?>
                    </select>
                </td></tr>
                <tr>
                    <td>Estoque Disponível (1000 para "temos bastante")</td>
                    <td>
                        <input type="text" id="estoque" name="estoque" value="" />
                    </td>
                </tr>
                <tr><td colspan="2"><input type="submit" name="submit" id="submit" value="Salvar" /></td></tr>
            </table>
	    </form>
	    <?php
	}
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>

<link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
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
  </body>
</html>
<?php
function ccase($str) {
	return ucwords(strtolower($str));
}
function abvNome($nome) {
    $path = explode(" ",$nome);
    for ($i = 0;$i < count($path); $i++) {
        if ($i == 0) {
            $nome = $path[$i];
        } else {
            if ($i == count($path)-1) {
                $nome .= " ".$path[$i];
            } else { 
                $nome .= " ".substr($path[$i],0,1);
            }
        }
    }
    return $nome;
}
function friendlyFreq($freq) {
	$frequencia = "";
	if (substr($freq,0,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " = ";
		}
		$frequencia .= "Semanal";
	}
	if (substr($freq,1,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " + ";
		}
		$frequencia .= "Quinzenal";
	}
	if (substr($freq,2,1) == "1") {
		if (strlen($frequencia)>0) {
			$frequencia .= " + ";
		}
		$frequencia .= "Mensal";
	}
	return $frequencia;
}
?>