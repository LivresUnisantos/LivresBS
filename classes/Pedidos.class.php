<?php
class Pedidos extends Livres {

    public $opcoesVariaveis = 2;

    public function listarPedidoFixo($id = "") {

        $sql = "SELECT * FROM Pedidos";
        $sql .= " WHERE Quantidade > 0";
        if ($id != "") {
            $sql .= " AND IDConsumidor = ".$id;
        }
        $sql .= " ORDER BY IDConsumidor";
        $st = $this->conn()->prepare($sql);
        $st->execute();    
        if ($st->rowCount() > 0)  {
            $cestas = $st->fetchAll();
            return $cestas;
        }
    }

    public function listarPedidoFixoSemana($dataEntrega) {

        $frequencias = $this->frequenciasEntrega($dataEntrega);
        $sql = "SELECT ped.IDConsumidor, ped.IDProduto, ped.Frequencia, ped.Quantidade, cons.comunidade, cons.id, cons.ativo,
                prod.previsao, prod.id
                FROM Pedidos ped LEFT JOIN Consumidores cons
                ON cons.id = ped.IDConsumidor
                LEFT JOIN produtos prod
                ON prod.id = ped.IDProduto
                WHERE ped.Quantidade > 0 AND cons.ativo = 1
                AND prod.previsao <= '".date('Y-m-d',$dataEntrega)."'
                ";
        $st = $this->conn()->prepare($sql);
        $st->execute();    
        if ($st->rowCount() > 0)  {
            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                if (!is_null($row["comunidade"]) &&  strlen($row["comunidade"]) > 0 && $row["comunidade"] > 0) {
                    if ($this->frequenciaNaSemana($frequencias[$row["comunidade"]],$row["Frequencia"])) {
                        $cestas[] = $row;                        
                    }
                }
            }
            return $cestas;
        }
    }

    public function listarPedidoVariavelSemana($dataEntrega) {
        $sql = "SELECT * FROM PedidosVar ped LEFT JOIN Calendario cal
                ON ped.idCalendario = cal.id        
                WHERE cal.data = ?";
        
        $st = $this->conn()->prepare($sql);
        $st->execute(array($this->dataTimeParaString($dataEntrega)));

        if ($st->rowCount() == 0) return false;
        
        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $contador = 0;
            for ($i = 1; $i <= $this->opcoesVariaveis; $i++) {
                if ($row["quantidadeOpcao".$i] > 0) {
                    if (!is_null($row["quantidadeOpcao".$i])) {
                        $variaveis[$row["idConsumidor"]]["item"][$contador]["idProduto"] = $row["escolhaOpcao".$i];
                        $variaveis[$row["idConsumidor"]]["item"][$contador]["quantidade"] = $row["quantidadeOpcao".$i];
                        $variaveis[$row["idConsumidor"]]["diferenca"] = $row["diferenca"];
                        $delivery = $row["delivery"];
                        /* ARMENG TEMPORÁRIO PARA CONVERTER O FORMATO ANTIGO DE DESCRIÇÃO DE DELIVERY PARA O NOVO */
                        if ($delivery == 'Não') { $delivery = 1; }
                        if ($delivery == 'Sim') { $delivery = 2; }
                        if ($delivery == 'Não sei ainda') { $delivery = 3; }
                        if (is_null($delivery)) { $delivery = 3; }
                        /* FIM ARMENG */
                        $variaveis[$row["idConsumidor"]]["delivery"] = $delivery;
                        $variaveis[$row["idConsumidor"]]["endereco_entrega"] = $row["endereco_entrega"];
                        $contador++;
                    }
                }
            }
        }
        if (!isset($variaveis)) return;
        return $variaveis;
    }
}
?>