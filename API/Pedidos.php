<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET["table"])) {
	exit ("Tabela não selecionada");
}

if (strlen($_GET["table"]) == 0) {
	exit ("Tabela não selecionada");
}

$table = $_GET["table"]; //"Pedidos";

if ($table != "Pedidos" && $table != "produtos" && $table != "Consumidores") {
    exit('não autorizado');
}

include "../config.php";
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
//$SQL = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$mysql_database."' AND TABLE_NAME='".$table."'";
$SQL = "SHOW COLUMNS FROM ".$table;
$st = $conn->prepare($SQL);
$st->execute();
$rsColumns=$st->fetchAll();
//$rsColumns = $conn->prepare->execute("PRAGMA table_info($table)");
$colCount=0;
echo '<html><head><meta charset="UTF-8"></head><body>';
echo '<table>';
echo '<tr>';
foreach ($rsColumns as $k) {
    if ($k['Field'] != 'unidade2' && $k['Field'] != 'multiplicador_unidade2' && $k['Field'] != 'carrinho') {
	    echo '<td>'.$k['Field'].'</td>';
	    $colCount++;
    }
}
echo '</tr>';

$SQL = "SELECT * FROM ".$table;//." ORDER BY id ASC";
$st = $conn->prepare($SQL);
$st->execute();
$rs=$st->fetchAll();

foreach ($rs as $row) {
	echo '<tr>';
	for ($i = 0; $i <= $colCount-1; $i++) {
		echo '<td>'.$row[$i].'</td>';
	}
	echo '</tr>';
}
echo '</table>';
echo '</body></html>';
?>