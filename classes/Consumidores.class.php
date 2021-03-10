<?php

class Consumidores extends Livres {

    //protected $dataEntrega = "";

    public function encontrarPorId($id) {
        $sql = "SELECT * FROM consumidores WHERE id = ".$id;
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
        $sql = "SELECT * FROM consumidores WHERE cpf = ".$cpf;
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
    
    public function consumidoresTodos() {
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
}
?>