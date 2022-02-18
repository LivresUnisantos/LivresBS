<?php
$levelRequired=1000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

$livres = new Livres();
$calendario = new Calendario();

$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);//

$filter = new \Twig\TwigFilter('stripslashes', 'stripslashes');
$twig->addFilter($filter);

$alerta = "";
$sucesso = "";
if (isset($_SESSION["alerta"])) { 
    $alerta = $_SESSION["alerta"];
    unset($_SESSION["alerta"]);
};
if (isset($_SESSION["sucesso"])) {
    $sucesso = $_SESSION["sucesso"];
    unset($_SESSION["sucesso"]);
};

/******
//Controle de permissão será feito por login individual para mais segurança
Permissões concedidas:
id = 1 Henderson | id = 5 Milena

//Caso haja permissão de aprovador, serão listadas as atividades de todas as pessoa
Para isso a criação do objeto da classe é diferente
****/
$permissao_aprovador = false;
if ($_SESSION["id"] == 1 ||$_SESSION["id"] == 3 ||$_SESSION["id"] == 5) {
    $permissao_aprovador = true;
    $oEcoholerite = new Ecoholerite();
} else {
    $oEcoholerite = new Ecoholerite($_SESSION["id"]);
}

//Tratar quando há GET (aprovação/reprovação/remoção de atividades)
/* act = aprovar/reprovar/remover */
if (isset($_GET["act"])) {
    $act = $_GET["act"];
    if ($act == 'aprovar_todos') {
        if ($oEcoholerite->aprovaAtividadeExecutada()) {
            $sucesso = "Todas as atividades pendentes foram aprovadas.";
            $_SESSION["sucesso"] = $sucesso;
        } else {
            $alerta = "Erro ao aprovar atividades";
            $_SESSION["alerta"] = $alerta;
        }
    } else {
        if ($act == 'aprovar_selecionados') {
            if (isset($_GET["ids"])) {
                foreach ($_GET["ids"] as $id) {
                    if (!$oEcoholerite->aprovaAtividadeExecutada($id)) {
                        $err[] = $id;
                    }
                }
                if (!isset($err)) {
                    $sucesso = "Tarefas selecionadas foram aprovadas.";
                    $_SESSION["sucesso"] = $sucesso;
                } else {
                    $alerta = "Falha ao aprovar as seguintes tarefas:<br>";
                    foreach ($err as $e) {
                        $alerta .= $e."<br>";
                    }
                    $alerta["sucesso"] = $alerta;
                }
            }
        } else {
            $id_atividade = $_GET["id_atividade"];
            $atividade = $oEcoholerite->atividadesExecutadas($id_atividade);
            if (!$atividade) {
                $alerta = "A atividade que você tentou ".$act." não existe";
                $_SESSION["alerta"] = $alerta;
            } else {
                switch($act) {
                    case "aprovar":
                        if ($oEcoholerite->aprovaAtividadeExecutada($id_atividade)) {
                            $sucesso = "Atividade código ".$id_atividade." aprovada";
                            $_SESSION["sucesso"] = $sucesso;
                        } else {
                            $alerta = "Erro ao aprovar atividade";
                            $_SESSION["alerta"] = $alerta;
                        }
                    break;
                    case "reprovar":
                        if ($oEcoholerite->reprovaAtividadeExecutada($id_atividade)) {
                            $sucesso = "Atividade código ".$id_atividade." reprovada";
                            $_SESSION["sucesso"] = $sucesso;
                        } else {
                            $alerta = "Erro ao reprovar atividade";
                            $_SESSION["alerta"] = $alerta;
                        }
                    break;
                    case "remover":
                        if ($oEcoholerite->removeAtividadeExecutada($id_atividade)) {
                            $sucesso = "Atividade código ".$id_atividade." ('".$atividade["descricao"]."' de '".$atividade["nome"]."' de '".date('d/m/Y',strtotime($atividade["data"]))."') removida";
                            $_SESSION["sucesso"] = $sucesso;
                        } else {
                            $alerta = "Erro ao remover atividade";
                            $_SESSION["alerta"] = $alerta;
                        }
                    break;
                }
            }
        }
    }
    exit();
}

//Tratar quando há POST (cadastro de novas atividades)
if (isset($_POST["add_salvar"])) {
    $erro = false;
    $campos = ['add_nome','add_data','add_atividade','add_ecohoras','add_valor','add_comentario'];
    //checa preenchimento
    foreach ($campos as $campo) {
        if (!isset($_POST[$campo])) {
            $alerta = "Não foi possível cadastrar atividade. Campo ".$campo." não preenchido";
            $erro=true;
        } else {
            $$campo = $_POST[$campo];
        }
    }
    //checa consistência da atividade/usuário selecionado
    if (!$erro) {
        $atividade = $oEcoholerite->listaAtividades($add_atividade);
        if (!$atividade) {
            $alerta = "Não foi possível cadastrar atividade. Atividade selecionada não encontrada.";
            $erro = true;
        }
        $pessoa = $oEcoholerite->listaPessoas($add_nome);
        if (!$pessoa) {
            $alerta = "Não foi possível cadastrar atividade. Nome selecionado não encontrado.";
            $erro = true;
        }
    }
    //checa consistência da ecohora/valor
    if (!$erro) {
        if ($atividade["ecohoras"] != 0 && ($atividade["ecohoras"] != $add_ecohoras || $atividade["valor"] != $add_valor)) {
            $alerta = "Não foi possível cadastrar atividade. Os dados de ecohora/valor não batem com os dados cadastrados.";
            $erro = true;
        }
    }
    //tudo certo, cadastrar
    if (!$erro) {
        if ($oEcoholerite->addAtividadeExecutada($add_nome, $add_atividade, $add_data, $add_ecohoras, $add_valor, $add_comentario)) {
            $sucesso = "Atividade cadastrada";
        } else {
            $alerta = "Erro ao cadastrar atividade. Tente novamente.";
        }
    }
    $_SESSION["alerta"] = $alerta;
    $_SESSION["sucesso"] = $sucesso;
    header("Location: ecoholerites.php");
}

$conteudo = $oEcoholerite->atividadesExecutadas();
$atividades = $oEcoholerite->listaAtividades();
$pessoas = $oEcoholerite->listaPessoas();

echo $twig->render('ecoholerites.html', [
    "titulo"                => "LivresBS - Ecoholerite",
    "alerta"                => $alerta,
    "sucesso"               => $sucesso,
    "admin_logado"          => $_SESSION["id"],
    "permissao_aprovador"   => $permissao_aprovador,
    "readonly"              => (!$permissao_aprovador) ? 'readonly="readonly"' : '', //variável auxiliar para bloquear campos que são editáveis apenas ao aprovador
    "disabled"              => (!$permissao_aprovador) ? 'disabled="disabled"' : '', //variável auxiliar para bloquear campos que são editáveis apenas ao aprovador
    "conteudo"              => $conteudo,
    "atividades"            => $atividades,
    "pessoas"               => $pessoas,
    "data_selecionada"      => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    ]);
    
?>