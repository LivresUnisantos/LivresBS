<?php
$NivelAcess = LEVEL_PED_PRODUTORES;
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
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-spoon-knife"><i class="icon-truck"></i>Pedido Produtores</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="../">Painel</a>
            <span class="crumb">/</span>
            Produtores
        </p>
    </div>
</header>

<header class="dashboard_header">
    <div class="wc_ead_chart_range">
        <form name="class_add" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Pedidos"/>
            <input type="hidden" name="callback_action" value="obterData"/>
            <input type="hidden" name="retorno" value="pedido_produtores"/>
            <label class="wc_ead_chart_range_picker">
                <span>Data:</span><input readonly="readonly" value="<?= date("d/m/Y H:i:s", strtotime($StartDate)); ?>" name="start_date" type="text" data-language="pt-BR" data-timepicker="true" class="jwc_datepicker_start" style="width: 150px;"/>
            </label><button class="btn icon-spinner11 icon-notext"></button><img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="../_img/load.gif"/>
        </form>
    </div>
</header>

<header class="dashboard_header">
    <?php
    //TOTAIS
    $Read->FullRead("SELECT pedido_id FROM " . DB_PD_CONS . " WHERE $wData[0] ", "{$wData[1]}");
    if ($Read->getResult()):
        $PedidosTotal = $Read->getRowCount();
        $PedidosItens = 0;
        foreach ($Read->getResult() as $Itens):
            $Read->FullRead("SELECT item_id FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi", "pi={$Itens['pedido_id']}");
            $PedidosItens += $Read->getRowCount();
        endforeach;
        ?>
        <div style="font-size:.8rem; text-align:center; color:#555;"><?= $PedidosTotal ?> cestas  | <?= $PedidosItens ?> itens <em>(cerca de <?= round($PedidosItens / $PedidosTotal, 0) ?> itens por pedido)</em> | <?= ucfirst(utf8_encode(strftime("%A, %d de %B", strtotime($StartDate)))); ?></div>
    <?php endif; ?>
</header>
<div class="box box100">
    <div class="panel" style="padding: 0; margin-bottom: 30px;">
        <?php
        $colunas = 6;
        $SomaProdutor = 0;

        $precoFixaProdutorTotal = 0;
        $precoVariavelProdutorTotal = 0;
        $precoAvulsoProdutorTotal = 0;
        $precoPreProdutorTotal = 0;

        //PRODUTORES QUE VÃO ENTREGAR NESTA DATA
        $Read->FullRead("SELECT a.Produtor, a.id AS idProdutor "
                . "FROM " . DB_PRODUTORES . " AS a "
                . "INNER JOIN " . DB_PD_CONS_ITENS . " AS b "
                . "ON a.id = b.item_produtor "
                . "LEFT JOIN " . DB_PD_CONS . " AS c "
                . "ON b.pedido_id = c.pedido_id "
                . "WHERE c.{$wData[0]} "
                . "GROUP BY b.item_produtor "
                . "ORDER BY a.Produtor ASC ", "{$wData[1]}");
        if (!$Read->getResult()):
            echo Erro("<span class='al_center icon-notification'>Olá, não existe nenhum registo para essa data!</span>", E_USER_NOTICE);
        else:
            $tbPdProd = "<table class='tableProdutores' cellspacing='0'>";

            foreach ($Read->getResult() as $Itens):

                $SomaProdutor = 0;
                $tbPdProd .= "<tr>";
                $tbPdProd .= "<th class='thProdutores' colspan='{$colunas}' style='background-color: #1aa4db; color: #fff;'>";
                $tbPdProd .= "" . strtoupper($Itens['Produtor']) . "";
                $tbPdProd .= "</th>";
                $tbPdProd .= "</tr>";

                $tbPdProd .= "<tr>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Produto";
                $tbPdProd .= "</th>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Fixa";
                $tbPdProd .= "</th>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Variável";
                $tbPdProd .= "</th>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Avulso";
                $tbPdProd .= "</th>";
                $tbPdProd .= "</th>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Pré Com.";
                $tbPdProd .= "</th>";
                $tbPdProd .= "<th class='thProdutores'>";
                $tbPdProd .= "Total";
                $tbPdProd .= "</th>";
                $tbPdProd .= "</tr>";

                $precoFixaProdutor = 0;
                $precoVariavelProdutor = 0;
                $precoAvulsoProdutor = 0;
                $precoPreProdutor = 0;

                $Read->FullRead("SELECT "
                        . "a.item_valor_produtor, "
                        . "a.item_produto, "
                        . "a.item_produtor, "
                        . "a.produto_id, "
                        . "p.unidade2 as unidade, "
                        . "p.multiplicador_unidade2 as multiplicador "
                        . "FROM " . DB_PD_CONS_ITENS . " AS a "
                        . "LEFT JOIN " . DB_PD_CONS . " AS b "
                        . "ON a.pedido_id = b.pedido_id "
                        . "LEFT JOIN " . DB_PRODUTO . " AS p "
                        . "ON a.produto_id = p.id "
                        . "WHERE b.{$wData[0]} "
                        . "AND a.item_produtor = :ip "
                        . "GROUP BY a.item_valor_produtor,a.produto_id "
                        . "ORDER BY a.item_produto "
                        , "{$wData[1]}&ip={$Itens['idProdutor']}");
                if ($Read->getResult()):

                    foreach ($Read->getResult() as $PdtDia):

                        $itensPdtProdutor = 0;
                        $precoPdtProdutor = 0;

                        $tbPdProd .= "<tr class='trProdutores'>";
                        $tbPdProd .= "<td class='tdProdutores'>{$PdtDia['item_produto']} [ R$ " . number_format($PdtDia['item_valor_produtor'], 2, ',', '.') . " ]</td>";

                        //FIXA
                        $Read->FullRead("SELECT sum(a.item_qtde) as quantidade, a.item_valor_produtor as valor_produtor "
                                . "FROM " . DB_PD_CONS_ITENS . " AS a "
                                . "LEFT JOIN " . DB_PD_CONS . " AS b "
                                . "ON a.pedido_id = b.pedido_id "
                                . "WHERE b.{$wData[0]} "
                                . "AND a.item_produtor = :ip "
                                . "AND a.produto_id = :p "
                                . "AND a.item_tipo_cesta = :fi group by a.item_valor_produtor", "{$wData[1]}&ip={$PdtDia['item_produtor']}&p={$PdtDia['produto_id']}&fi=fixa");

                        if ($Read->getResult()):
                            $fi = false;
                            foreach ($Read->getResult() as $produtoFixa):
                                if ($produtoFixa['valor_produtor'] == $PdtDia['item_valor_produtor']):
                                    $fi = true;
                                    $multiF = $produtoFixa['quantidade'] * $produtoFixa['valor_produtor'];
                                    $itensPdtProdutor += $produtoFixa['quantidade'];
                                    $precoPdtProdutor += $multiF;
                                    $valorFixaPdt = number_format($multiF, 2, ',', '.');
                                    $precoFixaProdutor += $multiF;
                                    $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($produtoFixa['quantidade'] / $PdtDia['multiplicador'], 2, ',', '.')) . " {$PdtDia['unidade']} x R$ " . number_format($produtoFixa['valor_produtor'] * $PdtDia['multiplicador'], 2, ',', '.') . " = R$ {$valorFixaPdt}</td>";
                                endif;
                            endforeach;
                            if ($fi == false):
                                $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                            endif;
                        else:
                            $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                        endif;

                        //CESTA VARIÁVEL
                        $Read->setPlaces("{$wData[1]}&ip={$Itens['idProdutor']}&p={$PdtDia['produto_id']}&fi=variavel");
                        if ($Read->getResult()):
                            $va = false;
                            foreach ($Read->getResult() as $produtoVariavel):
                                if ($produtoVariavel['valor_produtor'] == $PdtDia['item_valor_produtor']):
                                    $va = true;
                                    $multiV = $produtoVariavel['quantidade'] * $produtoVariavel['valor_produtor'];
                                    $itensPdtProdutor += $produtoVariavel['quantidade'];
                                    $precoPdtProdutor += $multiV;
                                    $valorVariavelPdt = number_format($multiV, 2, ',', '.');
                                    $precoVariavelProdutor += $multiV;
                                    $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($produtoVariavel['quantidade'] / $PdtDia['multiplicador'], 2, ',', '.')) . " {$PdtDia['unidade']} x R$ " . number_format($produtoVariavel['valor_produtor'] * $PdtDia['multiplicador'], 2, ',', '.') . " = R$ {$valorVariavelPdt}</td>";
                                endif;
                            endforeach;
                            if ($va == false):
                                $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                            endif;
                        else:
                            $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                        endif;

                        //AVULSO
                        $Read->setPlaces("{$wData[1]}&ip={$Itens['idProdutor']}&p={$PdtDia['produto_id']}&fi=avulso");
                        if ($Read->getResult()):
                            $av = false;
                            foreach ($Read->getResult() as $produtoAvulso):
                                if ($produtoAvulso['valor_produtor'] == $PdtDia['item_valor_produtor']):
                                    $av = true;
                                    $multiF = $produtoAvulso['quantidade'] * $produtoAvulso['valor_produtor'];
                                    $itensPdtProdutor += $produtoAvulso['quantidade'];
                                    $precoPdtProdutor += $multiF;
                                    $valorAvulsoPdt = number_format($multiF, 2, ',', '.');
                                    $precoAvulsoProdutor += $multiF;
                                    $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($produtoAvulso['quantidade'] / $PdtDia['multiplicador'], 2, ',', '.')) . " {$PdtDia['unidade']} x R$ " . number_format($produtoAvulso['valor_produtor'] * $PdtDia['multiplicador'], 2, ',', '.') . " = R$ {$valorAvulsoPdt}</td>";
                                endif;
                            endforeach;
                            if ($av == false):
                                $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                            endif;
                        else:
                            $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                        endif;

                        //PRÉ COMUNIDADE
                        $Read->setPlaces("{$wData[1]}&ip={$Itens['idProdutor']}&p={$PdtDia['produto_id']}&fi=pre");
                        if ($Read->getResult()):
                            $pr = false;
                            foreach ($Read->getResult() as $produtoPre):
                                if ($produtoPre['valor_produtor'] == $PdtDia['item_valor_produtor']):
                                    $pr = true;
                                    $multiF = $produtoPre['quantidade'] * $produtoPre['valor_produtor'];
                                    $itensPdtProdutor += $produtoPre['quantidade'];
                                    $precoPdtProdutor += $multiF;
                                    $valorPrePdt = number_format($multiF, 2, ',', '.');
                                    $precoPreProdutor += $multiF;
                                    $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($produtoPre['quantidade'] / $PdtDia['multiplicador'], 2, ',', '.')) . " {$PdtDia['unidade']} x R$ " . number_format($produtoPre['valor_produtor'] * $PdtDia['multiplicador'], 2, ',', '.') . " = R$ {$valorPrePdt}</td>";
                                endif;
                            endforeach;
                            if ($pr == false):
                                $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                            endif;
                        else:
                            $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                        endif;

                        //total item
                        $tbPdProd .= "<td class='tdProdutores al_right'>" . round($itensPdtProdutor / $PdtDia['multiplicador'], 2) . " {$PdtDia['unidade']} = R$ " . number_format($precoPdtProdutor, 2, ',', '.') . "</td>";
                        $SomaProdutor += $precoPdtProdutor;

                    endforeach;
                endif;

                $tbPdProd .= "<tr  class='trProdutores' style='font-weight:650; background:#f69b86'>";
                $tbPdProd .= "<td class='tdProdutores'>Soma {$Itens['Produtor']}</td>";
                $tbPdProd .= "<td class='tdProdutores'>Subtotal Fixa R$ " . number_format($precoFixaProdutor, 2, ',', '.') . "</td>";
                $tbPdProd .= "<td class='tdProdutores'>Subtotal Váriavel R$ " . number_format($precoVariavelProdutor, 2, ',', '.') . "</td>";
                $tbPdProd .= "<td class='tdProdutores'>Subtotal Avulso R$ " . number_format($precoAvulsoProdutor, 2, ',', '.') . "</td>";
                $tbPdProd .= "<td class='tdProdutores'>Subtotal Pré R$ " . number_format($precoPreProdutor, 2, ',', '.') . "</td>";
                $tbPdProd .= "<td class='tdProdutores al_right'>Total R$ " . number_format($SomaProdutor, 2, ',', '.') . "</td>";
                $tbPdProd .= "</tr>";
                $tbPdProd .= "<tr>";
                $tbPdProd .= "<td class='tdProdutores' colspan='{$colunas}'>&nbsp;</td>";
                $tbPdProd .= "</tr>";

                $precoFixaProdutorTotal += $precoFixaProdutor;
                $precoVariavelProdutorTotal += $precoVariavelProdutor;
                $precoAvulsoProdutorTotal += $precoAvulsoProdutor;
                $precoPreProdutorTotal += $precoPreProdutor;

            endforeach;

            $SomaProdutorTotal = $precoFixaProdutorTotal + $precoVariavelProdutorTotal + $precoAvulsoProdutorTotal + $precoPreProdutorTotal;

            $tbPdProd .= "<tr  class='trProdutores' style='font-weight:650; background:#000; color:#fff;'>";
            $tbPdProd .= "<td class='tdProdutores'>Soma Total</td>";
            $tbPdProd .= "<td class='tdProdutores'>Total Fixa R$ " . number_format($precoFixaProdutorTotal, 2, ',', '.') . "</td>";
            $tbPdProd .= "<td class='tdProdutores'>Total Váriavel R$ " . number_format($precoVariavelProdutorTotal, 2, ',', '.') . "</td>";
            $tbPdProd .= "<td class='tdProdutores'>Total Avulso R$ " . number_format($precoAvulsoProdutorTotal, 2, ',', '.') . "</td>";
            $tbPdProd .= "<td class='tdProdutores'>Total Pre R$ " . number_format($precoPreProdutorTotal, 2, ',', '.') . "</td>";
            $tbPdProd .= "<td class='tdProdutores al_right'>Total R$ " . number_format($SomaProdutorTotal, 2, ',', '.') . "</td>";
            $tbPdProd .= "</tr>";

            $tbPdProd .= "</table>";
            echo $tbPdProd;

        endif;
        ?>
        <div class="clear"></div>
    </div>
</div>