<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);//

//Upload da lista de produtos
if (isset($_POST["act"]) && $_POST["act"] == "upload") {
    $filename = $_FILES['file']['name'];
    $grupo = $_POST["grupo"];
    
    /* Location */
    $filename = "lista_produtos_".$grupo."_".date("YmdHis").".pdf";
    $location ="../Consumidor/".$filename;
    $uploadOk = 1;
    $fileType = pathinfo($location,PATHINFO_EXTENSION);
    
    /* Valid Extensions */
    $valid_extensions = array("pdf");
    /* Check file extension */
    if( !in_array(strtolower($fileType),$valid_extensions) ) {
       $uploadOk = 0;
    }
    
    if($uploadOk == 0){
       $alerta = "Falha ao carregar arquivo";
    }else{
       /* Upload file */
       if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
          //atualizar link no banco de dados
            $sqlLink = "UPDATE Parametros SET valor = '".$filename."' WHERE parametro = 'arquivo".$grupo."'";
            $st = $conn->prepare($sqlLink);
            if ($st->execute()) {
                $alerta= "Arquivo atualizado";
            } else {
                $alerta= "Arquivo carregado, porém houve falha ao atualizar link nos pedidos. Carregue novamente.".$sqlLink;
            }
       }else{
          $alerta = "Falha ao carregar arquivo";
       }
    }
}
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
        </style>
    </head>
    <body>

<?php
echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
]);

if (isset($alerta)) {
    if (strlen($alerta) > 0) {
        echo "<script>alert('".$alerta."')</script>";
    }
}
$sqlParam = "SELECT * FROM Parametros WHERE parametro LIKE 'PedidosAvulsos%'";
$st = $conn->prepare($sqlParam);
$st->execute();
$rsParam = $st->fetchAll();

//Montar lista de grupos
foreach ($rsParam as $row) {
    $grupo = str_replace('PedidosAvulsos','',$row["parametro"]);
    $grupos[$grupo] = $row["valor"];
}

//título com status da página
/*if ($rsParam["valor"] == '0') {
    echo '<h3 id="tituloPedidos">Página de pedidos fechada</h3>';
} else {
    echo '<h3 id="tituloPedidos">Página de pedidos liberada</h3>';
}
*/
//link de mudança de status da página de pedido
/*echo '<p id="controlePedidos">';
if ($rsParam["valor"] == '0') {
    echo '<a href="#" controle="liberar">Liberar página de pedidos</a><br>';
} else {
    echo '<a href="#" controle="fechar">Fechar página de pedidos</a><br>';
}
*/
//remover pedidos
echo '<span class="controlePedidos">';
echo '<a href="#" controle="remover_pedidos">Remover todos os pedidos feitos</a>';
echo '</span>';

//upload lista produtos para cada grupo
echo '<table>';
echo '<tr>';
echo '<td>Status Página de Pedidos</td>';
echo '<td>Grupo</td>';
echo '<td>Lista Atual</td>';
echo '<td>Carregar Nova Lista</td>';
echo '</tr>';
foreach ($grupos as $grupo=>$status) {
    //encontrar link do grupo
    $sqlLink = "SELECT * FROM Parametros WHERE parametro = 'arquivo".$grupo."'";
    $st = $conn->prepare($sqlLink);
    $st->execute();
    if ($st->rowCount() == 0) {
        $link = "";
    } else {
        $rsLink = $st->fetch();
        $link = $rsLink["valor"];
    }
    echo '<tr>';
    echo '<td>'.$grupo.'</td>';
    if ($status == "0") {
        echo '<td><span class="controlePedidos">Desativado - <a href="#" controle="liberar" grupo="'.$grupo.'">Ativar</a></span></td>';
    } else {
        echo '<td><span class="controlePedidos">Liberado - <a href="#" controle="fechar" grupo="'.$grupo.'">Desativar</a></span></td>';
    }
    echo '<td><a href="../Consumidor/'.$link.'" target="_blank">Consultar arquivo Atual</a></td>';
    echo '<td>';
    echo '<form method="post" action="" enctype="multipart/form-data" id="myform">';
    echo '<input type="hidden" id="act1" name="act" value="upload" />';
    echo '<input type="hidden" id="grupo" name="grupo" value="'.$grupo.'" />';
    echo '<input type="file" id="file1" name="file" />';
    echo '<input type="submit" value="Enviar" id="but_upload">';
    echo '</form>';
    echo '</td>';
    echo "</tr>";
}
echo '</table>';

echo '<p>&nbsp;</p>';
?>

<div class="input-group mb-2 mr-sm-2">
    <div class="input-group-prepend">
        <div class="input-group-text">Filtro</div>
    </div>
    <input type="text" class="form-control" id="filtro" name="Filtro" placeholder="Procure por nome, email, produto ou grupo">
</div>

<?php
$sql = "SELECT * FROM PedidosAvulsos WHERE pedido_inativo = 0 ORDER BY nome ASC";
$st = $conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0) {
    $rs=$st->fetchAll();
    echo '<table>';
    echo '<thead>';
    echo '<tr class="firstLine">';//
    echo '<td>Nome</td>';
    echo '<td>Email</td>';
    echo '<td>Endere&ccedil;o</td>';
    echo '<td>Entrega</td>';
    echo '<td>CPF</td>';
    echo '<td>Grupo</td>';
    echo '<td>Telefone</td>';
    echo '<td>Pedido</td>';
    echo '<td>Data do Pedido</td>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody id="tabela_produtos">';
    $count=0;
    foreach ($rs as $row) {
        $count++;
        if ($count % 2 == 0) {
            echo '<tr bgcolor="#d1f1ff">';
        } else {
            echo '<tr>';
        }
        echo '<td>'.$row["nome"].'</td>';
        echo '<td>'.$row["email"].'</td>';
        echo '<td>'.$row["endereco"].'</td>';
        echo '<td>'.$row["entrega"]."</td>";
        echo '<td>'.$row["cpf"].'</td>';
        echo '<td>'.$row["grupo"].'</td>';
        echo '<td>'.$row["telefone"].'</td>';
        echo '<td>'.$row["pedido"].'</td>';
        echo '<td>'.date("d/m/Y H:i",strtotime($row["data_criacao"])).'</td>';
        echo "</tr>";
    }
    echo '<tbody>';
    echo '</table>';
}
?> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$(document).ready(function() {
    $('.controlePedidos a').click(function() {
        clicado = $(this);
        //liberar/bloquear página de pediods
        if (clicado.attr('controle') == "liberar" || clicado.attr('controle') == "fechar") {
            if (clicado.attr('controle') == "liberar") {
                valor = 1;
            } else {
                valor = 0;
            }
            grupo = clicado.attr('grupo');
            $.ajax({
                method: "POST",
                url: "pedidos_avulsos_act.php",
                data: {
                    act: "PaginaPedidos",
                    grupo: grupo,
                    valor: valor
                }
            })
            .done(function() {
                alert("Alteração Realizada. A página será atualizada.");
                location.reload();
            })
            .fail(function() {
                alert("Falha ao realizar alteração, tente novamente.");
            });
        }
        //remover pedidos feitos
        if (clicado.attr('controle') == "remover_pedidos") {
            if (confirm("Deseja remover todos os pedidos realizados?")) {
                $.ajax({
                    method: "POST",
                    url: "pedidos_avulsos_act.php",
                    data: {
                    act: "RemoverPedidos",
                    }
                })
                .done(function() {
                    alert('Alteração realizada. A página será atualizada.');
                    location.reload();
                })
                .fail(function() {
                    alert('Falha ao remover pedidos. Tente novamente.');
                });
            }
        }
    });
});
</script>
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