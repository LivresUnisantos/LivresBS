<?php

$NivelAcess = LEVEL_SUPER_ADMIN;
if (empty($_SESSION['logado']) || empty($_SESSION['level']) || $_SESSION['level'] < $NivelAcess):
    Erro('<b class="icon-warning">OPPSSS:</b> Você não tem permissão para essa ação ou não está logado como administrador!', E_USER_ERROR);
    die;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
$Create = new Create;
$Update = new Update;

//$fallback = './';
//$anterior = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback);
//
//$wData[0] = "pedido_data = :start ";
//$wData[1] = "start={$StartDate}";


$Read->ExeRead(DB_PD_CONS);
if ($Read->getResult()):
    echo 'total = ' . $Read->getRowCount();
    foreach ($Read->getResult() as $Pedidos):
        extract($Pedidos);
        $pFixa = 0;
        $pVariavel = 0;
        $pAvulso = 0;
        $pPre = 0;
        $Read->FullRead("SELECT SUM(item_valor * item_qtde) AS totalPedido FROM " . DB_PD_CONS_ITENS . " WHERE pedido_id = :pi AND item_tipo_cesta = :if", "pi={$pedido_id}&if=fixa");
        $Fixa = $Read->getResult();
        if ($Fixa):
            $pFixa = $Read->getResult()[0]['totalPedido'];
        endif;

        $Read->setPlaces("pi={$pedido_id}&if=variavel");
        $Variavel = $Read->getResult();
        if ($Variavel):
            $pVariavel = $Read->getResult()[0]['totalPedido'];
        endif;

        $Read->setPlaces("pi={$pedido_id}&if=avulso");
        $Avulso = $Read->getResult();
        if ($Avulso):
            $pAvulso = $Read->getResult()[0]['totalPedido'];
        endif;

        $Read->setPlaces("pi={$pedido_id}&if=pre");
        $Pre = $Read->getResult();
        if ($Pre):
            $pPre = $Read->getResult()[0]['totalPedido'];
        endif;

        $ArrUpPedido = ['pedido_fixa' => $pFixa, 'pedido_variavel' => $pVariavel, 'pedido_avulso' => $pAvulso, 'pedido_pre' => $pPre];
        $Update->ExeUpdate(DB_PD_CONS, $ArrUpPedido, "WHERE pedido_id = :pi", "pi={$pedido_id}");
        echo '<pre>';
        var_dump($ArrUpPedido,$pedido_id);
        echo '</pre>';
    endforeach;
endif;