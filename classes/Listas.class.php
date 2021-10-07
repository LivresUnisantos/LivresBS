<?php
class Listas extends Livres {

    public function produtosListaAtivos($lista = "1", $ordem = "nome", $categoria = "") {
        $sql = "SELECT *, l.id as id_item, l.ativo as item_ativo, p.id as id_produto, p.imagem as imagem FROM listas_itens l LEFT JOIN produtos p";
        $sql .= " ON p.id = l.id_produto";
        $sql .= " WHERE l.ativo = 1 AND l.id_lista = ".$lista;
        if ($categoria != "") {
            $sql .= " AND p.categoria = '".$categoria."'";
        }
        $ordem = strtolower($ordem);
        $ordem = str_replace("ç","c",$ordem);
        if ($ordem == "nome" || $ordem == "preco") {
            $sql .= " ORDER BY p.".$ordem;
        }
        //echo $sql;
        return $this->listarProdutos($sql);
    }
    
    public function produtosListaTodos($lista = "1", $ordem = "nome", $categoria = "") {
        $sql = "SELECT *, l.id as id_item, l.ativo as item_ativo, p.id as id_produto, p.imagem as imagem FROM produtos p LEFT JOIN listas_itens l";
        $sql .= " ON p.id = l.id_produto";
        $sql .= " WHERE (l.id_lista = ".$lista." OR l.id_lista IS NULL)";
        if ($categoria != "") {
            $sql .= " AND p.categoria = '".$categoria."'";
        }
        $ordem = strtolower($ordem);
        $ordem = str_replace("ç","c",$ordem);
        if ($ordem == "nome" || $ordem == "preco") {
            $sql .= " ORDER BY p.".$ordem;
        }
        //echo $sql;
        return $this->listarProdutos($sql);
    }

    /*public function listarProdutosTodos($ordem = "nome") {
        $sql = "SELECT * FROM produtos ";
        $sql .= "ORDER BY ".$ordem." ASC";
        return $this->listarProdutos($sql);
    }

    public function listarProdutosAtivos($dataEntrega, $ordem = "nome") {
        $sql = "SELECT * FROM produtos WHERE produtos.previsao <= '".date("Y-m-d",$dataEntrega)."'";        
        $sql .= " ORDER BY ".$ordem." ASC";
        return $this->listarProdutos($sql);
    }

    public function listarProdutosInativos($dataEntrega, $ordem = "nome") {
        $sql = "SELECT * FROM produtos WHERE produtos.previsao > '".date("Y-m-d",$dataEntrega)."'";
        $sql .= " ORDER BY ".$ordem." ASC";        
        return $this->listarProdutos($sql);
    }*/

    private function listarProdutos($sql) {
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();

            $sql = "DESCRIBE produtos";
            $st = $this->conn()->prepare($sql);
            $st->execute();
            $colunas = $st->fetchAll(PDO::FETCH_COLUMN);

            foreach ($rs as $row) {
                foreach ($colunas as $coluna) {
                    $valor = $row[$coluna];
                    $produtos[$row["id_produto"]][$coluna] = $valor;
                }
                $produtos[$row["id_produto"]]["id"] = $row["id_produto"];
                $produtos[$row["id_produto"]]["id_lista"] = $row["id_lista"];
                $produtos[$row["id_produto"]]["id_item"] = $row["id_item"];
                $produtos[$row["id_produto"]]["ativo"] = $row["item_ativo"];
            }
            /*echo 'teste';
            echo "<pre>";
            print_r($produtos);
            echo "</pre>";*/
            return $produtos;
        } else {
            echo 'erro';
            return false;
        }
    }
    /*
    public function buscaProduto($id = "") {
        if ($id == "") {
            return false;
        }
        $sql = "SELECT * FROM produtos WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 1) {
            return $st->fetch();
        } else {
            return false;
        }
    }

    public function unidades($lookup = "id") {
        $sql = "SELECT * FROM unidades";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                if ($lookup == "id") {
                    $unidades[$row["id"]] = $row["unidade"];
                } else {
                    $unidades[$row["unidade"]] = $row["id"];
                }
            }
            return $unidades;
        }
        return false;
    }

    public function categorias() {
        $sql = "SELECT * FROM CategoriasProdutos ORDER BY Categoria ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            return $rs;
        } else {
            return false;
        }
        return false;
    }

    public function atualizarProduto($dados) {

        $sqlClause = "";
        foreach ($dados as $campo => $valor) {
            if ($campo != "id") {
                if ($sqlClause != "") { $sqlClause .= ", "; }
                $sqlClause .= $campo." = :".$campo;
            }
        }

        $sql = "UPDATE produtos SET ".$sqlClause." WHERE id = :id";
        $st = $this->conn()->prepare($sql);
        if ($st->execute($dados)) {
            echo "Produto atualizado";
        } else {
            echo "Falha ao atualizar produto";
        }
    }

    public function cadastraProduto($dados) {

        unset($dados["id"]);
        $sqlFields = "";
        $sqlValues = "";
        foreach ($dados as $campo => $valor) {
            if ($campo != "id") {
                if ($sqlFields != "") { $sqlFields .= ", "; }
                $sqlFields .= $campo;

                if ($sqlValues != "") { $sqlValues .= ", "; }
                $sqlValues .= ":".$campo;
            }
        }

        //echo "<pre>";
        //print_r($dados);
        //echo "</pre>";

        $sql = "INSERT INTO produtos (".$sqlFields.") VALUES (".$sqlValues.")";
        //echo $sql;
        $st = $this->conn()->prepare($sql);
        if ($st->execute($dados)) {
            echo "Produto cadastrado";
        } else {
            echo "Falha ao cadastrar produto";
        }
    }*/
    
}
?>