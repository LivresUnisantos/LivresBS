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
        $Read->FullRead("SELECT a.Produtor, a.id AS idProdutor FROM " . DB_PRODUTORES . " AS a "
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
                extract($Itens);

                $SomaProdutor = 0;

                $tbPdProd .= "<tr>";
                $tbPdProd .= "<th class='thProdutores' colspan='{$colunas}' style='background-color: #1aa4db; color: #fff;'>";
                $tbPdProd .= "" . strtoupper($Produtor) . "";
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

                //PRODUTOS POR PRODUTOR E DATA
                $Read->FullRead("SELECT a.item_produto, p.id AS idProduto, p.unidade2, p.multiplicador_unidade2 "
                        . "FROM " . DB_PD_CONS_ITENS . " AS a "
                        . "LEFT JOIN " . DB_PD_CONS . " AS b "
                        . "ON a.pedido_id = b.pedido_id "
                        . "LEFT JOIN " . DB_PRODUTO . " AS p "
                        . "ON a.produto_id = p.id "
                        . "WHERE b.{$wData[0]} "
                        . "AND a.item_produtor = :ip "
                        . "GROUP BY a.produto_id "
                        . "ORDER BY a.item_produto", "{$wData[1]}&ip={$idProdutor}");
                $Produtos = $Read->getResult();

                $precoFixaProdutor = 0;
                $precoVariavelProdutor = 0;
                $precoAvulsoProdutor = 0;
                $precoPreProdutor = 0;

                foreach ($Produtos as $Pdt):
                    $itensPdtProdutor = 0;
                    $precoPdtProdutor = 0;
                    $Fixa = null;
                    $Variavel = null;
                    $Avulso = null;
                    $Pre = null;

                    //CESTA FIXA
                    $Read->FullRead("SELECT sum(a.item_qtde) as Qtde, c.unidade as Unidade, a.item_produto, a.item_valor_produtor "
                            . "FROM " . DB_PD_CONS_ITENS . " AS a "
                            . "LEFT JOIN " . DB_PD_CONS . " AS b "
                            . "ON a.pedido_id = b.pedido_id "
                            . "LEFT JOIN " . DB_UNIDADE . " AS c "
                            . "ON a.item_tipo = c.id "
                            . "WHERE b.{$wData[0]} "
                            . "AND a.item_produtor = :ip "
                            . "AND a.produto_id = :pd "
                            . "AND a.item_tipo_cesta = :fi "
                            . "GROUP BY a.produto_id "
                            . "ORDER BY a.item_produto", "{$wData[1]}&ip={$idProdutor}&pd={$Pdt['idProduto']}&fi=fixa");
                    if ($Read->getResult()):
                        $Fixa = $Read->getResult()[0];
                    endif;

                    //CESTA VARIÁVEL
                    $Read->setPlaces("{$wData[1]}&ip={$idProdutor}&pd={$Pdt['idProduto']}&fi=variavel");
                    if ($Read->getResult()):
                        $Variavel = $Read->getResult()[0];
                    endif;

                    //AVULSO
                    $Read->setPlaces("{$wData[1]}&ip={$idProdutor}&pd={$Pdt['idProduto']}&fi=avulso");
                    if ($Read->getResult()):
                        $Avulso = $Read->getResult()[0];
                    endif;

                    //PRÉ COMUNIDADE
                    $Read->setPlaces("{$wData[1]}&ip={$idProdutor}&pd={$Pdt['idProduto']}&fi=pre");
                    if ($Read->getResult()):
                        $Pre = $Read->getResult()[0];
                    endif;

                    $tbPdProd .= "<tr class='trProdutores'>";
                    $tbPdProd .= "<td class='tdProdutores'>{$Pdt['item_produto']}</td>";

                    if (isset($Fixa) && !empty($Fixa)):
                        $multiF = $Fixa['Qtde'] * $Fixa['item_valor_produtor'];
                        $itensPdtProdutor += $Fixa['Qtde'];
                        $precoPdtProdutor += $multiF;
                        $valorFixaPdt = number_format($multiF, 2, ',', '.');
                        $precoFixaProdutor += $multiF;
                        $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($Fixa['Qtde'] / $Pdt['multiplicador_unidade2'], 2, ',', '.')) . " {$Pdt['unidade2']} x R$ " . number_format($Fixa['item_valor_produtor'] * $Pdt['multiplicador_unidade2'], 2, ',', '.') . " = R$ {$valorFixaPdt}</td>";
                    else:
                        $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                    endif;

                    if (isset($Variavel) && !empty($Variavel)):
                        $multiV = $Variavel['Qtde'] * $Variavel['item_valor_produtor'];
                        $itensPdtProdutor += $Variavel['Qtde'];
                        $precoPdtProdutor += $multiV;
                        $valorVariavelPdt = number_format($multiV, 2, ',', '.');
                        $precoVariavelProdutor += $multiV;
                        $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($Variavel['Qtde'] / $Pdt['multiplicador_unidade2'], 2, ',', '.')) . " {$Pdt['unidade2']} x R$ " . number_format($Variavel['item_valor_produtor'] * $Pdt['multiplicador_unidade2'], 2, ',', '.') . " = R$ {$valorVariavelPdt}</td>";
                    else:
                        $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                    endif;

                    if (isset($Avulso) && !empty($Avulso)):
                        $multiA = $Avulso['Qtde'] * $Avulso['item_valor_produtor'];
                        $itensPdtProdutor += $Avulso['Qtde'];
                        $precoPdtProdutor += $multiA;
                        $valorAvulsoPdt = number_format($multiA, 2, ',', '.');
                        $precoAvulsoProdutor += $multiA;
                        $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($Avulso['Qtde'] / $Pdt['multiplicador_unidade2'], 2, ',', '.')) . " {$Pdt['unidade2']} x R$ " . number_format($Avulso['item_valor_produtor'] * $Pdt['multiplicador_unidade2'], 2, '.', ',') . " = R$ {$valorAvulsoPdt}</td>";
                    else:
                        $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                    endif;

                    if (isset($Pre) && !empty($Pre)):
                        $multiP = $Pre['Qtde'] * $Pre['item_valor_produtor'];
                        $itensPdtProdutor += $Pre['Qtde'];
                        $precoPdtProdutor += $multiP;
                        $valorPrePdt = number_format($multiP, 2, ',', '.');
                        $precoPreProdutor += $multiP;
                        $tbPdProd .= "<td class='tdProdutores al_right'>" . str_replace(',00', '', number_format($Pre['Qtde'] / $Pdt['multiplicador_unidade2'], 2, ',', '.')) . " {$Pdt['unidade2']} x R$ " . number_format($Pre['item_valor_produtor'] * $Pdt['multiplicador_unidade2'], 2, '.', ',') . " = R$ {$valorPrePdt}</td>";
                    else:
                        $tbPdProd .= "<td class='tdProdutores'>&nbsp;</td>";
                    endif;

                    //total item
                    $tbPdProd .= "<td class='tdProdutores al_right'>" . round($itensPdtProdutor / $Pdt['multiplicador_unidade2'], 2) . " {$Pdt['unidade2']} = R$ " . number_format($precoPdtProdutor, 2, ',', '.') . "</td>";
                    $SomaProdutor += $precoPdtProdutor;
                endforeach;
                $tbPdProd .= "<tr  class='trProdutores' style='font-weight:650; background:#f69b86'>";
                $tbPdProd .= "<td class='tdProdutores'>Soma {$Produtor}</td>";
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