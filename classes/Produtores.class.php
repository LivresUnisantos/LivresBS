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
    
    public function listaProdutoresPorID() {
        $sql = "SELECT * FROM Produtores ORDER BY produtor";
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

}
?>