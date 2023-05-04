<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
//956 - Contribuição Solidária Anual (G1/G3/G7)	
//957 - Contribuição Solidária Anual (G2/G8/G10)	
//958 - Contribuição Solidária Anual (G4/G6/G11)	
//959 - Contribuição Solidária Anual (G5/G9)
//sábado - 956
//terça - 958

$contrib[1] = 956;
$contrib[2] = 956;
$contrib[3] = 956;
$contrib[4] = 958;
$contrib[5] = 958;
$contrib[6] = 958;
$contrib[7] = 956;
$contrib[8] = 956;
$contrib[9] = 958;
$contrib[10] = 956;
$contrib[11] = 958;

$sql = "SELECT * FROM Consumidores WHERE comunidade <> 0 AND comunidade <> 12";
$st = $livres->conn()->prepare($sql);
$st->execute();

$rs = $st->fetchAll();
foreach ($rs as $row) {
    //echo $row["consumidor"]." - ".$row["comunidade"];
    $sqlU = "INSERT INTO Pedidos (IDConsumidor, IDProduto, Quantidade, Frequencia) VALUES (".$row["id"].",".$contrib[$row["comunidade"]].",1,'Mensal')";
    //echo $sqlU."<br>";
    
    $st = $livres->conn()->prepare($sqlU);
    if (!$st->execute()) {
        echo "Erro: ".$row["consumidor"]."<br>";
    }
}
?>