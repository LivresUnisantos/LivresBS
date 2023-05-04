<?php
$NivelAcess = LEVEL_CRIAR_CESTA;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    Erro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    die;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$wData[0] = "pedido_data = :start ";
$wData[1] = "start={$StartDate}";

$fallback = 'index.php?lbs=home';
$anterior = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback);

$RegistroId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($RegistroId):
    $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
            . "INNER JOIN " . DB_CONSUMIDORES . " AS b "
            . "ON a.consumidor_id = b.id "
            . "WHERE pedido_id = :id", "id={$RegistroId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS</b>, você tentou editar um registro que não existe ou que foi removido recentemente!", E_USER_NOTICE);
        header("Location: {$anterior}");
        exit;
    endif;
endif;
?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-pencil2">Adicionar Cesta <?= (isset($consumidor) ? 'de ' . $consumidor : ''); ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../">Painel</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../montagem/index.php?lbs=home">Montagem</a>
            <span class="crumb">/</span>
            Adicionar
        </p>
    </div>
</header>

<?php if (!$RegistroId): ?>
    <header class="dashboard_header">
        <form name="entrega" action="" method="post" class="auto_save" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Pedidos"/>
            <input type="hidden" name="callback_action" value="criarCesta"/>
            <input type="hidden" name="data" value="<?= $StartDate; ?>"/>
            <label style="max-width: 300px;display: block;">
                <span style="font-size: .9rem; margin-bottom: 8px;display: block;">Escolha o consumidor:</span>
                <?php
                $Read->FullRead("SELECT consumidor FROM " . DB_CONSUMIDORES . " AS a "
                        . "ORDER BY a.consumidor ASC");
                $Read->getResult();
                if ($Read->getResult()):
                    echo '<datalist id="Consumidores">';
                    foreach ($Read->getResult() as $Pdt):
                        echo "<option value='{$Pdt['consumidor']}'></option>";
                    endforeach;
                    echo '</datalist>';
                endif;
                ?>
                <input style="padding: 8px;" list="Consumidores" type="text" name="consumidor" placeholder="Escolha o consumidor" />
            </label>
            <i class="form_load none"></i>
        </form>
    </header>
    <?php
else:


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
        endif;
    endif;
    ?>
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

                        echo "<article class='item itemDel' id='{$item_id}'>
                            <div class='montagem_editar'>
                                <p class='coll' style='text-align:left'>" . str_replace('.00', '', number_format($item_qtde, 2, '.', '.')) . " x {$unidade}</p>
                                <p class='coll' style='min-width:30%;text-align:left'>{$item_produto} <span style='float:right; text-transform: uppercase;'>({$item_freq_cesta})</span></p>
                                <p class='coll'>". number_format($item_valor, 2, ',', '.') ." <span class='j_itemValor' style='float:right;'>(Total: R$ " . number_format($item_valor * $item_qtde, 2, ",", ".") . ")</span></p>
                            </div>
                        </article>";
                    endforeach;
                    echo "</article>";
                endforeach;
                ?>
            </div>
        </div>
    </div>

    <div class="box box100">
        <div class="panel">
            <div class='single_order j_item'>

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
    <?php
    $Read->FullRead("SELECT id, nome, unidade FROM " . DB_PRODUTO . " ORDER BY nome");
    $optPdt = "<option value=''>Selecione</option>";
    foreach ($Read->getResult() as $Pd):
        $optPdt .= "<option value='{$Pd['id']}'>{$Pd['nome']} [{$Pd['unidade']}]</option>";
    endforeach;
    ?>
    <script>
        $(function () {
        var i = 0;
        //ADICIONAR TAMANHO, COR E QUANTIDADE
        $('.j_add_campos_item').on('click', function () {
        i++;
        $('.j_item').append("<article class='item j_remove_item'><form name='montagem' action='' method='post' class='' enctype='multipart/form-data'><input type='hidden' name='callback' value='Pedidos'/><input type='hidden' name='pedido_id' value='<?= $pedido_id ?>' /><input type='hidden' name='callback_action' value='criarItem'/><input type='hidden' name='admin_id_editar' value='<?= $_SESSION["id"] ?>' /><p class='coll colItemNovo'>Produto: <select class='j_produto' name='item_produto'><?= $optPdt ?></select></p><p class='coll colItemNovo'>Quantidade:<input type='number' step='1' name='item_qtde' value='' /></p><p class='coll colItemNovo'>Valor: <select class='j_valor' name='item_valor'><option value=''>Selecione</option><option value='comunidade'>Preço de comunidade</option><option value='mercado'>Preço de mercado</option><option value='lojinha'>Preço da lojinha</option></select></p><p class='coll colItemNovo'>Frequência: <select name='item_freq_cesta'><option value='avulso'>Avulso</option></select></p><p class='coll colItemNovo' style='padding-top:32px;'><button  name='public' value='1' id='" + i + "' class='btn btn_green j_salvar_item'>Salvar item</button><img class='form_load none' style='margin-left: 10px;' alt='Enviando!' title='Enviando Requisição!' src='../_img/load.gif'/></p><p class='coll colItemNovo j_action' style='padding-top:32px;'><span class='j_del_item icon-cancel-circle btn btn_red' style='margin-left:10px;'>Excluir</span></p><i class='form_load none'></i></form></div>");
        $('.j_salvar_item').fadeIn();
        });
        //REMOVER CAMPOS DE TAMANHO, COR E QUANTIDADE
        $('html').on('click', '.j_del_item', function () {
        $(this).parents('.j_remove_item').fadeOut().remove();
        });
        });
    </script>
<?php endif; ?>