<?php
class Cestas extends Livres {

    function listaDatasComPedido($consumidorId) {
        $sql = "SELECT *, cal.id as data_id FROM pedidos_consolidados ped
                LEFT JOIN Calendario cal ON ped.pedido_data = cal.data
                WHERE ped.consumidor_id = ".$consumidorId.
                " ORDER BY cal.data DESC";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        return $rs;
    }

    function cestaPelaData($idConsumidor, $dataId) {
        $sql = "SELECT * FROM pedidos_consolidados_itens it
                LEFT JOIN pedidos_consolidados ped
                ON ped.pedido_id = it.pedido_id
                LEFT JOIN Calendario cal
                ON cal.data = ped.pedido_data
                LEFT JOIN unidades un
                ON un.id = it.item_tipo
                LEFT JOIN produtos prod
                ON prod.id = it.produto_id
                WHERE cal.id = ".$dataId."
                AND ped.consumidor_id = ".$idConsumidor."
                ORDER BY it.item_produto";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        return $rs;
    }

    function cestaCompromisso($idConsumidor, $dataId) {
        $oProdutos = new Produtos();
        $produtosAtivos = $oProdutos->listarProdutosAtivos(strtotime(date('Y-m-d')));
        $produtosTodos = $oProdutos->listarProdutosTodos();

        $oPedido = new Pedidos();
        $pedido = $oPedido->listarPedidoFixo($idConsumidor);

        if (!is_array($pedido)) return false;

        foreach ($pedido as $row) {
            if (array_key_exists($row["IDProduto"],$produtosAtivos)) {
                $tipoCesta = "atual";
                $produto = $produtosAtivos[$row["IDProduto"]];
            } else {
                $tipoCesta = "compromisso";
                $produto = $produtosTodos[$row["IDProduto"]];
            }
            $cesta[$tipoCesta][] = [
                "quantidade"    => $row["Quantidade"],
                "frequencia"    => mb_strtolower($row["Frequencia"], 'UTF-8'),
                "produto"       => $produto
            ];
        }
        return $cesta;
    }
}
?>