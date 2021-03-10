<?php
$NivelAcess = LEVEL_MONTAGEM;
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

$getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$Page = ($getPage ? $getPage : 1);
$Pager = new Pager("index.php?lbs=home&page=", "<<", ">>", 5);
$Pager->ExePager($Page, 10);

$Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
        . "LEFT JOIN " . DB_CONSUMIDORES . " AS b "
        . "ON a.consumidor_id = b.id "
        . "LEFT JOIN " . DB_ENTREGA . " AS d "
        . "ON a.pedido_retirada = d.id "
        . "WHERE $wData[0] AND a.pedido_pre = 0 "
        . "ORDER BY b.consumidor ASC "
        . "LIMIT :limit OFFSET :offset", "{$wData[1]}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
$itensPD = $Read->getResult();
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-tree">Montagem</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../">Painel</a>
            <span class="crumb">/</span>
            Montagem das cestas
        </p>
    </div>
</header>

<header class="dashboard_header">
<!--    <div class="wc_ead_chart_range">
        <form name="class_add" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Pedidos"/>
            <input type="hidden" name="callback_action" value="obterData"/>
            <input type="hidden" name="retorno" value="home"/>
            <label class="wc_ead_chart_range_picker">
                <span>Data:</span><input readonly="readonly" value="<?= date("d/m/Y H:i", strtotime($StartDate)); ?>" name="start_date" type="text" data-language="pt-BR" data-timepicker="true" class="jwc_datepicker_start2" style="width: 150px;"/>
            </label><button class="btn icon-spinner11 icon-notext"></button><img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="../_img/load.gif"/>
        </form>
    </div>-->
    <?php
    $Read->FullRead("SELECT pedido_id "
            . "FROM " . DB_PD_CONS . " "
            . "WHERE {$wData[0]} AND pedido_endereco = :it", "{$wData[1]}&it=pre");
    $IdPedPre = '';
    if ($Read->getResult()):
        $IdPedPre = "&id={$Read->getResult()[0]['pedido_id']}";
    endif;
    ?>
    <div class="box box100 al_center" >
        <a href="index.php?lbs=pre<?= $IdPedPre ?>" title="Produtos da Pré Comunidade" class="icon-spinner6 btn btn_blue m_right">Produtos da Pré Comunidade</a>
        <a href="index.php?lbs=avulso" title="Adicionar cesta avulsa" class="icon-plus btn btn_yellow m_right">Adicionar Cesta Avulsa</a>
        <a href="montagem_pdf.php" title="PDF" target="_blank" class="icon-file-pdf btn btn_red icon-notext"></a>
    </div>
</header>

<?php include 'homeFiltro.php'; ?>

<header class="dashboard_header">
    <?php
//TOTAIS
    $Read->FullRead("SELECT pedido_id FROM " . DB_PD_CONS . " WHERE $wData[0] AND pedido_pre = 0", "{$wData[1]}");
    if ($Read->getResult()):
        $PedidosTotal = $Read->getRowCount();
        $PedidosItens = 0;
        foreach ($Read->getResult() as $Itens):
            $Read->FullRead("SELECT item_id FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi", "pi={$Itens['pedido_id']}");
            $PedidosItens += $Read->getRowCount();
        endforeach;
        ?>
        <div class="j_content" style="font-size:.8rem; text-align:center; color:#555;"><?= $PedidosTotal ?> cestas  | <?= $PedidosItens ?> itens <em>(cerca de <?= round($PedidosItens / $PedidosTotal, 0) ?> itens por pedido)</em> | <?= ucfirst(utf8_encode(strftime("%A, %d de %B", strtotime($StartDate)))); ?>
        <?php endif; ?>
        <b class="btn btn_sanfona fl_right icon-shrink icon-notext j_sanfona_todos wc_tooltip"><span class='wc_tooltip_balloon'>Recolher todos</span></b>
    </div>
</header>

<div class="box box100">
    <div class="panel j_montagem" style="padding: 0;">
        <?php
        if (!$itensPD):
            $Pager->ReturnPage();
            echo Erro("<span class='al_center icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
        else:
            $i = $Pager->getOffset();
            foreach ($itensPD as $Itens):
                $i++;
                extract($Itens);
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                        . "LEFT JOIN " . DB_PD_CONS . " AS b "
                        . "ON a.pedido_id = b.pedido_id "
                        . "LEFT JOIN " . DB_PRODUTO . " AS d "
                        . "ON a.produto_id = d.id "
                        . "LEFT JOIN " . DB_UNIDADE . " AS e "
                        . "ON a.item_tipo = e.id "
                        . "WHERE a.pedido_id = :pi "
                        . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}");
                $ReadPrincipal = $Read->getResult();
                require 'mont_cesta.php';
                echo $cesta_montagem;
            endforeach;

            //PAGINAÇÃO
            $Pager->ExeFullPaginator("SELECT * FROM " . DB_PD_CONS . " AS a "
                    . "LEFT JOIN " . DB_CONSUMIDORES . " AS b "
                    . "ON a.consumidor_id = b.id "
                    . "LEFT JOIN " . DB_ENTREGA . " AS d "
                    . "ON a.pedido_retirada = d.id "
                    . "WHERE $wData[0]", "{$wData[1]}");
            echo $Pager->getPaginator();
        endif;
        ?>
        <div class="clear"></div>
    </div>
</div>