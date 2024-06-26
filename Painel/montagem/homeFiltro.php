<?php
// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
if ($itensPD):
    ?>
    <header class="dashboard_header" style="text-align: center;">
        <form name="filtroMontagem" action="" method="post" class="auto_save" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Pedidos"/>
            <input type="hidden" name="callback_action" value="filtroMontagem"/>
            <input type="hidden" name="wData0" value="<?= $wData[0]; ?>"/>
            <input type="hidden" name="wData1" value="<?= $wData[1]; ?>"/>

            <label class="box box25">
                <?php
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
                        . "LEFT JOIN " . DB_CONSUMIDORES . " AS b "
                        . "ON a.consumidor_id = b.id "
                        . "WHERE $wData[0] AND a.pedido_pre = 0 "
                        . "ORDER BY b.consumidor ASC ", "{$wData[1]}");
                $Read->getResult();
                ?>
                <select class="js-example-basic-single" name="consumidor">
                    <option value="">Filtrar por consumidor</option>
                    <?php
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $Pdt):
                            ?>
                            <option value="<?= $Pdt['id'] ?>"><?= $Pdt['consumidor'] ?></option>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </select>

                <!--                if ($Read->getResult()):
                                    echo '<datalist id="Consumidores">';
                                    foreach ($Read->getResult() as $Pdt):
                                        echo "<option value='{$Pdt['consumidor']}'></option>";
                                    endforeach;
                                    echo '</datalist>';
                                endif;
                                ?>
                                <input style="padding: 8px;" list="Consumidores" type="text" name="consumidor" placeholder="Filtrar por consumidor" />-->
            </label>

            <label class="box box25">
                <?php
                $dataPrevisao = explode(' ', $wData[1]);
                $Read->FullRead("SELECT a.id, a.nome FROM " . DB_PRODUTO . " AS a "
                        . "RIGHT JOIN " . DB_PD_CONS_ITENS . " AS b "
                        . "ON a.id = b.produto_id "
                        . "LEFT JOIN " . DB_PD_CONS . " AS c "
                        . "ON b.pedido_id = c.pedido_id "
                        . "WHERE c.{$wData[0]} AND a.previsao <= :p "
                        . "AND soft_delete = 0 "
                        . "GROUP BY b.produto_id "
                        . "ORDER BY nome", "$wData[1]&p={$dataPrevisao[0]}");
                ?>
                <select class="js-example-basic-single" name="produto">
                    <option value="">Filtrar por produto</option>
                    <?php
                    if ($Read->getResult()):
                        foreach ($Read->getResult() as $Pdt):
                            ?>
                            <option value="<?= $Pdt['id'] ?>"><?= $Pdt['nome'] ?></option>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </select>
            </label>
            <label class="box box25">
                <select name="retirada">
                    <option value='0'>Filtrar por retirada</option>
                    <?php
                    $Read->ExeRead(DB_ENTREGA);
                    foreach ($Read->getResult() as $Entrega):
                        echo "<option value='{$Entrega['id']}'>{$Entrega['descricao_entrega']}</option>";
                    endforeach;
                    ?>
                </select>
            </label>
            <label class="box box25">
                <select name="montagem">
                    <option value='0'>Filtrar por separado/verificado</option>
                    <option value='1'>Todos Separados</option>
                    <option value='2'>Ainda não foram Separados</option>
                    <option value='3'>Todos Verficados</option>
                    <option value='4'>Ainda não foram Verficados</option>
                </select>
            </label>
            <button type="button" value="LimparFiltro" onClick="window.location.reload()" class="btn btn_red" style="margin-top: 12px">Resetar filtro</button><img class='form_load none' style='margin-left: 10px;' alt='Enviando!' title='Enviando Requisição!' src='../_img/load.gif'/>
        </form>
    </header>
<?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
                $(document).ready(function () {
                    $('.js-example-basic-single').select2();
                });
</script>