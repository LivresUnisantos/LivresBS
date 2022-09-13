<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$levelRequired=21000;
include "../config.php";
include "acesso.php";
include "helpers.php";

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
		.receita {
		    color: #30b053;
		}
		.despesa {
		    color:#ff0000;
		}
	</style>
	<title>Livres - Comboio Orgânico</title>
      </head>
  <body>
<?php

if (isset($_SESSION["data_id"]) && $dataStr = $livres->dataPeloID($_SESSION["data_id"])) {
    $frequencia_semana = $calendario->montaDisplayFrequenciaSemana(strtotime($dataStr));
} else {
    $frequencia_semana = "";    
}

echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
	"data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
	"frequencia_semana" => $frequencia_semana,
]);

//echo "Página em atualização para que sejam exibidos dados conforme pedido consolidado implementado em 12/06/2020";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
if (!isset($_SESSION["data_consulta"])) {
	echo 'Selecione uma data de entrega';
} else {
	if (strlen($_SESSION["data_consulta"]) > 0) {		
		$oProdutos = new Produtos();
		$produtos = $oProdutos->listarProdutosTodos();

		$oProdutor = new Produtores();
		$produtores = $oProdutor->listaProdutoresPorID();

		$oPedidos = new PedidosConsolidados($_SESSION["data_consulta"]);
		$pedidos = $oPedidos->listaPedidos();
		$pedidoPre = $oPedidos->valorPedidoPre();

		$itens = $oPedidos->listaItensTodos();

		// $totais["receitas"]["total"] = 0;
		// $totais["despesas"]["total"] = 0;
        
		foreach ($itens as $item) {
			$produto = $produtos[$item["produto_id"]];
			$produtor = $produtores[$item["item_produtor"]];

			if (!isset($totais) || !is_array($totais) || !array_key_exists($produtor,$totais["despesas"])
					|| !array_key_exists($item["item_tipo_cesta"],$totais["despesas"][$produtor])) {
				$totais["despesas"][$produtor][$item["item_tipo_cesta"]] = 0;
			}
			if ($item["produto_id"] == 136 || $item["produto_id"] == 137 || $item["produto_id"] == 484 || $item["produto_id"] == 410 || $item["produto_id"] == 411 || $item["produto_id"] == 711 || $item["produto_id"] == 736) {
				if (!isset($totais) || !is_array($totais) || !array_key_exists("receitas", $totais) || !array_key_exists("contribuicao", $totais["receitas"])) {
					$totais["receitas"]["contribuicao"] = 0;					
				}
				$totais["receitas"]["contribuicao"] += $item["item_qtde"]*$item["item_valor"];
			} else {
				$totais["despesas"][$produtor][$item["item_tipo_cesta"]] += $item["item_qtde"]*$item["item_valor_produtor"];
			}
		}
		if (!isset($totais) || !is_array($totais) || !array_key_exists("receitas", $totais) || !array_key_exists("contribuicao", $totais["receitas"])) {
			$totais["receitas"]["contribuicao"] = 0;					
		}

		foreach ($pedidos as $pedido) {
			if (!isset($totais) || !is_array($totais) || !array_key_exists("receitas",$totais) || !array_key_exists("pedidos",$totais["receitas"])) {
				$totais["receitas"]["pedidos"] = 0;
				$totais["receitas"]["cota"] = 0;
				$totais["receitas"]["fixa"] = 0;
				$totais["receitas"]["variavel"] = 0;
				$totais["receitas"]["avulso"] = 0;
				$totais["receitas"]["pre"] = 0;
			}
			$totais["receitas"]["pedidos"] += $pedido["pedido_valor_total"];

			$totais["receitas"]["cota"] += $pedido["pedido_cota"];
			$totais["receitas"]["fixa"] += $pedido["pedido_fixa"];
			$totais["receitas"]["variavel"] += ($pedido["pedido_variavel"] - ($pedido["pedido_cota"] - $pedido["pedido_fixa"]) > 0.5 ) ? $pedido["pedido_variavel"]: $pedido["pedido_cota"] - $pedido["pedido_fixa"];
			$totais["receitas"]["avulso"] += $pedido["pedido_avulso"];
		}
		$totais["receitas"]["pre"] = $pedidoPre;

		// echo '<pre>';
		// print_r($totais);
		// echo '</pre>';

		$totais["totalReceitas"] = 0;
		$totais["totalDespesas"] = 0;
		echo '<div class="container">';
		echo '<p><span>Exibindo resumo de contabilidade para entrega de '.date('d/m/Y',strtotime($_SESSION["data_consulta"])).'</span></p>';

		/************ */
		//Despesas
		$tipoCestas = array("fixa", "variavel", "avulso", "pre");
		foreach($tipoCestas as $tipoCesta) {
			$subtotais[$tipoCesta] = 0;
		}
		echo '<table>';
		echo '<tr>';
		echo '<td>Produtor</td>';
		echo '<td>Fixo</td>';
		echo '<td>Variável</td>';
		echo '<td>Avulso</td>';
		echo '<td>Pré</td>';
		echo '<td>Total</td>';
		echo '</tr>';
		foreach ($totais["despesas"] as $produtor => $despesaProdutor) {
			foreach($tipoCestas as $tipoCesta) {
				if (array_key_exists($tipoCesta,$despesaProdutor)) {
					$$tipoCesta = $despesaProdutor[$tipoCesta];
					$subtotais[$tipoCesta] += $despesaProdutor[$tipoCesta];
				} else {
					$$tipoCesta = 0;
				}
			}
			echo '<tr>';
			echo '<td>'.$produtor.'</td>';
			echo '<td class="despesa">R$'.number_format($fixa,2,',','.').'</td>';
			echo '<td class="despesa">R$'.number_format($variavel,2,',','.').'</td>';
			echo '<td class="despesa">R$'.number_format($avulso,2,',','.').'</td>';
			echo '<td class="despesa">R$'.number_format($pre,2,',','.').'</td>';
			echo '<td class="despesa">R$'.number_format(($fixa+$variavel+$avulso+$pre),2,',','.').'</td>';
			echo '</tr>';
		}
		echo '<tr><td colspan="'.(count($tipoCestas)+2).'">&nbsp;</td></tr>';
		echo '<tr>';
		echo '<td>Despesas</td>';
		$total = 0;
		foreach($tipoCestas as $tipoCesta) {
			if (array_key_exists($tipoCesta, $subtotais)) {
				echo '<td class="despesa">R$'.number_format($subtotais[$tipoCesta],2,",",".").'</td>';
				$total += $subtotais[$tipoCesta];
			}
		}
		$despesas = $total;
		echo '<td class="despesa">'.number_format($total,2,",",".").'</td>';
		echo '</tr>';
		echo '</table>';

		echo '<hr>';
		//Receitas
		echo '<table>';
		echo '<tr>';
		echo '<td>&nbsp;</td>';
		echo '<td>Fixo</td>';
		echo '<td>Variável</td>';
		echo '<td>Avulso</td>';
		echo '<td>Pré</td>';
		echo '<td>Total</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Cestas</td>';
		$total = 0;
		foreach($tipoCestas as $tipoCesta) {
			if (array_key_exists($tipoCesta,$totais["receitas"])) {
				$valor = $totais["receitas"][$tipoCesta];
			} else {
				$valor = 0;
			}
			echo '<td class="receita">R$'.number_format($valor,2,',','.').'</td>';
			$total += $valor;
		}
		echo '<td class="receita">R$'.number_format($total,2,',','.').'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Contribuição</td>';
		echo '<td class="receita">R$'.number_format($totais["receitas"]["contribuicao"],2,',','.').'</td>';
		echo '<td class="receita">R$0,00</td>';
		echo '<td class="receita">R$0,00</td>';
		echo '<td class="receita">R$0,00</td>';
		echo '<td class="receita">R$'.number_format($totais["receitas"]["contribuicao"],2,',','.').'</td>';
		echo '</tr>';
		echo '<tr><td colspan="'.(count($tipoCestas)+2).'">&nbsp;</td></tr>';

		echo '<tr>';
		echo '<td>Receitas</td>';
		$total = 0;
		foreach($tipoCestas as $tipoCesta) {
			if (array_key_exists($tipoCesta,$totais["receitas"])) {
				$valor = $totais["receitas"][$tipoCesta];
			} else {
				$valor = 0;
			}
			if ($tipoCesta == 'fixa') {
				$valor += $totais["receitas"]["contribuicao"];
			}
			echo '<td class="receita">R$'.number_format($valor,2,',','.').'</td>';
			$total += $valor;
		}
		$receitas = $total;
		echo '<td class="receita">R$'.number_format($total,2,',','.').'</td>';
		echo '</tr>';
		echo '</table>';

		echo '<hr>';

		echo '<table>';
		echo '<tr>';
		echo '<td>Total Receitas</td>';
		echo '<td class="receita">R$'.number_format($receitas,2,',','.').'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Total Despesas</td>';
		echo '<td class="despesa">R$'.number_format($despesas,2,',','.').'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Saldo Caixa</td>';
		echo '<td class="'.(($receitas-$despesas >= 0) ? "receita" : "despesa").'">R$'.number_format($receitas-$despesas,2,',','.').'</td>';
		echo '</tr>';
		echo '</table>';

		echo '<hr>';		
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