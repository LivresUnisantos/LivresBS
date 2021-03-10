<?php
$AdminLevel = LEVEL_WC_PRODUCTS_ORDERS;
if (!APP_ORDERS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//GET DATES
$StartDate = (!empty($_SESSION['relatorio_date'][0]) ? $_SESSION['relatorio_date'][0] : date("2018-01-01"));
$EndDate = (!empty($_SESSION['relatorio_date'][1]) ? $_SESSION['relatorio_date'][1] : date("Y-m-d"));

//DEFAULT REPORT
$DateStart = new DateTime($StartDate);
$DateEnd = new DateTime(date("Y-m-d", strtotime($EndDate . "+1day")));
$DateInt = new DateInterval("P1D");
$DateInterval = new DatePeriod($DateStart, $DateInt, $DateEnd);


$OrderB = (!empty($_SESSION['order_prod']) ? $_SESSION['order_prod'] : 'a.order_id,a.item_name');
$StatusE = (!empty($_SESSION['status_prod']) ? $_SESSION['status_prod'] : 0);
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-cart">Produção</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php">Dashboard</a>
            <span class="crumb">/</span>
            Produção
        </p>

    </div>
    <div class="dashboard_header_search">
        <form name='order_p' action='' method='post' class='auto_save' enctype='multipart/form-data'>
            <input type='hidden' name='callback' value='Orders'/>
            <input type='hidden' name='callback_action' value='get_prod'/>
            <input type='hidden' name='report_back' value='orders/producao' />
            <label class='label coll'>
                <select name='order_prod' required>
                    <option value='a.order_id' <?= ($OrderB == 'a.order_id,a.item_name' ? " selected='selected'" : ''); ?>>&raquo; Id do Pedido</option>
                    <option value='a.item_name' <?= ($OrderB == 'a.item_name' ? " selected='selected'" : ''); ?>>&raquo; Estampa</option>
                </select>
            </label>

            <label class='label coll'>
                <select name='status_prod'>
                    <option value='0' <?= ($StatusE == 0 ? " selected='selected'" : ''); ?>>&raquo; Todos</option>
                    <option value='1' <?= ($StatusE == 1 ? " selected='selected'" : ''); ?>>&raquo; A Estampar</option>
                    <option value='2' <?= ($StatusE == 2 ? " selected='selected'" : ''); ?>>&raquo; Estampado</option>
                </select>
            </label>

        </form>
    </div>
</header>

<div class="dashboard_content">
    <header class="dashboard_header">
        <div class="wc_ead_chart_range">
            <form name="class_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Reports"/>
                <input type="hidden" name="callback_action" value="get_relatorio"/>
                <input type="hidden" name="report_back" value="orders/producao"/>

                <label class="wc_ead_chart_range_picker">
                    <span>DE:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($StartDate)); ?>" name="start_date" type="text" data-language="pt-BR" class="jwc_datepicker_start"/>
                </label><label class="wc_ead_chart_range_picker">
                    <span>ATÉ:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($EndDate)); ?>" name="end_date" type="text" data-language="pt-BR" class="jwc_datepicker_end"/>
                </label><button class="btn icon-spinner11 icon-notext"></button><img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </form>
        </div>

        <a href="_sis/orders/producao-print.php" title="Imprimir produção" target="_blank" class='icon-printer btn btn_green icon-notext fl_right'></a>
    </header>
</div>

<div class="dashboard_content">

    <div class="box box100">
        <div class="panel_header default">
            <h2 class="icon-cart">Produção</h2>
        </div>

        <div class="panel" style="padding: 0;">
            <style>
                .producaoPeca{
                    width: 100px;
                    display: inline-block;
                }
            </style>

            <?php
            $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $Page = ($getPage ? $getPage : 1);
            $Pager = new Pager("dashboard.php?wc=orders/producao&page=", "<<", ">>", 5);
            $Pager->ExePager($Page, 300);
            $orderby = $OrderB;

            $EstWhere = null;
            if ($StatusE == 1):
                $EstWhere = 'AND a.item_producao = 1';
            elseif ($StatusE == 2):
                $EstWhere = 'AND a.item_producao = 2';
            endif;

            $fimMaisUm = date('Y-m-d', strtotime($EndDate . ' + 1 days'));
            $wData[0] = "order_date >= :start AND order_date <= :end ";
            $wData[1] = "start={$StartDate}&end={$fimMaisUm}";

            $Read->FullRead("SELECT count(order_id) as total FROM " . DB_ORDERS . " WHERE $wData[0] AND order_status = 6", "{$wData[1]}");
            $Total = $Read->getResult()[0]['total'];

            $Read->FullRead("SELECT count(DISTINCT(b.order_id)) as total, sum(a.item_amount) as totalpc FROM " . DB_ORDERS_ITEMS . " AS a INNER JOIN " . DB_ORDERS . " AS b ON a.order_id = b.order_id WHERE $wData[0] AND b.order_status = 6 AND a.item_producao = 1", "{$wData[1]}");
            $aEstampar = $Read->getResult()[0]['total'];
            $aEstamparPc = $Read->getResult()[0]['totalpc'];

            $Read->FullRead("SELECT count(DISTINCT(b.order_id)) as total, sum(a.item_amount) as totalpc FROM " . DB_ORDERS_ITEMS . " AS a INNER JOIN " . DB_ORDERS . " AS b ON a.order_id = b.order_id WHERE $wData[0] AND b.order_status = 6 AND a.item_producao = 2", "{$wData[1]}");
            $Estampado = $Read->getResult()[0]['total'];
            $EstampadoPc = $Read->getResult()[0]['totalpc'];

            $Read->FullRead("SELECT * FROM " . DB_ORDERS_ITEMS . " AS a INNER JOIN " . DB_ORDERS . " AS b ON a.order_id = b.order_id WHERE $wData[0] AND b.order_status = 6 {$EstWhere} ORDER BY {$orderby} LIMIT :limit OFFSET :offset", "{$wData[1]}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo Erro("<span class='al_center icon-notification'>Olá {$Admin['user_name']}. Não existe nenhum pedido para produção!</span>", E_USER_NOTICE);
            else:
                echo Erro("<span class='al_center'>Total: {$Total} [ Estampado: {$Estampado} ({$EstampadoPc} peças) | A estampar: {$aEstampar} ({$aEstamparPc} peças) ]</span>", E_USER_NOTICE);
                foreach ($Read->getResult() as $Order):
                    $fundoQtde = ($Order['item_amount'] > 1 ? 'background:yellow;' : '');
                    $corModelo = ($Order['item_modelo'] == 'Camiseta' ? 'blue' : 'red');
                    $Transparencia = ($Order['item_producao'] > 1 ? "style='opacity:0.6'" : '');

                    $Read->FullRead("SELECT user_name, user_lastname FROM " . DB_USERS . " WHERE user_id = :user", "user={$Order['user_id']}");
                    $Client = ($Read->getResult() ? "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}" : 'N/A');
                    $Traking = BASE . '/sro.php?objeto=';
                    echo "<article class='single_order' {$Transparencia}>
                            <form name='producao' action='' method='post' class='auto_save' enctype='multipart/form-data'>
                                <input type='hidden' name='callback' value='Orders'/>
                                <input type='hidden' name='callback_action' value='producao'/>
                                <input type='hidden' name='item_id' value='{$Order['item_id']}' />
                                <p class='coll coll_r' style='width:80px;min-width:80px;'><b><a class='order' href='dashboard.php?wc=orders/order&id={$Order['order_id']}' title='Ver Pedido'>" . str_pad($Order['order_id'], 7, 0, STR_PAD_LEFT) . "</a></b></p>
                                <p class='coll coll_r' style='min-width:80px !important;width:100px'><b><a href='declaracao.php?id={$Order['order_id']}' title='Imprimir Declaração' target='_blank' class='see'>Declaração</a></b></p>
                                <p class='coll coll_r' style='min-width:35px;width:35px; text-align:center'><a title='Rastrear Pedido' target='_blanck' href='{$Traking}{$Order['order_tracking']}'><span class='icon-truck'></span></a></p>
                                <p class='coll' style='text-align:left;'>" . Check::Chars($Client, 15) . "</p>
                                <p class='coll' style='text-align:left; min-width: 300px;color:{$corModelo}; {$fundoQtde}'>{$Order['item_amount']} * <span class='producaoPeca'>{$Order['item_modelo']}</span><span class='producaoPeca' style='width:50px;'>{$Order['item_tamanho']}</span><span class='producaoPeca'>" . getCorTecido($Order['item_cor']) . "</span></p>
                                <p class='coll' style='text-align:left; min-width: 200px;color:{$corModelo}; {$fundoQtde}'>" . Check::Chars($Order['item_name'], 20) . "</p>
                                <p class='coll' style='min-width:60px;width:60px;'>" . date('d/m', strtotime($Order['order_date'])) . "</p>
                                <div class='coll' style='min-width:270px !important'>
                                    <label class='item_producao_{$Order['item_id']} label_check label_yn " . ($Order['item_producao'] == 2 ? 'active' : '') . "' for='item_producao_{$Order['item_id']}'>" . ($Order['item_producao'] == 2 ? 'Estampado' : 'A estampar') . "</label>
                                    <input id='item_producao_{$Order['item_id']}' class='ds_none' type='radio' " . ($Order['item_producao'] == 2 ? 'value="2"' : 'value="1"') . " name='item_producao' checked='checked' />


                                    <label class='label_check item_estoque_{$Order['item_id']} label_check label_yn " . ($Order['item_estoque'] == 2 ? 'active' : '') . "' for='item_estoque_{$Order['item_id']}'>" . ($Order['item_estoque'] == 2 ? 'Removido do estoque!' : 'Remover do estoque!') . "</label>
                                    <input id='item_estoque_{$Order['item_id']}' class='ds_none' type='radio' " . ($Order['item_estoque'] == 2 ? 'value="2"' : 'value="1"') . " name='item_estoque' checked='checked' />
                                </div>

                            </form>
                        </article>";
                endforeach;
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>
</div>
<script>
    $(function () {
        //DATEPICKER CONFIG
        var wc_datepicker_start = $('.jwc_datepicker_start').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');
        var wc_datepicker_end = $('.jwc_datepicker_end').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');

        $('.jwc_datepicker_start').click(function () {
            var DateString = $('.jwc_datepicker_end').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_start.update('maxDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($StartDate)); ?>");
            }
        });

        $('.jwc_datepicker_end').click(function () {
            var DateString = $('.jwc_datepicker_start').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_end.update('minDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($EndDate)); ?>");
            }
        });
    });
</script>