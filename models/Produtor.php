<?php
class Produtor {
    private $conn;
    private $table = 'Produtores';

    public $id;
    public $produtor;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT
                p.id,
                p.Produtor as produtor
            FROM
                '.$this->table.' p
            ORDER BY p.produtor ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

}
?>