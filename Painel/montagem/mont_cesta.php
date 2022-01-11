<?php
$bc = ($banco == true ? '*' : '');
$cesta_montagem = "<article class='single_order cestaDel' id='{$pedido_id}'>
    <header>
        <h1 style='display:inline-block;'>{$i}) {$consumidor}{$bc} (G{$comunidade})</h1>";
if ($_SESSION["level"] >= LEVEL_BIKERS):
    $cesta_montagem .= "<span class='icon-shrink2 btn btn_sanfona icon-notext j_sanfona wc_tooltip' style='margin:11px 0 0 8px !important;'><span class='wc_tooltip_balloon'>Recolher</span></span>";
    $cesta_montagem .= "<div style='display:inline-block; float:right;padding:10px;'>";
    if ($_SESSION["level"] >= LEVEL_SUPER_ADMIN):
        $cesta_montagem .= "<a title='Editar cesta' href='index.php?lbs=editar&id={$pedido_id}' class='post_single_center icon-pencil btn btn_blue' style='margin-right:10px;'>Editar</a>
                    <span rel='cestaDel' class='j_delete_action icon-cancel-circle btn btn_red' id='{$pedido_id}'>Excluir</span>
                    <span rel='cestaDel' callback='Pedidos' callback_action='deleteCesta' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$pedido_id}'>Remover Cesta?</span>";
    endif;

    $cesta_montagem .= "<form name='entrega' action='' method='post' class='auto_save' enctype='multipart/form-data'>
                    <input type='hidden' name='callback' value='Pedidos'/>
                    <input type='hidden' name='callback_action' value='editarEntrega'/>
                    <input type='hidden' name='pedido_id' value='{$pedido_id}' />
                    <label style='width: 100%;display: inline-block;margin-top:10px;'>
                        <select name='pedido_retirada'>";
    $Read->ExeRead(DB_ENTREGA);
    if ($Read->getResult()):
        foreach ($Read->getResult() as $retirada):
            $cesta_montagem .= "<option value='{$retirada['id']}' " . ($pedido_retirada == $retirada['id'] ? 'selected="selected"' : '') . ">{$retirada['descricao_entrega']}</option>";
        endforeach;
    endif;
    $cesta_montagem .= "</select>
                    </label>
                    <i class='form_load none'></i>
                </form>
            </div>";
endif;

$cesta_montagem .= "<div style='padding:0 10px 15px;'><b>Valor total: R$" . number_format($pedido_valor_total, 2, ',', '.') . "</b> | Cota:R$" . number_format($pedido_cota, 2, ',', '.') . " <em>(Cesta fixa:R$" . number_format($pedido_fixa, 2, ',', '.') . " + Variável:R$" . number_format($pedido_variavel, 2, ',', '.') . " + Avulso:R$" . number_format($pedido_avulso, 2, ',', '.') . " + Mensal:R$" . number_format($pedido_mensal, 2, ',', '.') . ")</em><br>
            <span class='j_entrega_{$pedido_id}'><b>Retirada: </b>{$descricao_entrega} " . ($pedido_entrega_valor > 0 ? '(R$  ' . number_format($pedido_entrega_valor, 2, ', ', '.') : '') . "</span>
        </div>
    </header>";

$cesta_montagem .= "<div class='j_sanfona_desc'>";
foreach ($ReadPrincipal as $PedIens):
    extract($PedIens);
    $cesta_montagem .= "<div class='item itemDel' id='{$item_id}'><form name='montagem' action='' method='post' class='auto_save' enctype='multipart/form-data'>
                <input type='hidden' name='callback' value='Pedidos'/>
                <input type='hidden' name='callback_action' value='montagem'/>
                <input type='hidden' name='item_id' value='{$item_id}' />
                <input type='hidden' name='item_produto' value='{$item_produto}' />
                <input type='hidden' name='admin_id' value='{$_SESSION['id']}' />";

    $ExcPdt = '';
    if ($_SESSION['level'] >= LEVEL_SUPER_ADMIN):
        $ExcPdt = "<p class='coll colExcPdt'>"
                . "<span rel='itemDel' class='j_delete_action btn btn_yellow' id='{$item_id}'>Excluir Produto</span>"
                . "<span rel='itemDel' callback='Pedidos' callback_action='deleteItem' class='j_delete_action_confirm icon-warning btn btn_red' style='display: none' id='{$item_id}'>Tem certeza?</span></p>";
    endif;
    $cesta_montagem .= $ExcPdt;
    $cesta_montagem .= "<p class='coll' style='text-align:right;'>
    " . str_replace(', 000', '', number_format($item_qtde, 3, ', ', '.')) . " x {$unidade}</p>
                <p class='coll collPdt' style='text-align:left;
'>{$item_produto} - {$Produtor}<span style='float:right;
text-transform: uppercase;
'>({$item_freq_cesta})</span></p>
                <p class='coll coll_r'>R$ " . number_format($item_valor * $item_qtde, 2, ', ', '.') . "</p>
                <div class='coll coll_r'>
                    <label class='item_separado_{$item_id} label_check label_yn " . ($item_separado == 2 ? 'active' : '' ) . "' for='item_separado_{$item_id}'>" . ($item_separado == 2 ? 'Separado' : 'Separar') . "</label>
                    <input id='item_separado_{$item_id}' class='ds_none' type='radio' " . ($item_separado == 2 ? 'value="2"' : 'value="1"') . " name='item_separado' checked='checked' />
                </div><div class='coll coll_r'>
                    <label class='label_check item_verificado_{$item_id} label_check label_yn " . ($item_verificado == 2 ? 'active' : ($item_verificado == 3 ? 'activeVerify' : '')) . "' for='item_verificado_{$item_id}'>" . ($item_verificado == 2 ? 'Verificado' : ($item_verificado == 3 ? 'Verificado 2x' : 'Verificar')) . " </label>
                    <input id = 'item_verificado_{$item_id}' class='ds_none' type='radio' " . ($item_verificado == 2 ? 'value="2"' : ($item_verificado == 3 ? 'value="3"' : 'value="1"')) . " name='item_verificado' checked='checked' />
                </div>
                <i class='form_load none'></i>
            </form>
            </div>";
endforeach;
$cesta_montagem .= "</div>";
$cesta_montagem .= "</article>";
