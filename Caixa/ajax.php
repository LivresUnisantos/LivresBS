<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../Painel/acesso.php";
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "../Painel/helpers.php";

$livres = new Livres();
$conn = $livres->conn();
$caixa = new Caixa();

if (isset($_POST["act"])) {
    $act = $_POST["act"];
    switch ($act) {
        case "listaTransacoes":
            if (isset($_POST["id_caixa"])) {
                if ($caixa->caixaExiste($_POST["id_caixa"])) {
                    http_response_code(200);
                    echo $caixa->listaTransacoes($_POST["id_caixa"]);
                } else {
                    http_response_code(400);
                    echo "Caixa não encontrado";
                }
            } else {
                http_response_code(400);
                echo "Caixa não selecionado";
            }
        break;
        case "cadastraTransacao":
            if ($msg = $caixa->cadastraTransacao($_POST["id_caixa"], $_POST["descricao"],$_POST["valor"],$_POST["forma_pagamento"])) {
                http_response_code(200);
            } else {
                http_response_code(400);
                echo "Falha ao criar transacao";
            }
        break;
        case "apagaTransacao":
            if ($msg = $caixa->apagaTransacao($_POST["id_transacao"])) {
                http_response_code(200);
                echo "Transacao Apagada";
            } else {
                http_response_code(400);
                echo "Falha ao apagar transacao";
            }
        break;
        case "relatorioCaixa":
            
            if ($rel = $caixa->relatorioCaixa($_POST["id_caixa"])) {
                http_response_code(200);
                echo $rel;
            } else {
                http_response_code(400);
                echo "Falha ao gerar relatório";
            }
        break;
    }
}

?>