<?php
$NivelAcess = LEVEL_SUPER_ADMIN;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    Erro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    die;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
$Create = new Create;

$fallback = './';
$anterior = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback);

$wData[0] = "pedido_data = :start ";
$wData[1] = "start={$StartDate}";

$RegistroId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($RegistroId):
    $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
            . "WHERE a.pedido_id = :id", "id={$RegistroId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] =  AjaxErro("<b>OPPSS</b>, você tentou editar um registro que não existe ou que foi removido recentemente!", E_USER_WARNING);
        header("Location: {$anterior}");
        exit;
    endif;
else:
    if ($StartDate):
        $Read->FullRead("SELECT pedido_id "
                . "FROM " . DB_PD_CONS . " "
                . "WHERE {$wData[0]} AND pedido_endereco = :it", "{$wData[1]}&it=pre");
        if ($Read->getResult()):
            header('Location: index.php?lbs=pre&id=' . $Read->getResult()[0]['pedido_id']);
            exit;
        else:
            $PdCreate = ['pedido_data' => $StartDate, 'pedido_endereco' => 'pre'];
            $Create->ExeCreate(DB_PD_CONS, $PdCreate);
            header('Location: index.php?lbs=pre&id=' . $Create->getResult());
            exit;
        endif;
    else:
        $_SESSION['trigger_controll'] =  AjaxErro("<b>OPPSS</b>, você precisa selecionar uma data antes de cadastrar os produtos da Pré Comunidade!", E_USER_WARNING);
        header("Location: {$anterior}");
        exit;
    endif;
endif;
?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-spinner6">Pré Comunidade</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../">Painel</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../montagem/index.php?lbs=home">Montagem</a>
            <span class="crumb">/</span>
            Cadastrar os produtos da pré comunidade
        </p>
    </div>
</header>
<form name='montagem' action='' method='post' class='' enctype='multipart/form-data'>
    <div class='action_pre'><button  name='public' value='1' class='btn btn_green btn_medium j_salvar_item'>SALVAR PRODUTOS</button><img class='form_load none' style='margin-left: 10px;' alt='Enviando!' title='Enviando Requisição!' src='../_img/load.gif'/></div>
    <div class="box box100">
        <div class="panel">
            <input type="text" class="form-control" id="filtro" name="Filtro" placeholder="Filtre por nome, produtor ou número">
            <div class="single_order" style="margin-bottom: 100px;" id="tabela_produtos">
                <?php
                if ($RegistroId):

                    echo "<input type='hidden' name='callback' value='Pedidos'/>";
                    echo "<input type='hidden' name='callback_action' value='editarPre'/>";
                    echo "<input type='hidden' name='admin_id_editar' value='{$_SESSION["id"]}' />";
                    echo "<input type='hidden' name='pedido_id' value='{$pedido_id}' />";

                    $dia = explode(' ', $StartDate);
                    $Read->FullRead("SELECT id, nome, unidade, produtor FROM " . DB_PRODUTO . " ORDER BY nome");
                    $Pdts = $Read->getResult();
                    $i = 0;

                    foreach ($Pdts as $Pdt):
                        extract($Pdt);
                        $Read->FullRead("SELECT item_qtde FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND produto_id = :pid", "pi={$pedido_id}&pid={$id}");
                        $qtde = (!empty($Read->getResult()[0]['item_qtde']) ? str_replace('.00', '', number_format($Read->getResult()[0]['item_qtde'], 2, '.', '.')) : '');

                        $i++;
                        echo "<div class='item'>
                            <input type='hidden' name='produto_id[]' value='{$id}' />
                            <p class='box box15' style='width:5%;margin-top:20px;'><b>{$i})</b></p>
                            <p class='box box15'>Quantidade:<input type='decimal' name='item_qtde[]' value='$qtde' /></p>
                            <p class='box box50'>Produto: <br><span style='margin-top:6px; display:block'><b>{$nome}</b></span></p>
                            <p class='box box30'>Produtor: <br><span style='margin-top:6px; display:block'><b>{$produtor}</b></span></p>
                        </form>
                    </div>";
                    endforeach;
                    echo "";
                endif;
                ?>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</form>
<script>
    $("#filtro").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tabela_produtos .item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
</script>