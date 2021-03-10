<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);

$oProdutos = new Produtos();
$categorias = $oProdutos->categorias();
$unidades = $oProdutos->unidades();

$datas = $calendario->montaArrayDatas();

$oProdutores = new Produtores();

$produtores = $oProdutores->listaProdutoresPorID();

if (isset($_GET["id"])) {
    $produto = $oProdutos->buscaProduto($_GET["id"]);
} else {
    $produto = $oProdutos->buscaProduto();
}

function trataPreco($p) {
    $p = str_replace("R","",$p);
    $p = str_replace("$","",$p);
    $p = str_replace(",",".",$p);
    return $p;
}

if (isset($_POST["nome"])) {
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
    } else {
        $id = 0;
    }
    
    $categoria = $_POST["categoria"];
    $unidade = $_POST["unidade"];
    $preco = $_POST["preco"];;
    $previsao = $_POST["previsao"];
    $produtor = $_POST["produtor"];
    $preco_produtor = $_POST["preco_produtor"];
    $preco_mercado = $_POST["preco_mercado"];
    $preco_lojinha = $_POST["preco_lojinha"];
    $preco_pre = $_POST["preco_pre"];
    $estoque = $_POST["estoque"];
    
    $preco = str_replace(",",".",$preco);
    $preco = str_replace("R$","",$preco);
    
    $preco_produtor = str_replace(",",".",$preco_produtor);
    $preco_produtor = str_replace("R$","",$preco_produtor);
    
    $preco_mercado = str_replace(",",".",$preco_mercado);
    $preco_mercado = str_replace("R$","",$preco_mercado);
    
    $preco_lojinha = str_replace(",",".",$preco_lojinha);
    $preco_lojinha = str_replace("R$","",$preco_lojinha);
    
    $preco_pre = str_replace(",",".",$preco_pre);
    $preco_pre = str_replace("R$","",$preco_pre);

    $nome = $_POST["nome"];
    $unidade2 = $_POST["unidade2"];
    $multiplicador_unidade2 = $_POST["multiplicador_unidade2"];
    
    if (isset($_POST["mensal"])) {
        $mensal = true;
    } else {
        $mensal = false;
    }
    if (isset($_POST["carrinho"])) {
        $carrinho = true;
    } else {
        $carrinho = false;
    }
    
    $dados = [
        "id"                        => $id,
        "categoria"                 => $categoria,
        "unidade"                   => $unidade,
        "preco"                     => $preco,
        "previsao"                  => $previsao,
        "produtor"                  => $produtor,
        "preco_produtor"            => $preco_produtor,
        "preco_mercado"             => $preco_mercado,
        "preco_lojinha"             => $preco_lojinha,
        "preco_pre"                 => $preco_pre,
        "mensal"                    => $mensal,
        "carrinho"                  => $carrinho,
        "estoque"                   => $estoque,   
        "nome"                      => $nome,
        "unidade2"                  => $unidade2,
        "multiplicador_unidade2"    => $multiplicador_unidade2
    ];

    /*echo "<pre>";
    print_r($dados);
    echo "</pre>";*/

    //PENDENTE: executar alteração no BD
    if ($id == 0) {
        echo $oProdutos->cadastraProduto($dados);
    } else {
        echo $oProdutos->atualizarProduto($dados);
    }
} else {

    echo $twig->render('editar_produtos_act.html', [
        "titulo"            => "LivresBS - Editar Produtos",
        "menu_datas"        => $calendario->listaDatas(),
        "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
        "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
        "level_user"        => $_SESSION["level"],
        "level_write"       => 15000,
        "produto"           => $produto,
        "categorias"        => $categorias,
        "unidades"          => $unidades,
        "datas"             => $datas,
        "produtores"        => $produtores
        ]);
}
?>