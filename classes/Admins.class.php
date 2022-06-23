<?php
class Admins extends Livres {

    public function listarAdmins() {
        $sql = "SELECT * FROM Admins ORDER BY nome";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        return $st->fetchAll();
    }
}
?>