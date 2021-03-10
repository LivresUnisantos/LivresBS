<?php
class Consumidor {
    private $conn;
    private $table = 'Consumidores';

    public $id;
    public $consumidor;
    public $email;
    public $cpf;
    public $endereco;
    public $telefone;
    public $cota;
    public $data_criacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT
                c.id,
                c.consumidor,
                c.email,
                c.cpf,
                c.endereco,
                c.telefone,
                c.cota_imediato as cota,
                c.data_criacao
            FROM
                '.$this->table.' c
            ORDER BY c.id ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

}
?>