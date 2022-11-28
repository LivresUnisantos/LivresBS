<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$oCons = new Consumidores();
$calendario = new Calendario();

if (isset($_POST["id"])) {
    $id = $_POST["id"];
    
    $consumidor = $_POST["consumidor"];
    $email = $_POST["email"];
    $endereco = $_POST["endereco"];
    $telefone = $_POST["telefone"];
    $ativo = $_POST["ativo"];
    $comunidade = $_POST["comunidade"];
    $nascimento = $_POST["nascimento"];
    $banco = $_POST["banco"];
    
    $telefone = str_replace("(", "", $telefone);
    $telefone = str_replace(")", "", $telefone);
    $telefone = str_replace("-", "", $telefone);
    $telefone = str_replace("+", "", $telefone);
    $telefone = str_replace(" ", "", $telefone);
    
    $dados = [
        "id"                            => $id,
        "consumidor"                    => $consumidor,
        "email"                         => $email,
        "endereco"                      => $endereco,
        "telefone"                      => $telefone,
        "ativo"                         => $ativo,
        "comunidade"                    => $comunidade,
        "nascimento"                    => $nascimento,
        "banco"                         => $banco
    ];

    /*echo "<pre>";
    print_r($dados);
    echo "</pre>";*/

    echo $oCons->atualizaConsumidor($dados);

} else {

    $oCons = new Consumidores;
    $livres = new Livres();
    $grupos = $livres->getParametro("grupos");
    $consumidor = $oCons->encontrarPorId($_GET["id"]);
    
    echo $twig->render('editar_consumidores_act.html', [
        "titulo"            => "LivresBS - Editar Produtos",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "level_user"        => $_SESSION["level"],
        "level_write"       => 15000,
        "consumidor"        => $consumidor,
        "grupos"            => $grupos
        ]);
}
?>