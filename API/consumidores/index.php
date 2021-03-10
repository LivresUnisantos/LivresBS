<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Consumidor.php';//

$database = new Database();
$db = $database->connect();

$consumidor = new Consumidor ($db);

$result = $consumidor->read();
$num = $result->rowCount();

if ($num > 0) {
    $consumidor_arr = array();
    $consumidor_arr['data'] = array();
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $consumidor_item = array(
            'id_consumidor' => $id,
            'consumidor' => $consumidor,
            'email' => $email,
            'cpf' => $cpf,
            'endereco' => $endereco,
            'telefone' => $telefone,
            'cota' => $cota,
            'data_criacao' => $data_criacao
        );
        array_push($consumidor_arr['data'], $consumidor_item);
    }
    echo json_encode($consumidor_arr);
} else {
    echo json_encode(
        array('message' => 'Sem consumidores')
    );
}
?>