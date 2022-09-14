<?php

class Consumidores extends Livres {

    //protected $dataEntrega = "";

    public function encontrarPorId($id) {
        $sql = "SELECT * FROM Consumidores WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
    
        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            return $rs;
        } else {
            return false;
        }
    }

    public function encontrarPorCPF($cpf) {
        $sql = "SELECT * FROM Consumidores WHERE cpf = ".$cpf;
        $st = $this->conn()->prepare($sql);
        $st->execute();
    
        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            return $rs;
        } else {
            return false;
        }
    }
    
    public function consumidoresAtivos($ordenar = "") {
        return $this->listaConsumidores('ativos', $ordenar);
    }
    
    public function consumidoresInativos() {
        return $this->listaConsumidores('inativos', $ordenar);
    }
    
    public function consumidoresTodos($ordenar = "") {
        return $this->listaConsumidores('', $ordenar);
    }

    private function listaConsumidores($tipo, $ordenar = "") {
        $sql = "SELECT * FROM Consumidores";
        if ($tipo == 'ativos') {
            $sql .= " WHERE ativo = 1";
        }
        if ($tipo == 'inativos') {
            $sql .= " WHERE ativo = 0";
        }
        if ($ordenar == "") {
            $sql .= " ORDER BY consumidor, ativo, comunidade ASC";
        } else {
            $sql .= " ORDER BY ".$ordenar." ASC";
        }
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();

            $sql = "DESCRIBE Consumidores";            
            $st = $this->conn()->prepare($sql);
            $st->execute();
            $colunas = $st->fetchAll(PDO::FETCH_COLUMN);

            foreach ($rs as $row) {
                foreach ($colunas as $coluna) {
                    $valor = $row[$coluna];
                    $consumidores[$row["id"]][$coluna] = $valor;
                }
            }
            return $consumidores;
        } else {
            return false;
        }
    }

    public function atualizarCadastro($id, $dados) {
        if (!is_array($dados)) return false;

        if (!$this->encontrarPorId($id)) return false;

        $campos = "";
        foreach ($dados as $campo => $valor) {
            if ($campos != "") $campos .= ", ";
            $campos .= $campo." = ?";
            $valores[] = $valor;
        }

        $sql = "UPDATE Consumidores SET " . $campos;
        $sql .= " WHERE id = ".$id;

        $st = $this->conn()->prepare($sql);
        if (!$st->execute($valores)) return false;
        return 4;
    }

    public function proximoPedidoConsumidor($id, $dataAtual) {
        $sql = "SELECT * FROM pedidos_consolidados ped LEFT JOIN pedidos_consolidados_itens it ";
        $sql .= "ON ped.pedido_id = it.pedido_id WHERE ped.consumidor_id = ".$id." AND pedido_data > '".$dataAtual."'";

        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() < 1) return false;

        return $st->fetchAll();
    }

    public function proximoPedidoVariavelConsumidor($id, $dataAtual) {
        $sql = "SELECT * FROM pedidos_consolidados ped LEFT JOIN pedidos_consolidados_itens it ";
        $sql .= "ON ped.pedido_id = it.pedido_id WHERE ped.consumidor_id = ".$id." AND pedido_data > '".$dataAtual."'";
        $sql .= " AND item_tipo_cesta = 'variavel'";

        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() < 1) return false;

        return $st->fetchAll();
    }
}
?>