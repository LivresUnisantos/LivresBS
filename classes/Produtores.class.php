<?php
class Produtores extends Livres {

    public function listaProdutoresPorNome() {
        $sql = "SELECT * FROM Produtores ORDER BY id";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $produtores[$row["Produtor"]] = $row["id"];
        }
        return $produtores;
    }
    
    public function listaProdutoresPorID($ordem = "") {
        $sql = "SELECT * FROM Produtores";
        if ($ordem == "") {
            $sql .= " ORDER BY Produtor";
        } else {
            $sql .= " ORDER BY ".$ordem;
        }
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $produtores[$row["id"]] = $row["Produtor"];
        }
        return $produtores;
    }

    public function buscaProdutor($id) {
        $sql = "SELECT * FROM Produtores WHERE id = ?";
        $st = $this->conn()->prepare($sql);
        $st->execute($id);

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetch();

        return $rs["Produtor"];
    }
    
    public function cadastrarProdutor($produtor) {
        $sql = "INSERT INTO Produtores (Produtor) VALUES ('".$produtor."')";
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }

}
?>