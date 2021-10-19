<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$oListas = new Listas();
$sucesso = "";
$erro = "";

//Lidar com formulários enviados
if (isset($_POST["act"])) {
    $act = $_POST["act"];
    switch($act) {
        case "editar_nome":
            $listaUpdate = $_POST["lista_selecionada"];
            $nomeUpdate = $_POST["nome_lista"];
            if ($listaUpdate != "" && $nomeUpdate != "") {
                if ($oListas->updateNomeLista($listaUpdate, $nomeUpdate)) {
                    $sucesso = "Nome atualizado";
                } else {
                    $erro = "Falha ao atualizar nome";
                }
            }
        break;
        case "criar_lista":
            $nomeInsert = $_POST["nome_lista"];
            if ($nomeInsert != "") {
                $exec = $oListas->createLista($nomeInsert);
                if ($exec != "") {
                    $idLista = "";
                    $sucesso = "Lista Criada. Selecione abaixo para prosseguir com edição";
                } else {
                    $erro = "Falha ao criar lista";
                }
            }
        break;
        case "apagar_lista":
            $listaUpdate = $_POST["lista_selecionada"];
            if ($listaUpdate != "") {
                if ($oListas->deleteLista($listaUpdate)) {
                    $sucesso = "Lista excluída";
                } else {
                    $erro = "Falha ao excluir lista. Verifique se existem grupos de consumidores avulsos associados à esta lista.";
                }
            }
        break;
    }
}

if (isset($_GET["lista_selecionada"]) && !isset($idLista)) {
    $idLista = $_GET["lista_selecionada"];
    $nomeLista = $oListas->getNomeLista($idLista);
    $produtos = $oListas->produtosListaTodos($idLista);
} else {
    $idLista = 0;
    $nomeLista = "";
    $produtos = "";
}

$listas = $oListas->listarListas();

//print_r($produtos);

echo $twig->render('editar_listas.html', [
    "titulo"            => "LivresBS - Listas de Produtos",
    "menu_datas"        => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
    "level_user"        => $_SESSION["level"],
    "level_write"       => 15000,
    "conteudo"          => $produtos,
    "listas"            => $listas,
    "id_lista"          => $idLista,
    "nome_lista"        => $nomeLista,
    "sucesso"            => $sucesso,
    "erro"              => $erro
    ]);
?>