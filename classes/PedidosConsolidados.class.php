<?php
class PedidosConsolidados extends Livres {

    function __construct($dataEntrega) {
        if (!is_numeric($dataEntrega)) {
            $this->dataEntrega = $this->dataParaTime($dataEntrega);
        } else {
            $this->dataEntrega = $dataEntrega;
        }
    }

    public function valorPedidoPre() {
        $sql = "SELECT pedido_pre FROM pedidos_consolidados ped
                WHERE ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'
                AND ped.pedido_endereco = 'pre'";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetch();
        return $rs["pedido_pre"];
    }

    public function listaPedidos($colunas = "*") {
        $colunasConv = $colunas;
        if (is_array($colunasConv)) {
            $colunasConv = join(", ",$colunasConv);
        }

        $sql = "SELECT ".$colunasConv." FROM pedidos_consolidados ped
                LEFT JOIN Consumidores cons ON ped.consumidor_id = cons.id
                LEFT JOIN FormaEntrega fe ON fe.id = ped.pedido_retirada
                WHERE ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'
                AND consumidor_id IS NOT NULL
                ORDER BY cons.consumidor ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        if (is_array($colunas) || $colunas === "*") {
            $rs = $st->fetchAll();
        } else {
            $rs = $st->fetchAll(PDO::FETCH_COLUMN);
        }
        return $rs;
    }

    public function listaItensTodos() {
        /*$sql = "SELECT * FROM pedidos_consolidados_itens it LEFT JOIN pedidos_consolidados ped
                ON it.pedido_id = ped.pedido_id
                WHERE ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'";*/
        $sql = "SELECT * FROM pedidos_consolidados_itens it LEFT JOIN pedidos_consolidados ped
                ON it.pedido_id = ped.pedido_id
                WHERE ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'";
        return $this->listaItens($sql);
    }

    public function listaItensFixos() {
        $sql = "SELECT * FROM pedidos_consolidados_itens it LEFT JOIN pedidos_consolidados ped
                ON it.pedido_id = ped.pedido_id
                WHERE item_tipo_cesta = 'fixa' AND ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'";
        return $this->listaItens($sql);
    }

    public function listaItensVariaveis() {
        $sql = "SELECT * FROM pedidos_consolidados_itens it LEFT JOIN pedidos_consolidados ped
                ON it.pedido_id = ped.pedido_id
                WHERE item_tipo_cesta = 'variavel' AND ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'";
        return $this->listaItens($sql);
    }

    public function listaItensAvulsos() {
        $sql = "SELECT * FROM pedidos_consolidados_itens it LEFT JOIN pedidos_consolidados ped
                ON it.pedido_id = ped.pedido_id
                WHERE item_tipo_cesta = 'avulso' AND ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."'";
        return $this->listaItens($sql);
    }

    private function listaItens($sql) {
        $sql .= " ORDER BY it.item_tipo_cesta, it.item_produto ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();        
        return $rs;
    }

    public function pedidoCompletoPorConsumidor() {
        $corrigirValorTotal = $this->pedidoCorrigirValorTotal();
        $pedidos = $this->listaPedidos();
        $itensRS = $this->listaItensTodos();
        if (!is_array($pedidos) || !is_array($itensRS)) {
            return false;
        } else {
            $oConsumidores = new Consumidores();
            //Linha abaixo alterada em 11/09/20 porque consumidores porque o nome de consumidores desativados ap贸s a entrega n茫o estavam aparecendo na planilha de caixa
            //$consumidores = $oConsumidores->consumidoresAtivos();
            $consumidores = $oConsumidores->consumidoresTodos();

            $oProdutos = new Produtos();
            $produtos = $oProdutos->listarProdutosTodos();

            //transformar array de itens para poder agrupar por ordem
            foreach ($itensRS as $item) {
                $itens[$item["pedido_id"]][] = $item;
            }
            
            //varrer pedidos e mostrar itens
            $index = 0;
            foreach ($pedidos as $pedido) {
                $conteudo[$index]["consumidor"] = $consumidores[$pedido["consumidor_id"]];
                $conteudo[$index]["pedido"] = $pedido;

                foreach ($itens[$pedido["pedido_id"]] as $item) {
                    $conteudo[$index]["itens"][] = [
                        "item" => $item,
                        "produto" => $produtos[$item["produto_id"]]
                    ];

                }
                $index++;
            }
            return $conteudo;
        }
    }
    
    //Essa função foi criada porque por algum motivo, o trigger do MySQL não está funcionando corretamente e não está atualizando o valor total do pedido dos consumidores.
    //Sendo assim, ao consultar um pedido, checamos se algum consumidor está com total "null" e forçamos um update no pedido sem alterar nada para disparar o trigger
    private function pedidoCorrigirValorTotal() {
        $sql = "UPDATE pedidos_consolidados SET pedido_mensal = pedido_mensal WHERE ped.pedido_data = '".date('Y-m-d H:i:s',$this->dataEntrega)."' AND pedido_valor_total IS NULL";
        $st = $this->conn()->prepare($sql);
        if ($st->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
}
?>