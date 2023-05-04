<?php
$NivelAcess = LEVEL_EDITAR_CESTA;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    Erro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    die;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$fallback = 'index.php?lbs=home';
$anterior = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback);

$RegistroId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($RegistroId):
    $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
            . "INNER JOIN " . DB_CONSUMIDORES . " AS b "
            . "ON a.consumidor_id = b.id "
            . "WHERE pedido_id = :id", "id={$RegistroId}");
    if ($Read->getResult()):
        $Pedidos = $Read->getResult();
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS</b>, você tentou editar um registro que não existe ou que foi removido recentemente!", E_USER_NOTICE);
        header("Location: {$anterior}");
        exit;
    endif;
else:
    header("Location: {$anterior}");
    exit;
endif;
?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-pencil2">Editar Cesta de <?= $consumidor; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../">Painel</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../montagem/index.php?lbs=home">Montagem</a>
            <span class="crumb">/</span>
            Editar
        </p>
    </div>
</header>

<header class="dashboard_header">
    <form name="entrega" action="" method="post" class="auto_save" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Pedidos"/>
        <input type="hidden" name="callback_action" value="editarEntrega"/>
        <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>" />
        <label style="max-width: 300px;display: block;">
            <span style="font-size: .9rem; margin-bottom: 8px;display: block;">Forma de entrega da Cesta:</span>
            <select name="pedido_retirada">
                <?php
                $Read->ExeRead(DB_ENTREGA);
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $retirada):
                        echo "<option value='{$retirada['id']}' " . ($pedido_retirada == $retirada['id'] ? 'selected="selected"' : '') . ">{$retirada['descricao_entrega']}</option>";
                    endforeach;
                endif;
                ?>
            </select>
        </label>
        <i class="form_load none"></i>
    </form>
</header>

<div class="box box100">
    <div class="panel">
        <div class='single_order j_item'>
            <?php
            foreach ($Pedidos as $Itens):
                extract($Itens);
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                        . "LEFT JOIN " . DB_PD_CONS . " AS b "
                        . "ON a.pedido_id = b.pedido_id "
                        . "LEFT JOIN " . DB_PRODUTO . " AS d "
                        . "ON a.produto_id = d.id "
                        . "LEFT JOIN " . DB_UNIDADE . " AS e "
                        . "ON a.item_tipo = e.id "
                        . "WHERE a.pedido_id = :pi "
//                        . "GROUP BY a.item_produto "
                        . "ORDER BY a.item_produto, a.item_id", "pi={$pedido_id}");
                foreach ($Read->getResult() as $PedIens):
                    extract($PedIens);

                    $Read->FullRead("SELECT preco, preco_mercado, preco_lojinha FROM " . DB_PRODUTO . " WHERE nome = :id", "id={$item_produto}");
                    if ($Read->getResult()):
                        $PrecoPdt = $Read->getResult()[0];
                        $optionsPdt = "<option value='comunidade' " . ($item_valor == $PrecoPdt['preco'] ? 'selected="selected"' : '') . ">(R$ " . number_format($PrecoPdt['preco'], 2, ",", ".") . ") Comboio agroecológico</option>"
                                . "<option value='mercado' " . ($item_valor == $PrecoPdt['preco_mercado'] ? 'selected="selected"' : '') . ">(R$ " . number_format($PrecoPdt['preco_mercado'], 2, ",", ".") . ") Consumo consciente</option>"
                                . "<option value='lojinha' " . ($item_valor == $PrecoPdt['preco_lojinha'] ? 'selected="selected"' : '') . ">(R$ " . number_format($PrecoPdt['preco_lojinha'], 2, ",", ".") . ") Livre Mercado</option>"
                                . "<option value='cortesia' " . ($item_valor == 0 ? 'selected="selected"' : '') . ">(R$ 0,00) Preço Cortesia</option>";
                    endif;

                    echo "<article class='item itemDel' id='{$item_id}'><form name='montagem' action='' method='post' class='auto_save' enctype='multipart/form-data'>
                            <input type='hidden' name='callback' value='Pedidos'/>
                            <input type='hidden' name='callback_action' value='editarPedido'/>
                            <input type='hidden' name='item_id' value='{$item_id}' />
                            <input type='hidden' name='item_produto' value='{$item_produto}' />
                            <input type='hidden' name='unidade' value='{$unidade}' />
                            <input type='hidden' name='admin_id_editar' value='{$_SESSION["id"]}' />

                            <div class='montagem_editar'>
                                <p class='coll' style='text-align:left'><input class='inputOld' name='item_qtde' type='decimal' value='" . str_replace('.00', '', number_format($item_qtde, 2, '.', '.')) . "' /> x {$unidade}</p>
                                <p class='coll' style='min-width:30%;text-align:left'>{$item_produto} <span style='float:right; text-transform: uppercase;'>({$item_freq_cesta})</span></p>
                                <p class='coll'><select name='item_valor' style='max-width:200px;float:left;'>{$optionsPdt}</select> <span class='j_itemValor' style='float:right;'>(Total: R$ " . number_format($item_valor * $item_qtde, 2, ",", ".") . ")</span></p>
                                <p class='coll' style='max-width:150px;'>"
                    . "<span rel='itemDel' class='j_delete_action icon-cancel-circle btn btn_red' id='{$item_id}'>Excluir</span>"
                    . "<span rel='itemDel' callback='Pedidos' callback_action='deleteItem' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$item_id}'>Remover Item?</span></p>
                            </div>
                        <i class='form_load none'></i></form></article>";
                endforeach;

                echo "</article>";
            endforeach;
            $Read->FullRead("SELECT id, nome, unidade, produtor FROM " . DB_PRODUTO . " WHERE soft_delete = 0 ORDER BY nome");
            $optPdt = "<option value=''>Selecione</option>";
            foreach ($Read->getResult() as $Pd):
                $optPdt .= "<option value='{$Pd['id']}'>{$Pd['nome']} [{$Pd['unidade']}] - {$Pd['produtor']}</option>";
            endforeach;
            ?>
            <div class='' style="padding: 20px; text-align: center; background-color: #1aa4db;color: #fff;">
                <h3 style="color:#fff;">Adicionar mais itens nesta cesta:</h3><br>
                <span>Esses dados da cesta de <?= $consumidor ?> servem <b>apenas para o dia <?= date('d/m/Y', strtotime($_SESSION['data_consulta'])) ?></b></span>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
    <div style="margin: 40px 0; text-align: center;">
        <span class="btn btn_yellow j_add_campos_item icon-plus">Adicionar mais um item</span>
    </div>
</div>
<script>
    $(function () {
    var i = 0;
    //ADICIONAR TAMANHO, COR E QUANTIDADE
    $('.j_add_campos_item').on('click', function () {
        i++;
        $('.j_item').append("<form name='montagem' action='' method='post' class='' enctype='multipart/form-data'><article class='item j_remove_item'><input type='hidden' name='callback' value='Pedidos'/><input type='hidden' name='pedido_id' value='<?= $pedido_id ?>'/><input type='hidden' name='callback_action' value='criarItem'/><input type='hidden' name='admin_id_editar' value='<?= $_SESSION["id"] ?>'/><p class='coll colItemNovo'>Produto:<select class='j_produto' name='item_produto'><?= $optPdt ?></select></p><p class='coll colItemNovo'>Quantidade:<input type='decimal' name='item_qtde' value=''/></p><p class='coll colItemNovo'>Valor:<select class='j_valor' name='item_valor'><option value=''>Selecione</option><option value='comunidade'>Comboio Agroecológico</option><option value='mercado'>Consumo Consciente</option><option value='lojinha'>Livre Mercado</option><option value='cortesia'>Preço cortesia</option></select></p><p class='coll colItemNovo'>Valor pago ao produtor:<select class='j_valor_produtor' name='item_valor_produtor'><option value=''>Selecione</option><option value='tradicional'>Preço tradicional</option><option value='cortesia'>(R$ 0,00) Preço cortesia</option></select></p><p class='coll colItemNovo'>Frequência:<select name='item_freq_cesta'><option value=''>Selecione</option><option value='semanal'>Semanal</option><option value='qinzenal'>Quinzenal</option><option value='mensal'>Mensal</option><option value='variavel'>Variável</option><option value='avulso'>Avulso</option></select></p><p class='coll colItemNovo' style='padding-top:32px;width:auto !important'><button name='public' value='1' id='" + i + "' class='btn btn_green j_salvar_item'>Salvar item</button></p><p class='coll colItemNovo j_action' style='padding-top:32px;width:auto !important'><span class='j_del_item icon-cancel-circle btn btn_red' style='margin-left:10px;'>Excluir</span></p><i class='form_load none'></i></article></form>");
        $('.j_salvar_item').fadeIn();
    });
        //REMOVER CAMPOS DE TAMANHO, COR E QUANTIDADE
        $('html').on('click', '.j_del_item', function () {
            $(this).parents('.j_remove_item').fadeOut().remove();
        });
    });
</script>