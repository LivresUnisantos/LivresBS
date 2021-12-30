<?php
class Ecoholerite extends Livres {

    public $idAdmin;

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
        if ($id == "") {
            $sql = "SELECT * FROM Ecoatividades ORDER BY descricao";
        } else {
            $sql = "SELECT * FROM Ecoatividades WHERE id = ".$id." ORDER BY descricao";
        }
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
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
    
    public function addAtividadeExecutada($add_nome, $add_atividade, $add_data, $add_ecohoras, $add_valor, $add_comentario) {
        
        $data = DateTime::createFromFormat('d/m/Y H:i', $add_data);
        $data = $data->format('Y-m-d');
        
        $add_valor = str_replace(",",".",$add_valor);
        $add_comentario = $this->conn()->quote($add_comentario);
        
        $sql = "INSERT INTO Ecoholerites(id_admin, id_atividade, data, ecohoras, valor, comentario) VALUES";
        $sql .= " (".$add_nome.",".$add_atividade.",'".$data."',".$add_ecohoras.",".$add_valor.",".$add_comentario.")";
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
    regra para reembolso/entrega
    reembolso = 0 / desconsidera reembolsos
    reembolso = 1 / considera também reembolsos
    reembolso = 2 / considera apenas reembolsos
    */
    public function relatorioPagamento($dataI = "", $dataF = "", $reembolso = 0, $entrega = 0) {

        $sql = "";
        $sql .= "SELECT ad.nome, SUM(eh.valor) AS valor_total, eh.*, ea.*, ad.* FROM Ecoholerites eh";
        $sql .= " LEFT JOIN Ecoatividades ea";
        $sql .= " ON eh.id_atividade = ea.id";
        $sql .= " LEFT JOIN Admins ad";
        $sql .= " ON eh.id_admin = ad.id";
        $where = " WHERE eh.status = 2";
        if ($reembolso == 0) {
            $where .= " AND ea.descricao <> 'Reembolso'";
        }
        if ($reembolso == 2) {
            $where .= " AND ea.descricao = 'Reembolso'";
        }
        if ($entrega == 0) {
            $where .= " AND ea.descricao <> 'Entregas'";
        }
        if ($entrega == 2) {
            $where .= " AND ea.descricao = 'Entregas'";
        }
        if ($dataI != "") {
            $where .= " AND eh.data >= '".$dataI."'";
        }
        if ($dataF != "") {
            $where .= " AND eh.data <= '".$dataF."'";
        }
        $sql .= $where;
        $sql .= " GROUP BY eh.id_admin";
        //echo $sql."<br><br>";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        return $st->fetchAll();
    }
}
?>