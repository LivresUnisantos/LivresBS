<?php

session_start();
require '../../_app/Config.inc.php';

$NivelAcess = LEVEL_MONTAGEM;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    echo json_encode($jSON);
    die;
endif;

//DEFINE O CALLBACK E RECUPERA O POST
$jSON = null;
$CallBack = 'Pedidos';
$PostData = filter_input_array(INPUT_POST, FILTER_DEFAULT);

//VALIDA AÇÃO
if ($PostData && $PostData['callback_action'] && $PostData['callback'] == $CallBack):
    //PREPARA OS DADOS
    $Case = $PostData['callback_action'];
    unset($PostData['callback'], $PostData['callback_action']);

    // AUTO INSTANCE OBJECT READ
    if (empty($Read)):
        $Read = new Read;
    endif;

    // AUTO INSTANCE OBJECT CREATE
    if (empty($Create)):
        $Create = new Create;
    endif;

    // AUTO INSTANCE OBJECT UPDATE
    if (empty($Update)):
        $Update = new Update;
    endif;

    // AUTO INSTANCE OBJECT DELETE
    if (empty($Delete)):
        $Delete = new Delete;
    endif;

    //SELECIONA AÇÃO
    switch ($Case):
        //COLOCAR DATA NA SESSÃO E REDIRECIONAR/RELOAD
        case 'obterData':
            $DateStart = date("Y-m-d H:i:s", strtotime(($PostData['start_date'] ? Check::Data($PostData['start_date']) : date("Y-m-d H:i:s"))));
            $_SESSION['data_consulta'] = $DateStart;
            $jSON['redirect'] = "index.php?lbs={$PostData['retorno']}";
            break;

        //MARCAR CADA ITEM QUE FOI SEPARADO
        case 'montagem':
            $Read->FullRead("SELECT item_separado, item_verificado FROM " . DB_PD_CONS_ITENS . " WHERE item_id = :ii", "ii={$PostData['item_id']}");
            if ($Read->getResult()):
                $ReadMontagem = $Read->getResult()[0];
                if ($ReadMontagem['item_separado'] != 2 && $PostData['item_verificado'] == 2):
                    $jSON['trigger'] = AjaxErro("Ooooops: não é possível marcar <b>verificado antes de separar</b>!", E_USER_WARNING);
                    $jSON['desativarBtn'] = $PostData['item_id'];
                    echo json_encode($jSON);
                    return;
                endif;

                //DESMARCAR VERIFICADO
                if ($ReadMontagem['item_separado'] == 2 && $PostData['item_verificado'] == 1):
                    $atualizaItem['item_verificado'] = $PostData['item_verificado'];
                    $atualizaItem['admin_id_verificar'] = $PostData['admin_id'];
                endif;

                //DESMARCAR SEPARADO
                if ($PostData['item_separado'] == 1 && $ReadMontagem['item_verificado'] < 2):
                    $atualizaItem['item_separado'] = $PostData['item_separado'];
                    $atualizaItem['admin_id_separar'] = $PostData['admin_id'];
                endif;

                //MARCAR SEPARADO
                if ($PostData['item_separado'] == 2 && $ReadMontagem['item_verificado'] < 2):
                    $atualizaItem['item_separado'] = $PostData['item_separado'];
                    $atualizaItem['admin_id_separar'] = $PostData['admin_id'];
                endif;

                //MARCAR VERIFICADO
                if ($ReadMontagem['item_separado'] == 2 && $PostData['item_verificado'] >= 2):
                    $atualizaItem['item_verificado'] = $PostData['item_verificado'];
                    $atualizaItem['admin_id_verificar'] = $PostData['admin_id'];
                endif;
            endif;

            $Update->ExeUpdate(DB_PD_CONS_ITENS, $atualizaItem, "WHERE item_id = :ii", "ii={$PostData['item_id']}");
            $jSON['trigger'] = AjaxErro("( √ ) <b>{$PostData['item_produto']} atualizado!</b>", E_USER_NOTICE);

            break;

        //ALTERAR A FORMA DE ENTREGA DA CESTA
        case 'editarEntrega':
            $Read->ExeRead(DB_PD_CONS, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
            if ($Read->getResult()):
                $pedidoId = $PostData['pedido_id'];
                unset($PostData['pedido_id']);
                $Read->ExeRead(DB_ENTREGA, "WHERE id = :id", "id={$PostData['pedido_retirada']}");
                if ($Read->getResult()):
                    $PostData['pedido_entrega_valor'] = $Read->getResult()[0]['valor_entrega'];
                    $Ent = $Read->getResult()[0]['descricao_entrega'] . " " . ($Read->getResult()[0]['valor_entrega'] > 0 ? '(R$ ' . number_format($Read->getResult()[0]['valor_entrega'], 2, ",", ".") . ')' : '');
                endif;
                $Update->ExeUpdate(DB_PD_CONS, $PostData, "WHERE pedido_id = :pi", "pi={$pedidoId}");
                $jSON['trigger'] = AjaxErro("( √ ) <b>Forma de entrega atualizada com sucesso!</b>", E_USER_NOTICE);
                $divEnt = ".j_entrega_{$pedidoId}";
                $jSON['retirada'] = [$divEnt, "<b>Retirada:</b> {$Ent}"];
            else:
                $jSON['trigger'] = AjaxErro("Ooooops: não foi possível editar a forma de entrega da cesta!", E_USER_WARNING);
                echo json_encode($jSON);
                return;
            endif;
            break;

        //EDITAR CESTA INDIVIDUALMENTE
        case 'editarPedido':
            $Read->ExeRead(DB_PRODUTO, "WHERE nome = :n", "n={$PostData['item_produto']}");
            if ($Read->getResult()):
                if ($PostData['item_valor'] == 'comunidade'):
                    $ArrQtde['item_valor'] = $Read->getResult()[0]['preco'];
                elseif ($PostData['item_valor'] == 'mercado'):
                    $ArrQtde['item_valor'] = $Read->getResult()[0]['preco_mercado'];
                elseif ($PostData['item_valor'] == 'lojinha'):
                    $ArrQtde['item_valor'] = $Read->getResult()[0]['preco_lojinha'];
                elseif ($PostData['item_valor'] == 'cortesia'):
                    $ArrQtde['item_valor'] = 0;
                endif;
            endif;
            $ArrQtde['item_qtde'] = str_replace(',', '.', $PostData['item_qtde']);
            $ArrQtde['admin_id_editar'] = $PostData['admin_id_editar'];
            $Update->ExeUpdate(DB_PD_CONS_ITENS, $ArrQtde, "WHERE item_id = :ii", "ii={$PostData['item_id']}");
            if ($Update->getResult()):
                $jSON['trigger'] = AjaxErro("( √ ) <b>{$PostData['item_produto']} atualizado!</b>", E_USER_NOTICE);
            endif;

            $TotalPedido = 0;
            $Read->ExeRead(DB_PD_CONS_ITENS, "WHERE item_id = :ii", "ii={$PostData['item_id']}");
            $ReadPItens = $Read->getResult()[0];
            if ($ReadPItens['item_tipo_cesta'] == 'fixa'):
                if ($ReadPItens['item_freq_cesta'] == 'mensal'):
                    $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_freq_cesta = :if", "pi={$ReadPItens['pedido_id']}&if=mensal");
                    if ($Read->getResult()):
                        $TotalPedido = $Read->getResult()[0]['totalPedido'];
                        $ArrUpPedidoFi = ['pedido_mensal' => $TotalPedido];
                        $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$ReadPItens['pedido_id']}");
                    endif;
                else:
                    $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :it AND  item_freq_cesta != :if", "pi={$ReadPItens['pedido_id']}&it=fixa&if=mensal");
                    if ($Read->getResult()):
                        $TotalPedido = $Read->getResult()[0]['totalPedido'];
                        $ArrUpPedidoFi = ['pedido_fixa' => $TotalPedido];
                        $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$ReadPItens['pedido_id']}");
                    endif;
                endif;
            endif;

            if ($ReadPItens['item_tipo_cesta'] == 'variavel'):
                $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$ReadPItens['pedido_id']}&if=variavel");
                $Variavel = $Read->getResult();
                if ($Variavel):
                    $TotalPedido = $Read->getResult()[0]['totalPedido'];
                    $ArrUpPedidoVa = ['pedido_variavel' => $TotalPedido];
                    $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoVa, "WHERE pedido_id = :pi", "pi={$ReadPItens['pedido_id']}");
                endif;
            endif;

            if ($ReadPItens['item_tipo_cesta'] == 'avulso'):
                $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$ReadPItens['pedido_id']}&if=avulso");
                $Avulso = $Read->getResult();
                if ($Avulso):
                    $TotalPedido = $Read->getResult()[0]['totalPedido'];
                    $ArrUpPedidoAv = ['pedido_avulso' => $TotalPedido];
                    $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoAv, "WHERE pedido_id = :pi", "pi={$ReadPItens['pedido_id']}");
                endif;
            endif;

            if (!empty($PostData['item_valor'])):
                $jSON['itemValor'] = "(Total: R$ " . number_format($TotalPedido, 2, ",", ".") . ")";
            endif;
            break;

        //CRIA UM PEDIDO PARA CONSUMIDOR QUE NÃO TEM CESTA NO DIA - ESTILO AVULSO
        case 'criarCesta':
            $Read->FullRead("SELECT id, endereco FROM " . DB_CONSUMIDORES . " WHERE consumidor = :n", "n={$PostData['consumidor']}");
            $consumidor = $Read->getResult();
            if ($consumidor):
                $Read->ExeRead(DB_PD_CONS, "WHERE consumidor_id = :c AND pedido_data = :d", "c={$consumidor[0]['id']}&d={$PostData['data']}");
                if ($Read->getResult()):
                    $jSON['redirect'] = "index.php?lbs=avulso&id={$Read->getResult()[0]['pedido_id']}";
                else:
                    if ($consumidor):
                        $Cons = $consumidor[0];
                        $ArrConsumidor = ['consumidor_id' => $Cons['id'], 'pedido_endereco' => $Cons['endereco'], 'pedido_data' => $PostData['data']];
                        $Create->ExeCreate(DB_PD_CONS, $ArrConsumidor);
                        $jSON['redirect'] = "index.php?lbs=avulso&id={$Create->getResult()}";
                    endif;
                endif;
            endif;

            break;

        //CRIAR NOVO ITEM NO PEDIDO
        case 'criarItem':
            if (empty($PostData['item_produto']) OR empty($PostData['item_qtde']) OR empty($PostData['item_valor']) OR empty($PostData['item_freq_cesta'])):
                $vazio = (empty($PostData['item_produto']) ? '<b>Produto</b>' : (empty($PostData['item_qtde']) ? '<b>Quantidade</b>' : (empty($PostData['item_valor']) ? '<b>Valor</b>' : (empty($PostData['item_freq_cesta']) ? '<b>Frequência</b>' : ''))));
                $jSON['trigger'] = AjaxErro("Oooooops: é necessário preencher o campo {$vazio} para adicionar o item!", E_USER_ERROR);
                break;
            else:
                if (!isset($PostData['item_id'])):
                    $wItemId[0] = '';
                    $wItemId[1] = '';
                else:
                    $wItemId[0] = ' AND item_id != :item';
                    $wItemId[1] = "&item={$PostData['item_id']}";
                endif;

                //VERIFICA SE O PRODUTO JÁ EXISTE COM A MESMA FREQUÊNCIA QUE ESTÁ CADASTRANDO
                $Read->ExeRead(DB_PD_CONS_ITENS, "WHERE pedido_id = :pi AND produto_id = :pp AND item_freq_cesta = :fr AND item_qtde = :iq {$wItemId[0]}", "pi={$PostData['pedido_id']}&pp={$PostData['item_produto']}&fr={$PostData['item_freq_cesta']}&iq={$PostData['item_qtde']}{$wItemId[1]}");
                if ($Read->getResult()):
                    $jSON['trigger'] = AjaxErro("Oooooops: Esse produto já existe neste pedido, revise para adicionar um item novo!", E_USER_ERROR);
                    break;
                else:
                    //CASO NÃO EXISTA, BUSCA OS DADOS DO PRODUTO
                    $Read->FullRead("SELECT a.nome, a.preco, a.preco_mercado, a.preco_lojinha, a.preco_produtor, a.unidade2, a.multiplicador_unidade2, "
                            . "b.id AS idProdutor, c.id AS idUnidade "
                            . "FROM " . DB_PRODUTO . " AS a "
                            . "LEFT JOIN " . DB_PRODUTORES . " AS b "
                            . "ON a.produtor = b.Produtor "
                            . "LEFT JOIN " . DB_UNIDADE . " AS c "
                            . "ON a.unidade = c.unidade "
                            . "WHERE a.id = :id ", "id={$PostData['item_produto']}");

                    $item_produto = $Read->getResult()[0]['nome'];
                    $item_produtor = $Read->getResult()[0]['idProdutor'];
                    $item_valor_produtor = $Read->getResult()[0]['preco_produtor'];

                    //DEFINE O VALOR DO ITEM CONFORME A FAIXA DE PREÇO
                    $multiplicador = $Read->getResult()[0]['multiplicador_unidade2'];
                    if ($PostData['item_valor'] == 'comunidade'):
                        $item_valor = $Read->getResult()[0]['preco'];
                    elseif ($PostData['item_valor'] == 'mercado'):
                        $item_valor = $Read->getResult()[0]['preco_mercado'];
                    elseif ($PostData['item_valor'] == 'lojinha'):
                        $item_valor = $Read->getResult()[0]['preco_lojinha'];
                    elseif ($PostData['item_valor'] == 'cortesia'):
                        $item_valor = 0;
                    endif;

                    //DEFINE O TIPO DE CESTA CONFORME A FREQUENCIA
                    $tipoCesta = ($PostData['item_freq_cesta'] == 'variavel' ? 'variavel' : ($PostData['item_freq_cesta'] == 'avulso' ? 'avulso' : ($PostData['item_freq_cesta'] == 'mensal' ? 'fixa' : 'fixa')));

                    //CRIA A ARRAY QUE VAI CADASTRAR OU ATUALIZAR O ITEM
                    $ArrCriarItem = [
                        'pedido_id' => $PostData['pedido_id'],
                        'admin_id_editar' => $PostData['admin_id_editar'],
                        'produto_id' => $PostData['item_produto'],
                        'item_tipo' => $Read->getResult()[0]['idUnidade'],
                        'item_qtde' => str_replace(',', '.', $PostData['item_qtde']),
                        'item_produto' => $item_produto,
                        'item_valor' => $item_valor,
                        'item_produtor' => $item_produtor,
                        'item_valor_produtor' => $item_valor_produtor,
                        'item_tipo_cesta' => $tipoCesta,
                        'item_freq_cesta' => $PostData['item_freq_cesta'],
                        'pedido_data_criado' => date('Y-m-d H:i:s')
                    ];

                    //CONSULTA OS DADOS DO PEDIDO
                    $Read->ExeRead(DB_PD_CONS, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
                    $Pedido = $Read->getResult();

                    //CONDIÇÃO PARA CADASTRAR OS DADOS
                    if (!isset($PostData['item_id'])):
                        //CRIA O ITEM
                        $Create->ExeCreate(DB_PD_CONS_ITENS, $ArrCriarItem);
                        if ($Create->getResult()):
                            $jSON['itemId'] = $Create->getResult();
                            $jSON['trigger'] = AjaxErro("( √ ) <b>{$item_produto} salvo!</b>", E_USER_NOTICE);
                        endif;
                    else:
                        //ATUALIZA O ITEM
                        $Update->ExeUpdate(DB_PD_CONS_ITENS, $ArrCriarItem, "WHERE item_id = :ii", "ii={$PostData['item_id']}");
                        $jSON['trigger'] = AjaxErro("( √ ) <b>{$item_produto} atualizado!</b>", E_USER_NOTICE);
                    endif;

                    //SOMA A CESTA EQUIVALENTE AO TIPO DE ITEM CRIADO
                    if ($tipoCesta == 'fixa'):
                        if ($PostData['item_freq_cesta'] == 'mensal'):
                            $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_freq_cesta = :if", "pi={$PostData['pedido_id']}&if=mensal");
                            if ($Read->getResult()):
                                $TotalPedido = $Read->getResult()[0]['totalPedido'];
                                $ArrUpPedidoFi = ['pedido_mensal' => $TotalPedido];
                                $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
                            endif;
                        else:
                            $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :it AND  item_freq_cesta != :if", "pi={$PostData['pedido_id']}&it=fixa&if=mensal");
                            if ($Read->getResult()):
                                $TotalPedido = $Read->getResult()[0]['totalPedido'];
                                $ArrUpPedidoFi = ['pedido_fixa' => $TotalPedido];
                                $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
                            endif;
                        endif;

                    elseif ($tipoCesta == 'variavel'):
                        $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$PostData['pedido_id']}&if=variavel");
                        if ($Read->getResult()):
                            $TotalPedido = $Read->getResult()[0]['totalPedido'];
                            $ArrUpPedidoVa = ['pedido_variavel' => $TotalPedido];
                            $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoVa, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
                        endif;
                    elseif ($tipoCesta == 'avulso'):
                        $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$PostData['pedido_id']}&if=avulso");
                        if ($Read->getResult()):
                            $TotalPedido = $Read->getResult()[0]['totalPedido'];
                            $ArrUpPedidoAv = ['pedido_avulso' => $TotalPedido];
                            $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoAv, "WHERE pedido_id = :pi", "pi={$PostData['pedido_id']}");
                        endif;
                    endif;
                endif;
            endif;
            break;

        //EXCLUIR A CESTA INTEIRA
        case 'deleteCesta':
            $Read->FullRead("SELECT consumidor FROM " . DB_CONSUMIDORES . " AS a INNER JOIN " . DB_PD_CONS . " AS b ON a.id = b.consumidor_id WHERE b.pedido_id = :id", "id={$PostData['del_id']}");
            $consDel = '';
            if ($Read->getResult()):
                $consDel = " de {$Read->getResult()[0]['consumidor']}";
            endif;

            $Delete->ExeDelete(DB_PD_CONS_ITENS, "WHERE pedido_id = :pi", "pi={$PostData['del_id']}");
            $Delete->ExeDelete(DB_PD_CONS, "WHERE pedido_id = :pi", "pi={$PostData['del_id']}");

            $jSON['recado'] = AjaxErro("Cesta{$consDel} excluída com sucesso!", E_USER_NOTICE);
            $jSON['sucesso'] = true;
            break;

        //EXCLUIR UM ITEM DA CESTA
        case 'deleteItem':
            $Read->FullRead("SELECT item_valor, item_qtde, item_tipo_cesta, item_freq_cesta, item_produto, pedido_id FROM " . DB_PD_CONS_ITENS . " WHERE item_id = :id", "id={$PostData['del_id']}");
            $itemDel = '';
            $PdItens = $Read->getResult();
            if ($PdItens):
                $PedId = $PdItens[0]['pedido_id'];
                $CestaTipo = $PdItens[0]['item_tipo_cesta'];
                $itemDel = ": {$PdItens[0]['item_produto']}";
                $Delete->ExeDelete(DB_PD_CONS_ITENS, "WHERE item_id = :pi", "pi={$PostData['del_id']}");
                $jSON['recado'] = AjaxErro("Item{$itemDel} excluído com sucesso!", E_USER_NOTICE);
                $jSON['sucesso'] = true;

                if ($CestaTipo == 'fixa'):
                    if ($PdItens[0]['item_freq_cesta'] == 'mensal'):
                        $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_freq_cesta = :if", "pi={$PdItens[0]['pedido_id']}&if=mensal");
                        if ($Read->getResult()):
                            $TotalPedido = $Read->getResult()[0]['totalPedido'];
                            $ArrUpPedidoFi = ['pedido_mensal' => $TotalPedido];
                            $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$PdItens[0]['pedido_id']}");
                        endif;
                    else:
                        $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :it AND  item_freq_cesta != :if", "pi={$PdItens[0]['pedido_id']}&it=fixa&if=mensal");
                        if ($Read->getResult()):
                            $TotalPedido = $Read->getResult()[0]['totalPedido'];
                            $ArrUpPedidoFi = ['pedido_fixa' => $TotalPedido];
                            $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoFi, "WHERE pedido_id = :pi", "pi={$PdItens[0]['pedido_id']}");
                        endif;
                    endif;

                elseif ($CestaTipo == 'variavel'):
                    $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$PedId}&if=variavel");
                    if ($Read->getResult()):
                        $TotalPedido = $Read->getResult()[0]['totalPedido'];
                        $ArrUpPedidoVa = ['pedido_variavel' => $TotalPedido];
                        $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoVa, "WHERE pedido_id = :pi", "pi={$PdItens[0]['pedido_id']}");
                    endif;

                elseif ($CestaTipo == 'avulso'):
                    $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$PedId}&if=avulso");
                    if ($Read->getResult()):
                        $TotalPedido = $Read->getResult()[0]['totalPedido'];
                        $ArrUpPedidoAv = ['pedido_avulso' => $TotalPedido];
                        $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedidoAv, "WHERE pedido_id = :pi", "pi={$PdItens[0]['pedido_id']}");
                    endif;
                endif;
            else:
                $jSON['recado'] = AjaxErro("Oooops: algo deu errado, parece você tentou excluir um item que não existe!", E_USER_NOTICE);
            endif;
            break;

        //CONSULTAR O VALOR DO PRODUTO
        case 'ProdutoValor':
            $Read->FullRead("SELECT preco, preco_mercado, preco_lojinha FROM " . DB_PRODUTO . " WHERE id = :id", "id={$PostData['id']}");
            if ($Read->getResult()):
                $PrecoPdt = $Read->getResult()[0];
                $options = "<option value=''>Selecione</option><option value='comunidade'>(R$ " . number_format($PrecoPdt['preco'], 2, ",", ".") . ") Preço de comunidade</option><option value='mercado'>(R$ " . number_format($PrecoPdt['preco_mercado'], 2, ",", ".") . ") Preço de mercado</option><option value='lojinha'>(R$ " . number_format($PrecoPdt['preco_lojinha'], 2, ",", ".") . ") Preço da lojinha</option><option value='cortesia'>(R$ " . number_format($PrecoPdt['cortesia'], 2, ",", ".") . ") Preço cortesia</option>";
            endif;
            $jSON['options'] = $options;
            break;

        //FILTROS DA MONTAGEM
        case 'filtroMontagem':
            $cesta_montagem = '';
            $FiltroRetorno = '';
            $wData[0] = $PostData["wData0"];
            $wData[1] = $PostData["wData1"];

            if (isset($PostData["consumidor"]) && !empty($PostData["consumidor"])):
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
                        . "INNER JOIN " . DB_PD_CONS_ITENS . " AS c "
                        . "ON a.pedido_id = c.pedido_id "
                        . "LEFT JOIN " . DB_CONSUMIDORES . " AS b "
                        . "ON a.consumidor_id = b.id "
                        . "LEFT JOIN " . DB_ENTREGA . " AS d "
                        . "ON a.pedido_retirada = d.id "
                        . "WHERE $wData[0] "
                        . "AND b.consumidor = :co "
                        . "GROUP BY a.pedido_id "
                        . "ORDER BY b.consumidor ASC ", "{$wData[1]}&co={$PostData["consumidor"]}");
                $itensPD = $Read->getResult();

                if (!$itensPD):
                    $jSON['trigger'] = AjaxErro("<span class='icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
                    $jSON['clear'] = true;
                    echo json_encode($jSON);
                    return;
                else:
                    $i = 0;
                    foreach ($itensPD as $Itens):
                        $i++;
                        extract($Itens);
                        $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                                . "LEFT JOIN " . DB_PD_CONS . " AS b "
                                . "ON a.pedido_id = b.pedido_id "
                                . "LEFT JOIN " . DB_ADMIN . " AS c "
                                . "ON a.admin_id_editar = c.id OR "
                                . "a.admin_id_separar = c.id OR "
                                . "a.admin_id_verificar = c.id "
                                . "LEFT JOIN " . DB_PRODUTO . " AS d "
                                . "ON a.produto_id = d.id "
                                . "LEFT JOIN " . DB_UNIDADE . " AS e "
                                . "ON a.item_tipo = e.id "
                                . "WHERE a.pedido_id = :pi "
                                . "GROUP BY a.item_id "
                                . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}");
                        $ReadPrincipal = $Read->getResult();
                        require '../montagem/mont_cesta.php';
                        $FiltroRetorno .= $cesta_montagem;
                    endforeach;
                endif;
            endif;

            if (isset($PostData["produto"]) && !empty($PostData["produto"])):
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS c "
                        . "LEFT JOIN " . DB_PD_CONS . " AS a "
                        . "ON a.pedido_id = c.pedido_id "
                        . "LEFT JOIN " . DB_CONSUMIDORES . " AS b "
                        . "ON a.consumidor_id = b.id "
                        . "LEFT JOIN " . DB_ENTREGA . " AS d "
                        . "ON a.pedido_retirada = d.id "
                        . "WHERE a.$wData[0] AND c.item_tipo_cesta != 'pre' "
                        . "AND c.item_produto = :ip "
                        . "GROUP BY a.consumidor_id "
                        . "ORDER BY b.consumidor ASC ", "{$wData[1]}&ip={$PostData["produto"]}");
                $itensPD = $Read->getResult();
                if (!$itensPD):
                    $jSON['trigger'] = AjaxErro("<span class='icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
                    $jSON['clear'] = true;
                    echo json_encode($jSON);
                    return;
                else:
                    $Read->FullRead("SELECT COUNT(a.pedido_id) AS cestas, COUNT(b.item_id) AS itens "
                            . "FROM " . DB_PD_CONS_ITENS . " AS b "
                            . "LEFT JOIN " . DB_PD_CONS . " AS a "
                            . "ON b.pedido_id = a.pedido_id "
                            . "WHERE $wData[0] "
                            . "AND b.item_tipo_cesta != 'pre' "
                            . "AND b.item_produto = :ip", "{$wData[1]}&ip={$PostData["produto"]}");
                    if ($Read->getResult()):
                        $PedidosTotal = $Read->getResult()[0]['cestas'];
                        $PedidosItens = $Read->getResult()[0]['itens'];

                        $total = "<div style='font-size:.8rem; text-align:center; color:#555;'>Pesquisa por <b>{$PostData["produto"]}</b> tem como resultado {$PedidosTotal} cestas  num total de {$PedidosItens} itens</div>";
                    endif;
                    $i = 0;
                    foreach ($itensPD as $Itens):
                        $i++;
                        extract($Itens);
                        $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                                . "LEFT JOIN " . DB_PD_CONS . " AS b "
                                . "ON a.pedido_id = b.pedido_id "
                                . "LEFT JOIN " . DB_ADMIN . " AS c "
                                . "ON a.admin_id_editar = c.id OR "
                                . "a.admin_id_separar = c.id OR "
                                . "a.admin_id_verificar = c.id "
                                . "LEFT JOIN " . DB_PRODUTO . " AS d "
                                . "ON a.produto_id = d.id "
                                . "LEFT JOIN " . DB_UNIDADE . " AS e "
                                . "ON a.item_tipo = e.id "
                                . "WHERE a.pedido_id = :pi "
                                . "AND a.item_produto = :ip "
                                . "GROUP BY a.item_id "
                                . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}&ip={$PostData["produto"]}");
                        $ReadPrincipal = $Read->getResult();
                        require '../montagem/mont_cesta.php';
                        $FiltroRetorno .= $cesta_montagem;
                    endforeach;
                endif;
            endif;

            if (isset($PostData["retirada"]) && !empty($PostData["retirada"])):
                $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
                        . "INNER JOIN " . DB_CONSUMIDORES . " AS b "
                        . "ON a.consumidor_id = b.id "
                        . "INNER JOIN " . DB_ENTREGA . " AS c "
                        . "ON a.pedido_retirada = c.id "
                        . "WHERE $wData[0] AND a.pedido_pre = 0 "
                        . "AND a.pedido_retirada = :pr "
                        . "ORDER BY b.consumidor ASC ", "{$wData[1]}&pr={$PostData["retirada"]}");
                $itensPD = $Read->getResult();

                if (!$itensPD):
                    $jSON['trigger'] = AjaxErro("<span class='icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
                    $jSON['clear'] = true;
                    echo json_encode($jSON);
                    return;
                else:
                    $Read->FullRead("SELECT COUNT(DISTINCT(a.pedido_id)) AS cestas, COUNT(DISTINCT(b.item_id)) AS itens, c.descricao_entrega "
                            . "FROM " . DB_PD_CONS_ITENS . " AS b "
                            . "LEFT JOIN " . DB_PD_CONS . " AS a "
                            . "ON b.pedido_id = a.pedido_id "
                            . "LEFT JOIN " . DB_ENTREGA . " AS c "
                            . "ON a.pedido_retirada = c.id "
                            . "WHERE $wData[0] "
                            . "AND b.item_tipo_cesta != 'pre' "
                            . "AND a.pedido_retirada = :pr", "{$wData[1]}&pr={$PostData["retirada"]}");
                    if ($Read->getResult()):
                        $PedidosTotal = $Read->getResult()[0]['cestas'];
                        $PedidosItens = $Read->getResult()[0]['itens'];

                        $total = "<div style='font-size:.8rem; text-align:center; color:#555;'>Forma de entrega: <b>{$Read->getResult()[0]["descricao_entrega"]}</b> tem como resultado {$PedidosTotal} cestas  num total de {$PedidosItens} itens</div>";
                    endif;
                    $i = 0;
                    foreach ($itensPD as $Itens):
                        $i++;
                        extract($Itens);
                        $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                                . "LEFT JOIN " . DB_PD_CONS . " AS b "
                                . "ON a.pedido_id = b.pedido_id "
                                . "LEFT JOIN " . DB_ADMIN . " AS c "
                                . "ON a.admin_id_editar = c.id OR "
                                . "a.admin_id_separar = c.id OR "
                                . "a.admin_id_verificar = c.id "
                                . "LEFT JOIN " . DB_PRODUTO . " AS d "
                                . "ON a.produto_id = d.id "
                                . "LEFT JOIN " . DB_UNIDADE . " AS e "
                                . "ON a.item_tipo = e.id "
                                . "WHERE a.pedido_id = :pi "
                                . "GROUP BY a.item_id "
                                . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}");
                        $ReadPrincipal = $Read->getResult();
                        require '../montagem/mont_cesta.php';
                        $FiltroRetorno .= $cesta_montagem;
                    endforeach;
                endif;
            endif;

            if (isset($PostData["montagem"]) && !empty($PostData["montagem"])):
                $wMontagem = ($PostData["montagem"] == 1 ? 'item_separado = 2' : ($PostData["montagem"] == 2 ? 'item_separado < 2' : ($PostData["montagem"] == 3 ? 'item_verificado = 2' : ($PostData["montagem"] == 4 ? 'item_verificado < 2' : '' ))));
                $wMontagem2 = ($PostData["montagem"] == 1 ? 'Todas cestas com itens separados' : ($PostData["montagem"] == 2 ? 'Cestas que não foram separadas' : ($PostData["montagem"] == 3 ? 'Todas cestas que já foram verificadas' : ($PostData["montagem"] == 4 ? 'Cestas que não foram verificadas' : '' ))));

                $Read->FullRead("SELECT * FROM " . DB_PD_CONS . " AS a "
                        . "RIGHT JOIN " . DB_PD_CONS_ITENS . " AS c "
                        . "ON a.pedido_id = c.pedido_id "
                        . "INNER JOIN " . DB_CONSUMIDORES . " AS b "
                        . "ON a.consumidor_id = b.id "
                        . "INNER JOIN " . DB_ENTREGA . " AS d "
                        . "ON a.pedido_retirada = d.id "
                        . "WHERE $wData[0] "
                        . "AND c.{$wMontagem} "
                        . "GROUP BY a.pedido_id "
                        . "ORDER BY b.consumidor ASC ", "{$wData[1]}");
                $itensPD = $Read->getResult();

                if (!$itensPD):
                    $jSON['trigger'] = AjaxErro("<span class='icon-notification'>Olá, não existe nenhum pedido!</span>", E_USER_NOTICE);
                    $jSON['clear'] = true;
                    echo json_encode($jSON);
                    return;
                else:
                    $Read->FullRead("SELECT a.pedido_id FROM " . DB_PD_CONS . " AS a "
                            . "RIGHT JOIN " . DB_PD_CONS_ITENS . " AS c "
                            . "ON a.pedido_id = c.pedido_id "
                            . "WHERE $wData[0] AND c.{$wMontagem} "
                            . "GROUP BY a.pedido_id ", "{$wData[1]}");
                    if ($Read->getResult()):
                        $EntregaTotal = $Read->getResult();
                        $PedidosTotal = $Read->getRowCount();
                        $PedidosItens = 0;
                        foreach ($Read->getResult() as $ItemT):
                            $Read->FullRead("SELECT item_id FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi", "pi={$ItemT['pedido_id']}");
                            $PedidosItens += $Read->getRowCount();
                        endforeach;

                        $total = "<div style='font-size:.8rem; text-align:center; color:#555;'>Pesquisa por <b>{$wMontagem2}</b> tem como resultado {$PedidosTotal} cestas  num total de {$PedidosItens} itens</div>";
                    endif;
                    $i = 0;
                    foreach ($itensPD as $Itens):
                        $i++;
                        extract($Itens);
                        $Read->FullRead("SELECT * FROM " . DB_PD_CONS_ITENS . " AS a "
                                . "LEFT JOIN " . DB_PD_CONS . " AS b "
                                . "ON a.pedido_id = b.pedido_id "
                                . "LEFT JOIN " . DB_ADMIN . " AS c "
                                . "ON a.admin_id_editar = c.id OR "
                                . "a.admin_id_separar = c.id OR "
                                . "a.admin_id_verificar = c.id "
                                . "LEFT JOIN " . DB_PRODUTO . " AS d "
                                . "ON a.produto_id = d.id "
                                . "LEFT JOIN " . DB_UNIDADE . " AS e "
                                . "ON a.item_tipo = e.id "
                                . "WHERE a.pedido_id = :pi "
                                . "GROUP BY a.item_id "
                                . "ORDER BY d.nome, a.item_id", "pi={$pedido_id}");
                        $ReadPrincipal = $Read->getResult();
                        require '../montagem/mont_cesta.php';
                        $FiltroRetorno .= $cesta_montagem;
                    endforeach;
                endif;
            endif;

            if (!empty($FiltroRetorno)):
                $jSON['montagem'] = $FiltroRetorno;
                $jSON['clear'] = true;
            endif;
            if (isset($total)):
                $jSON['content'] = $total;
            endif;
            break;

        //CADASTRAR OU ATUALIZAR PRODUTOS DA PRÉ COMUNIDADE
        case 'editarPre':
            $IdAdmin = $PostData['admin_id_editar'];
            $IdPedido = $PostData['pedido_id'];
            unset($PostData['admin_id_editar'], $PostData['pedido_id'], $PostData['Filtro']);

            $ArrCreate = array();
            $ArrUpdatePdt = array();
            $ArrCreatePdt = array();
            $ArrDeletePdt = array();
            $UpBoolean = false;

            foreach ($PostData['produto_id'] as $k => $v):

                $Read->FullRead("SELECT item_id, item_qtde, item_valor FROM " . DB_PD_CONS_ITENS . " AS a WHERE a.pedido_id = :pi AND a.produto_id = :pdi", "pi={$IdPedido}&pdi={$v}");
                $itemPre = $Read->getResult();

                if (!empty($PostData['item_qtde'][$k])):
                    if (!empty($itemPre)):
                        $QtdItem = str_replace(',', '.', $PostData['item_qtde'][$k]);
                        if ($itemPre[0]['item_qtde'] != $QtdItem):
                            $ArrUp = ['item_qtde' => $QtdItem];
                            $Update->ExeUpdate(DB_PD_CONS_ITENS, $ArrUp, "WHERE pedido_id = :pi AND produto_id = :pdi", "pi={$IdPedido}&pdi={$v}");
                            $UpBoolean = true;
                        endif;
                    else:
                        $Read->FullRead("SELECT a.nome, a.preco_pre AS preco, b.id AS idProdutor, a.preco_produtor, "
                                . "c.id AS Unidade FROM " . DB_PRODUTO . " AS a "
                                . "LEFT JOIN " . DB_PRODUTORES . " AS b "
                                . "ON a.produtor = b.Produtor "
                                . "LEFT JOIN " . DB_UNIDADE . " AS c "
                                . "ON a.unidade2 = c.unidade "
                                . "WHERE a.id = :id", "id={$v}");
                        $Produto = $Read->getResult()[0];

                        $ArrCreate[] = [
                            'pedido_id' => $IdPedido,
                            'admin_id_editar' => $IdAdmin,
                            'produto_id' => $v,
                            'item_tipo' => $Produto['Unidade'],
                            'item_qtde' => str_replace(',', '.', $PostData['item_qtde'][$k]),
                            'item_produto' => $Produto['nome'],
                            'item_valor' => $Produto['preco'],
                            'item_produtor' => $Produto['idProdutor'],
                            'item_valor_produtor' => $Produto['preco_produtor'],
                            'item_tipo_cesta' => 'pre',
                            'item_freq_cesta' => 'pre'
                        ];
                    endif;
                elseif ($PostData['item_qtde'][$k] == '0'):
                    if (isset($itemPre[0]['item_id'])):
                        $Delete->ExeDelete(DB_PD_CONS_ITENS, "WHERE item_id = :ii", "ii={$itemPre[0]['item_id']}");
                        $UpBoolean = true;
                    endif;
                endif;
            endforeach;

            if ($ArrCreate):
                $Create->ExeCreateMulti(DB_PD_CONS_ITENS, $ArrCreate);
                $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi", "pi={$IdPedido}");
                if ($Read->getResult()):
                    $TotalPedido = $Read->getResult()[0]['totalPedido'];

                    $ArrUpPedido = ['pedido_pre' => $TotalPedido];
                    $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedido, "WHERE pedido_id = :pi", "pi={$IdPedido}");
                    $jSON['trigger'] = AjaxErro("( √ ) <b>Cesta da pré comunidade cadastrada com sucesso! O valor total foi de R$ " . number_format($TotalPedido, '2', ',', '.') . "</b>", E_USER_NOTICE);
                endif;
            endif;

            if ($UpBoolean == true):
                $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi", "pi={$IdPedido}");
                if ($Read->getResult()):
                    $TotalPedido = $Read->getResult()[0]['totalPedido'];

                    $ArrUpPedido = ['pedido_pre' => $TotalPedido];
                    $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedido, "WHERE pedido_id = :pi", "pi={$IdPedido}");
                    $jSON['trigger'] = AjaxErro("( √ ) <b>Cesta da pré comunidade atualizada com sucesso! O valor total foi para R$ " . number_format($TotalPedido, '2', ',', '.') . "</b>", E_USER_NOTICE);
                endif;
            endif;

            if (empty($ArrCreate) && $UpBoolean == false):
                $jSON['trigger'] = AjaxErro("Nada para atualizar! :)", E_USER_NOTICE);
            endif;
            break;
    endswitch;

    //RETORNA O CALLBACK
    if ($jSON):
        echo json_encode($jSON);
    else:
        $jSON['trigger'] = AjaxErro('<b class="icon-warning">OPSS:</b> Desculpe. Mas uma ação do sistema não respondeu corretamente!', E_USER_ERROR);
        echo json_encode($jSON);
    endif;
else:
    //ACESSO DIRETO
    die('<br><br><br><center><h1>Acesso Restrito!</h1></center>');
endif;
