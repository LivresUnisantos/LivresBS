<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
setlocale(LC_TIME, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil');

require_once "acesso.php";
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/index');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$oProdutos = new Produtos();
$oListas = new Listas();
$oCons = new Consumidores();

$cons = $oCons->encontrarPorId($_SESSION["id_consumidor"]);
$categorias = $oProdutos->categorias();
$produtos_extra = $oListas->produtosListaAtivos(5);
$proximoPedido = $oCons->proximoPedidoConsumidor($_SESSION["id_consumidor"], date('Y-m-d'));
$proximoPedidoVariavel = $oCons->proximoPedidoVariavelConsumidor($_SESSION["id_consumidor"], date('Y-m-d'));

$proximaEntrega = $proximoPedido[0]["pedido_data"];

$produtos_promo = $oListas->listarVariaveis($proximaEntrega);

if (!$produtos_promo || count($produtos_promo) == 0) {
    $erros[] = "Ainda não foram disponibilizados produtos para preenchimento";
    $livres->setLog("log.txt","CPF: ".$cpf." - tentativa de preenchimento sem produtos cadastrados","");
}

if ($proximoPedidoVariavel !== false) {
    $erros[] = "Você já realizou seu pedido de variável para essa semana. Caso deseje alterar, entre em contato diretamente com o Livres.";
    foreach ($proximoPedidoVariavel as $item) {
        $erros[] = $item["item_qtde"]." x ".$item["item_produto"]." - R$".number_format($item["item_valor"], 2, ",", ".");
    }
    $livres->setLog("log.txt","CPF: ".$cons["cpf"]." - Logou para realizar pedido, mas pedido já havia sido realizado.","");
}

$oVariaveis = new Variaveis($proximaEntrega);
$pedidoAberto = $oVariaveis->pedidoAberto($cons);
if ($pedidoAberto !== true) {
    $erros[] = $pedidoAberto;
} else {
    if (isset($_POST["pedido_final"])) {
        $pedido = $_POST["pedido_final"];
        $pedido = json_decode($pedido, true);
        $oConsolidar = new ConsolidarPedidos($proximaEntrega);
        $atualizar_endereco = isset($_POST["atualizar_endereco"]);
        $salvar = $oConsolidar->cadastrarVariavel($proximoPedido[0], $pedido, $_POST["delivery"], $_POST["endereco_entrega"], $atualizar_endereco);
        if ($salvar === true) {
            $sucesso = "Pedido realizado";
        } else {
            $erros[] = "Erro ao cadastrar pedido";
            
        }
    }
}

echo $twig->render('variavel.html', [
    "produtos_promo"        => $produtos_promo,
    "produtos_extra"        => $produtos_extra,
    "cons"                  => $cons,
    "pedido"                => $proximoPedido[0],
    "sucesso"               => (isset($sucesso)) ? $sucesso : false,
    "alerta"                 => (isset($erros)) ? implode('<br>',$erros) : false
    ]);
?>