<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Produto.php';//

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
?>