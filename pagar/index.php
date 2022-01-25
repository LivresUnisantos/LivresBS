<html>
<head>
<title>Livres Coop - Pagamento</title>
</head>
<body>
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
    echo '<textarea id="copiacola" name="copiacola" rows="4" cols="60">';
    echo $pixCopiaCola;
    echo '</textarea>';
    echo '<p><button id="btn_copiacola" name="btn_copiacola">Copiar Código</button></p>';
    echo "</p>";
} else {
    echo "Link de pagamento/pedido não encontrado";
}
?>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $("#btn_copiacola").on("click", function() {
            $("#copiacola").select();
            document.execCommand("copy");
            alert("Código Copiado")
        });
    });
</script>
</html>