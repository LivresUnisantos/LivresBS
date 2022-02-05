<?php
class Ecoholerite extends Livres {

    public $idAdmin;
    
    //ID de atividades com tratamento específico no relatório
    public $idEntregas = 1;
    public $idReembolso = 29;
    public $idTracarRotas = 11;

    function __construct($idAdmin = "") {
        $this->idAdmin = $idAdmin;
    }

    public function atividadesExecutadas($id_ecoholerite = "") {
        $sql = "SELECT eh.id as id_ecoholerite, eh.valor as valor_receber, eh.*, ea.*, ad.* FROM Ecoholerites eh LEFT JOIN Ecoatividades ea";
        $sql .= " ON eh.id_atividade = ea.id ";
        $sql .= " LEFT JOIN Admins ad ON eh.id_admin = ad.id";
        if ($id_ecoholerite != "") {
            $sql .= " WHERE eh.id = ".$id_ecoholerite;
            if ($this->idAdmin != "") {
                $sql .= " AND ad.id = ".$this->idAdmin;
            }
        } else {
            if ($this->idAdmin != "") {
                $sql .= " WHERE ad.id = ".$this->idAdmin;
            }
        }
        $sql .= " ORDER BY data, status";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        if ($id_ecoholerite != "") {
            return $st->fetch();
        } else {
            return $st->fetchAll();
        }
            
    }
    
    public function listaAtividades($id = "") {
        $sql = "SELECT a.*, sum(c.aliquota) as descontos FROM Ecoatividades a";
        $sql .= " LEFT JOIN descontos_atividades b on (a.id = b.id_atividade and b.ativo = 1)";
        $sql .= " LEFT JOIN descontos_em_folha c on c.id = b.id_desconto";

        if ($id != "") {
            $sql .= " WHERE a.id = ".$id."";
        }
        
        $sql .= " GROUP BY a.id";
        $sql .= " ORDER BY descricao";
        
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        // foreach ($rs as $num=>$row) {
        //     $descontos = 0;
        //     foreach ($this->descontos($row["id"]) as $desconto) {
        //         $descontos += $desconto;
        //     }
        //     $rs[$num]["descontos"] = $descontos;
        // }
        return $rs;
    }
    
    public function listaPessoas($id = "") {
        if ($id == "") {
            $sql = "SELECT * FROM Admins ORDER BY nome";
        } else {
            $sql = "SELECT * FROM Admins WHERE id = ".$id." ORDER BY nome";
        }
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        return $rs;
    }
    
    /*
    ativo = 0 --> apenas atividades inativas
    ativo = 1 --> apenas atividades ativas
    ativo = 2 --> atividades ativas e inativas
    */
    public function descontos($id_atividade, $ativo = 1) {
        $sql = "SELECT * FROM descontos_atividades a";
        $sql .= " LEFT JOIN descontos_em_folha b ON b.id = a.id_desconto";
        $sql .= " WHERE id_atividade = ".$id_atividade;
        if ($ativo <= 1) {
            $sql .= " AND a.ativo = ".$ativo;
        }
        
        $st = $this->conn()->prepare($sql);
        
        if (!$st->execute()) return false;
        if ($st->rowCount() == 0) return false;
        
        foreach ($st->fetchAll() as $row) {
            $arr[] = $row["aliquota"];
        }
        
        if (!isset($arr)) return false;
        
        return $arr;
    }
    
    public function addAtividadeExecutada($add_nome, $add_atividade, $add_data, $add_ecohoras, $add_valor, $add_comentario) {
        
        $data = DateTime::createFromFormat('d/m/Y H:i', $add_data);
        $data = $data->format('Y-m-d');
        
        $add_valor = str_replace(",",".",$add_valor);
        $add_comentario = $this->conn()->quote($add_comentario);
        
        $valor_desconto = 0;
        if ($descontos = $this->descontos($add_atividade)) {
            foreach ($descontos as $desconto) {
                $valor_desconto += $desconto*$add_valor;
            }
        }
        
        $sql = "INSERT INTO Ecoholerites(id_admin, id_atividade, data, ecohoras, valor, desconto, comentario) VALUES";
        $sql .= " (".$add_nome.",".$add_atividade.",'".$data."',".$add_ecohoras.",".$add_valor.",".$valor_desconto.",".$add_comentario.")";
        $st = $this->conn()->prepare($sql);
        
        return $st->execute();
    }
    
    public function removeAtividadeExecutada($id_atividade_exec) {
        $sql = "DELETE FROM Ecoholerites WHERE id = ".$id_atividade_exec;
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }
    
    public function aprovaAtividadeExecutada($id_atividade_exec = "") {
        if ($id_atividade_exec != "") {
            $sql = "UPDATE Ecoholerites SET status= 2 WHERE id = ".$id_atividade_exec;
        } else {
            $sql = "UPDATE Ecoholerites SET status= 2 WHERE status = 0";
        }
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }
    
    public function reprovaAtividadeExecutada($id_atividade_exec) {
        $sql = "UPDATE Ecoholerites SET status=1 WHERE id = ".$id_atividade_exec;
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }

    /*
    regra para reembolso/entrega/traçar rotas
    = 0 / desconsidera reembolso/entrega/traçar rotas
    = 1 / considera também reembolso/entrega/traçar rotas
    = 2 / considera apenas reembolso/entrega/traçar rotas
    */
    public function relatorioPagamento($dataI = "", $dataF = "", $reembolso = 0, $entrega = 0, $tracarRotas = 0) {
        
        $sql = "";
        $sql .= "SELECT ad.nome, SUM(eh.valor) AS valor_total, SUM(eh.desconto) as desconto_total, eh.*, ea.*, ad.* FROM Ecoholerites eh";
        $sql .= " LEFT JOIN Ecoatividades ea";
        $sql .= " ON eh.id_atividade = ea.id";
        $sql .= " LEFT JOIN Admins ad";
        $sql .= " ON eh.id_admin = ad.id";
        $where = " WHERE eh.status = 2";
        if ($reembolso == 0) {
            $where .= " AND ea.id <> ".$this->idReembolso;
        }
        if ($reembolso == 2) {
            $where .= " AND ea.id = ".$this->idReembolso;
        }
        if ($entrega == 0) {
            $where .= " AND ea.id <> ".$this->idEntregas;
        }
        if ($entrega == 2) {
            $where .= " AND ea.id = ".$this->idEntregas;
        }
        if ($dataI != "") {
            $where .= " AND eh.data >= '".$dataI."'";
        }
        if ($dataF != "") {
            $where .= " AND eh.data <= '".$dataF."'";
        }
        if ($tracarRotas == 0) {
            $where .= " AND ea.id <> ".$this->idTracarRotas;
        }
        if ($tracarRotas == 2) {
            $where .= " AND ea.id = ".$this->idTracarRotas;
        }
        
        $sql .= $where;
        $sql .= " GROUP BY eh.id_admin";
        $sql .= " ORDER BY ad.nome";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;
        
        $colunas = array("nome", "valor_total", "desconto_total");
        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            foreach ($colunas as $coluna) {
                $arr[$row["id"]][$coluna] = $row[$coluna];
            }
        }

        return $arr;
    }
}
?>