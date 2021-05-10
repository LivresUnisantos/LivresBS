<?php
ob_start();
session_start();

require '../../_app/Config.inc.php';

$NivelAcess = LEVEL_MONTAGEM;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;
// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

//GET DATES
$StartDate = (!empty($_SESSION['data_consulta']) ? $_SESSION['data_consulta'] : date("Y-m-d 00:00"));
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= ADMIN_NAME ?></title>
        <meta name="robots" content="noindex, nofollow"/>

        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>
        <link rel="base" href="<?= BASE; ?>/Painel">
        <style>
            *{font-family: 'Open Sans', sans-serif;}
            .single_order h1{
                display: block;
                padding: 4px;
                font-size: 11pt;
                margin: 0;
            }
            .single_order p{
                display: block;
                padding: 4px;
                background: #f2f2f2;
                font-size: 10pt;
                margin: 0;
            }
            .single_order p:nth-of-type(2n){
                background: #ccc;
            }

        </style>
    </head>
    <body>
        <div class="panel">
            <?php
            $wData[0] = "pedido_data = :start ";
            $wData[1] = "start={$StartDate}";

            $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
                    . "INNER JOIN " . DB_CONSUMIDORES . " AS b "
                    . "ON a.consumidor_id = b.id "
                    . "LEFT JOIN " . DB_ENTREGA . " AS d "
                    . "ON a.pedido_retirada = d.id "
                    . "WHERE $wData[0] "
                    . "ORDER BY b.consumidor ASC", "{$wData[1]}");
            if (!$Read->getResult()):
                echo Erro("<span class='al_center icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
            else:
                $i = 0;
                foreach ($Read->getResult() as $Order):
                    $i++;
                    extract($Order);
                    $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                            . "LEFT JOIN " . DB_PD_CONS . " AS b "
                            . "ON a.pedido_id = b.pedido_id "
                            . "LEFT JOIN " . DB_PRODUTO . " AS d "
                            . "ON a.produto_id = d.id "
                            . "LEFT JOIN " . DB_UNIDADE . " AS e "
                            . "ON a.item_tipo = e.id "
                            . "WHERE a.pedido_id = :pi "
                            . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}");
                    echo "<article class='single_order'>
                        <h1>{$i}) {$consumidor}</h1>";
                    foreach ($Read->getResult() as $PedIens):
                        extract($PedIens);
                        echo "<p>" . str_replace(',00', '', number_format($item_qtde, 2, ',', '.')) . " x {$unidade} | {$item_produto}</p>";
                    endforeach;

                    echo "<footer style='font-size:9pt;display:block;padding:4px;text-align:right;margin-bottom:0.5cm;'><b>Valor total: R$" . number_format($pedido_valor_total, 2, ",", ".") . "</b> "
                    . "<em>(Cota:R$ " . number_format($pedido_cota, 2, ",", ".") . " + "
                    . "Variável:R$ " . number_format($pedido_variavel, 2, ",", ".") . " + "
                    . "Mensal:R$ " . number_format($pedido_mensal, 2, ",", ".") . ")</em> | "
                    . "<b>Retirada:</b> {$descricao_entrega} "
                    . "" . ($pedido_entrega_valor > 0 ? '(R$ ' . number_format($pedido_entrega_valor, 2, ",", ".") . ')' : '') . "</footer>";
                    echo "</article>";
                endforeach;
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </body>
</html>
<?php
ob_end_flush();
