<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$token_match = "oFX1r63Az8RRyVbFBS69RKK96oIha0oj";

if (!isset($_POST["date"])) {
    echo json_encode(
        array('message' => 'Selecione uma data')
    );
    exit();
}

if (!isset($_POST["token"]) || $_POST["token"] != $token_match) {
    echo json_encode(
        array('message' => 'Token de autenticação inválido')
    );
    exit();
}

$dataStr = $_POST["date"];

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
require_once "../../includes/autoloader.inc.php";

$livres = new Livres();

$sql = "SELECT * FROM pedidos_consolidados ped LEFT JOIN Consumidores cons on ped.consumidor_id = cons.id ";
$sql .= " WHERE pedido_data = '".$dataStr."' AND ped.consumidor_id IS NOT NULL AND cons.consumidor NOT LIKE '%Vendas loja não consumidores%'";
$sql .= " ORDER BY cons.comunidade, cons.consumidor";
$st = $livres->conn()->prepare($sql);
$st->execute();

$entregas_arr = array();
$entregas_arr['data'] = array();

if ($st->rowCount() > 0) {
    $rs = $st->fetchAll();
	foreach ($rs as $row) {
        if ($row["pedido_retirada"] == 1) {
    			$delivery = "Não";
    	} else {
    		if ($row["pedido_retirada"] == 2) {
    			$delivery = "Sim";
    		} else {
    			$delivery = "";
    		}
    	}
    	
    	$entrega_item = array(
    	    'id_consumidor'         => $row["consumidor_id"],
    	    'nome_consumidor'       => ucwords(mb_strtolower($row["consumidor"]),'UTF-8'),
    	    'comunidade_consumidor' => $row["comunidade"],
    	    'telefone_consumidor'   => $row["telefone"],
    	    'endereco_entrega'      => $row["pedido_endereco"],
    	    'opcao_entrega'         => $delivery,
	        'valor_entrega'         => number_format($row["pedido_entrega_valor"],2,".","")
        );
        array_push($entregas_arr['data'], $entrega_item);
	}
	echo json_encode($entregas_arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
} else {
    echo json_encode(
        array('message' => 'Sem pedidos para esta data')
    );
}
/**/
/*
$database = new Database();
$db = $database->connect();

$produto = new Produto ($db);

if (isset($_GET["id"])) {
    $result = $produto->find($_GET["id"]);
} else {
    $result = $produto->read();
}
$num = $result->rowCount();

if ($num > 0) {
    $produto_arr = array();
    $produto_arr['data'] = array();
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $produto_item = array(
            'id_produto' => $id_produto,
            'id_produtor' => $id_produtor,
            'produto' => $produto,
            'categoria' => $categoria,
            'unidade' => $unidade,
            'produtor' => $produtor,
            'preco_produtor' => $preco_produtor,
            'preco_comboio' => $preco_comboio,
            'preco_comunidade' => $preco_comunidade,
            'preco_mercado' => $preco_mercado,
            'disponivel_desde' => $disponivel_desde,
            'disponivel_mensal' => $disponivel_mensal
        );
        array_push($produto_arr['data'], $produto_item);
    }
    echo json_encode($produto_arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(
        array('message' => 'Sem produtos')
    );
}
*/
?>