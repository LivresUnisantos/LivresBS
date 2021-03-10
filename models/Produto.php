<?php
class Produto {
    private $conn;
    private $table = 'produtos';

    public $id_produto;
    public $id_produtor;
    public $produto;
    public $categoria;
    public $unidade;
    public $produtor;
    public $preco_produtor;
    public $preco_comboio;
    public $preco_comunidade;
    public $preco_mercado;
    public $disponivel_desde;
    public $disponivel_mensal;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT
                p.id as id_produto,
                r.id as id_produtor,
                p.nome as produto,
                p.categoria,
                p.unidade,
                p.produtor,
                p.preco_produtor as preco_produtor,
                p.preco as preco_comboio,
                p.preco_lojinha as preco_comunidade,
                p.preco_mercado as preco_mercado,
                p.prazo as disponivel_desde,
                p.mensal as disponivel_mensal
            FROM
                '.$this->table.' p
            LEFT JOIN Produtores r ON r.Produtor = p.produtor
            ORDER BY p.nome ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function find($id) {
        $query = 'SELECT
                p.id as id_produto,
                r.id as id_produtor,
                p.nome as produto,
                p.categoria,
                p.unidade,
                p.produtor,
                p.preco_produtor as preco_produtor,
                p.preco as preco_comboio,
                p.preco_lojinha as preco_comunidade,
                p.preco_mercado as preco_mercado,
                p.prazo as disponivel_desde,
                p.mensal as disponivel_mensal
            FROM
                '.$this->table.' p
            LEFT JOIN Produtores r ON r.Produtor = p.produtor
            WHERE p.id = '.$id.'
            ORDER BY p.nome ASC';
            
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

}
?>