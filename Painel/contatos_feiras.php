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
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
$sql = "SELECT * FROM ContatosFeiras ORDER BY data_criacao ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
echo '<table>';
echo '<tr class="firstLine">';//
echo '<td>Nome</td>';
echo '<td>Email</td>';
echo '<td>Endere&ccedil;o</td>';
echo '<td>CPF</td>';
echo '<td>Telefone</td>';
echo '<td>Data Cadastro</td>';
echo '</tr>';
$count=0;
foreach ($rs as $row) {
    $count++;
    if ($count % 2 == 0) {
        echo '<tr bgcolor="#d1f1ff">';
    } else {
        echo '<tr>';
    }
    echo '<td>'.$row["consumidor"].'</td>';
    echo '<td>'.$row["email"].'</td>';
    echo '<td>'.$row["endereco"].'</td>';
    echo '<td>'.$row["cpf"].'</td>';
    echo '<td>'.$row["telefone"].'</td>';
    echo '<td>'.date("d/m/Y",strtotime($row["data_criacao"])).'</td>';
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