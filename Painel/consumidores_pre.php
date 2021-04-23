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
?>
<div class="input-group mb-2 mr-sm-2">
    <div class="input-group-prepend">
        <div class="input-group-text">Filtro</div>
    </div>
    <input type="text" class="form-control" id="filtro" name="Filtro" placeholder="Procure por nome, email, produto ou grupo">
</div>

<?php
//alteração de grupo
if (isset($_POST["id_consumidor"])) {
    $id = $_POST["id_consumidor"];
    $grupo = $_POST["grupo"];
    
    $sqlUpdate = "UPDATE Usuarios SET grupo = '$grupo' WHERE id = ".$id;
    $st = $conn->prepare($sqlUpdate);
    if ($st->execute()) {
        echo "Grupo alterado<br>";
        $sqlSearch = "SELECT * FROM Usuarios WHERE id = ".$id;
        $st = $conn->prepare($sqlSearch);
        $st->execute();
        $rsS = $st->fetch();
        echo $rsS["nome"]." alterado para ".$grupo."<br>";
    } else {
        echo "Falha ao alterar grupo<br>";
    }
}

$grupos = array("APROATE","Não sei","pre-comunidade","AOVALE");
$sql = "SELECT * FROM Usuarios ORDER BY nome ASC";
$st = $conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0) {
    $rs=$st->fetchAll();
    echo '<table>';
    echo '<thead>';
    echo '<tr class="firstLine">';//
    echo '<td>Nome</td>';
    echo '<td>Email</td>';
    echo '<td>CPF</td>';
    echo '<td>Endereço</td>';
    echo '<td>Grupo</td>';
    echo '<td>Alterar Senha</td>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody id="tabela_cons_pre">';
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
        echo '<td>'.$row["cpf"].'</td>';
        echo '<td>'.$row["endereco"].'</td>';
        echo '<td>';//.$row["grupo"]
        echo '<form method="post" action="">';
        echo '<input type="hidden" id="id_consumidor_'.$row["id"].'" name="id_consumidor" value="'.$row["id"].'" />';
        echo '<select id="grupo_'.$row["id"].'" name="grupo">';
        foreach ($grupos as $grupo) {
            if ($grupo == $row["grupo"]) {
                echo '<option selected="selected" value="'.$grupo.'">'.$grupo.'</option>';
            } else {
                echo '<option value="'.$grupo.'">'.$grupo.'</option>';
            }
        }
        echo '</select>';
        echo '<input type="submit" value="Alterar" />';
        echo '</form>';
        echo '</td>';
        echo '<td><a href="consumidores_pre_act.php?act=senha&id='.$row["id"].'" target="_blank">Alterar Senha</a></td>';
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
        $("#tabela_cons_pre tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
    </body>
</html>