<?php
if (!isset($_GET)) exit();

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$uuid = $_SERVER['QUERY_STRING'];

$livres = new Livres();
$pedidos = new Pedidos();
$pix = new Pix();

$pixCopiaCola = $pix->showCopiaCola($uuid);
$pedido = $pedidos->pedidoPeloPix($uuid);

if ($pixCopiaCola && $pedido) {
    echo "Olá ".$pedido["consumidor"]."<br>";
    echo "Este é o código para pagamento da cesta do dia ".date("d/m/Y",strtotime($pedido["pedido_data"]))."<br>";
    echo "<b>Valor total: R$".number_format($pedido["pgt_valor_linkpix"],2,",",".")."</b><br>";
    echo $pix->PrintQRCode($pixCopiaCola);
    echo '<br>';
    echo "Você também pode copiar o código abaixo e colar no aplicativo de seu banco<br>";
    echo "<p>";
    echo '<textarea rows="4" cols="60">';
    echo $pixCopiaCola;
    echo '</textarea>';
    echo "</p>";
} else {
    echo "Link de pagamento/pedido não encontrado";
}
?>