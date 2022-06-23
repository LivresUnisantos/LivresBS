<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/autoloader.inc.php";
require_once "acesso.php";

if (isset($_POST["id_admin"]) && isset($_POST["id_formacao"]) && isset($_POST["data"])) {
    $id_admin = $_POST["id_admin"];
    $id_formacao = $_POST["id_formacao"];
    $data = $_POST["data"];
    if ($id_admin != "" && $id_formacao != "" && $data != "") {
        $oRH = new RH($_SESSION["id"]);
        if (!$oRH->listarPessoas($id_admin)) {
            echo "Usuário não encotrado";
            http_response_code(500);
        }
        if (!$oRH->cadastraFormacao($id_admin, $id_formacao, $data)) {
            echo "Falha ao cadastrar";
            http_response_code(500);
        } else {
            http_response_code(200);
        }
    }
    
}
?>