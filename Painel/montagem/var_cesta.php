<?php

$cesta_montagem = "<article class='single_order cestaDel' id='{$pedido_id}'>
    <header>
        <h1 style='display:inline-block;'>{$i}) {$consumidor} (G{$comunidade})</h1>";
if ($_SESSION["level"] >= LEVEL_BIKERS):
    $cesta_montagem .= "<span class='icon-shrink2 btn btn_sanfona icon-notext j_sanfona wc_tooltip' style='margin:11px 0 0 8px !important;'><span class='wc_tooltip_balloon'>Recolher</span></span>";
    
endif;
$excedente = $pedido_variavel - ($pedido_cota - $pedido_fixa);
if (abs($excedente) < 0.5) {
    $excedente = 0;
}
$cesta_montagem .= "<div style='padding:0 10px 15px;'>";
$cesta_montagem .= "<b>Cota Variável: </b>R$" . number_format($pedido_cota - $pedido_fixa, 2, ',', '.') . "<br>
<b>Total Variável: </b>R$" . number_format($pedido_variavel, 2, ',', '.') . " | 
";
if ($excedente != 0) {
    $cesta_montagem .= "<span style='color: ".($excedente > 0 ? '#FF0000' : '#056d27')."'><b>".($excedente < 0 ? 'Diferença a receber' : 'Diferença a pagar').": </b>R$" . number_format($excedente, 2, ',', '.') . "</span>";
} else {
    $cesta_montagem .= "Diferença menor que R$0,50 (R$".number_format($pedido_variavel - ($pedido_cota - $pedido_fixa),2,",",".").")";
}
$cesta_montagem .= '<br>';
//Respostas preenchidas
$totalRespostas=0;
if (array_key_exists($consumidor_id, $respostas)) {
    $resposta = $respostas[$consumidor_id];
    for ($i = 1; $i <= 2; $i++) {
        if (!is_null($resposta["idOpcao".$i])) {
            if ($totalRespostas > 0) {
                $cesta_montagem .= '<br>';
            }
            $cesta_montagem .= 'Resposta '.$i.': '.$listaProdutos[$resposta["idOpcao".$i]]["nome"];
            $totalRespostas++;
        }
    }
}
if ($totalRespostas == 0) {
    $cesta_montagem .= 'Sem resposta';
}
$cesta_montagem .= '<br>';
$cesta_montagem .= "</div>
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
    $cesta_montagem .= "<p class='coll' style='text-align:right;
'>" . str_replace(', 00', '', number_format($item_qtde, 2, ', ', '.')) . " x {$unidade}</p>
                <p class='coll collPdt' style='text-align:left;
'>{$item_produto} <span style='float:right;
text-transform: uppercase;
'>({$item_freq_cesta})</span></p>
                <p class='coll coll_r'>R$ " . number_format($item_valor * $item_qtde, 2, ', ', '.') . "</p>
                <div class='coll coll_r'>
                    <label class='item_separado_{$item_id} label_check label_yn " . ($item_separado == 2 ? 'active' : '' ) . "' for='item_separado_{$item_id}'>" . ($item_separado == 2 ? 'Separado' : 'Separar') . "</label>
                    <input id='item_separado_{$item_id}' class='ds_none' type='radio' " . ($item_separado == 2 ? 'value="2"' : 'value="1"') . " name='item_separado' checked='checked' />
                </div>
                <div class='coll coll_r'>
                    <label class='label_check item_verificado_{$item_id} label_check label_yn " . ($item_verificado == 2 ? 'active' : ($item_verificado == 3 ? 'activeVerify' : '')) . "' for='item_verificado_{$item_id}'>" . ($item_verificado == 2 ? 'Verificado' : ($item_verificado == 3 ? 'Verificado 2x' : 'Verificar')) . " </label>
                    <input id = 'item_verificado_{$item_id}' class='ds_none' type='radio' " . ($item_verificado == 2 ? 'value="2"' : ($item_verificado == 3 ? 'value="3"' : 'value="1"')) . " name='item_verificado' checked='checked' />
                </div>
                <i class='form_load none'></i>
            </form>
            </div>";
endforeach;
$cesta_montagem .= "<div style='margin: 40px 0; text-align: center;'>
        <span class='btn btn_yellow j_add_campos_item icon-plus'>Adicionar mais um item</span>
    </div>";
$cesta_montagem .= "</div>";
$cesta_montagem .= "</article>";
