<?php
$levelRequired=10000;
include "../config.php";
include "helpers.php";
include "acesso.php";

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);
/*
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$counter=0;
$sql = "SELECT * FROM Consumidores WHERE ativo = 1 ORDER BY Consumidor ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
foreach ($rs as $row) {
*/
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
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
        <script src="../js/vendor/jquery.js"></script>
        <script>
	    $(document).ready(function() {
	        $(":checkbox").change(function() {
	            dia = $(this).attr('dia')
	            grupo = $(this).attr('grupo');
	            idCalendario = $(this).attr('idCalendario');
	            if ($("[dia='semanal'][grupo='"+grupo+"'][idCalendario="+idCalendario+"]").is(":checked")) {
	                semanal=1;
	            } else {
	                semanal=0;
	            }
	            if ($("[dia='quinzenal'][grupo='"+grupo+"'][idCalendario="+idCalendario+"]").is(":checked")) {
	                quinzenal=1;
	            } else {
	                quinzenal=0;
	            }
	            if ($("[dia='mensal'][grupo='"+grupo+"'][idCalendario="+idCalendario+"]").is(":checked")) {
	                mensal=1;
	            } else {
	                mensal=0;
	            }
	            frequencia=semanal+''+quinzenal+''+mensal;
	            $.ajax({
                    method: "POST",
                    url: "calendario_act.php",
                    data: {
                        idCalendario: idCalendario,
                        grupo: grupo,
                        frequencia: frequencia,
                        act: "atualiza_calendario"
                    }
                })
                .done(function(msg) {
                    if (msg.length > 0) {
                        alert(msg);
                    }
                });
	        });
	    });
        </script>
    </head>
    <body>
<?php
echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);

$diasSemana[1] = "Seg";
$diasSemana[2] = "Ter";
$diasSemana[3] = "Qua";
$diasSemana[4] = "Qui";
$diasSemana[5] = "Sex";
$diasSemana[6] = "Sab";
$diasSemana[7] = "Dom";
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
$nGrupos=getTotalGrupos($conn);
$sql = "SELECT * FROM Calendario WHERE data > '".date("Y-m-d",strtotime((date("Y")-1)."-12-01"))."' ORDER BY data ASC";
echo $sql;
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
echo 'Ordem: semanal/quinzenal/mensal';
echo '<table>';
echo '<tr class="firstLine">';
echo '<td>id</td>';
echo '<td>Data</td>';
echo '<td>Dia</td>';
for ($i = 1; $i <= $nGrupos; $i++) {
    echo '<td>Grupo '.$i.'</td>';
}
echo '</tr>';
$count=0;
foreach ($rs as $row) {
    $count++;
    if ($count % 2 == 0) {
        echo '<tr bgcolor="#d1f1ff">';
    } else {
        echo '<tr>';
    }
    echo '<td>'.$row["id"].'</td>';
    echo '<td>'.date("d/m/Y",strtotime($row["data"])).'</td>';
    echo '<td>'.$diasSemana[date("N",strtotime($row["data"]))].'</td>';
    for ($i = 1; $i <= $nGrupos; $i++) {
        echo '<td>';
        //Semanal
        echo '<input type="checkbox" id="semanal_'.$i.'_'.$row["id"].'" name="semanal_'.$i.'_'.$row["id"].'" dia="semanal" grupo="'.$i.'" idCalendario="'.$row["id"].'" value="1" ';
        if (getFreq($row[$i."acomunidade"],"s")) {
            echo "checked";
        }
        echo '/>';
        //Quinzenal
        echo '<input type="checkbox" id="quinzenal_'.$i.'_'.$row["id"].'" name="quinzenal_'.$i.'_'.$row["id"].'" dia="quinzenal" grupo="'.$i.'" idCalendario="'.$row["id"].'" value="1" ';
        if (getFreq($row[$i."acomunidade"],"q")) {
            echo "checked";
        }
        echo '/>';
        //Mensal
        echo '<input type="checkbox" id="mensal_'.$i.'_'.$row["id"].'" name="mensal_'.$i.'_'.$row["id"].'" dia="mensal" grupo="'.$i.'" idCalendario="'.$row["id"].'" value="1" ';
        if (getFreq($row[$i."acomunidade"],"m")) {
            echo "checked";
        }
        echo '/>';
        echo '</td>';
    }
    echo "</tr>";
}
echo '</table>';
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
    </body>
</html>