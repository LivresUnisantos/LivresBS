<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Produtor.php';//

$database = new Database();
$db = $database->connect();

$produtor = new Produtor ($db);

$result = $produtor->read();
$num = $result->rowCount();

if ($num > 0) {
    $produtor_arr = array();
    $produtor_arr['data'] = array();
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $produtor_item = array(
            'id_produtor' => $id,
            'produtor' => $produtor,
        );
        array_push($produtor_arr['data'], $produtor_item);
    }
    echo json_encode($produtor_arr);
} else {
    echo json_encode(
        array('message' => 'Sem produtores')
    );
}
?>