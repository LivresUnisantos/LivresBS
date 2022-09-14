<?php
class Listas extends Livres {

    public function produtosListaAtivos($lista = "1", $ordem = "nome", $categoria = "", $filtro = "", $ignorarSoftdelete = false) {
        $sql = "SELECT *, l.id as id_item, l.ativo as item_ativo, p.id as id_produto, p.imagem as imagem, p.nome FROM listas_itens l LEFT JOIN produtos p";
        $sql .= " ON p.id = l.id_produto WHERE l.id_lista = ".$lista;
        $sql .= " AND l.ativo = 1";
        if ($categoria != "") {
            $sql .= " AND p.categoria = '".$categoria."'";
        }
        if ($filtro != "") {
            $sql .= " AND p.nome LIKE '%".$filtro."%'";
        }
        if ($ignorarSoftdelete) {
            $sql .= " soft_delete = 0";
        }
        $ordem = strtolower($ordem);
        $ordem = str_replace("ç","c",$ordem);
        if ($ordem == "nome" || $ordem == "preco") {
            $sql .= " ORDER BY p.".$ordem;
        }
        //echo $sql;
        return $this->listarProdutos($sql);
    }
    
    public function produtosListaTodos($lista = "1", $ordem = "nome", $categoria = "", $ignorarSoftdelete = false) {
        $sql = "SELECT *, l.id as id_item, l.ativo as item_ativo, p.id as id_produto, p.imagem as imagem FROM produtos p LEFT JOIN listas_itens l";
        $sql .= " ON (p.id = l.id_produto AND l.id_lista = ".$lista.")";
        if ($categoria != "") {
            $sql .= " WHERE p.categoria = '".$categoria."'";
            if ($ignorarSoftdelete) {
                $sql .= " soft_delete = 0";
            }
        } else {
            if ($ignorarSoftdelete) {
                $sql .= " WHERE soft_delete = 0";
            }
        }
        $ordem = strtolower($ordem);
        $ordem = str_replace("ç","c",$ordem);
        if ($ordem == "nome" || $ordem == "preco") {
            $sql .= " ORDER BY p.".$ordem;
        }
        //echo $sql;
        return $this->listarProdutos($sql);
    }
    
    public function listarListas() {
        $sql = "SELECT * FROM listas_produtos ORDER BY id ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            return $rs;
        } else {
            return false;
        }
    }
    
    public function getNomeLista($id) {
        $sql = "SELECT * FROM listas_produtos WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            //echo $rs["nome_lista"];
            return $rs["nome_lista"];
        }
        return false;
    }
    
    public function updateNomeLista($id, $nome) {
        $sql = "UPDATE listas_produtos SET nome_lista = '".$nome."' WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        if ($st->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function createLista($nome) {
        $sql = "INSERT INTO listas_produtos (nome_lista) VALUES ('".$nome."')";
        $st = $this->conn()->prepare($sql);
        if ($st->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function deleteLista($id) {
        /*
        $nomeLista = $this->getNomeLista($id);
        if (!$nomeLista) {
            return false;
        }
        //Checar se existe grupo de consumidores avulsos associados à essa lista
        $sql = "SELECT grupo FROM Usuarios WHERE grupo = '".$nomeLista."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            return false;
        }*/
        if ($this->listaGrupoExiste($id)) {
            return false;
        }
        $sql = "DELETE FROM listas_produtos WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            return true;
        }
        return false;
    }
    
    public function listaGrupoExiste($id) {
        /*$nomeLista = $this->getNomeLista($id);
        if (!$nomeLista) {
            return false;
        }
        $grupos = explode("+", $nomeLista);
        foreach ($grupos as $grupo) {
            $grupo = strtolower(trim($grupo));
            if ($grupo == "pre") {
                $grupo = "pre-comunidade";
            }
            //Checar se existe grupo de consumidores avulsos associados à essa lista
            $sql = "SELECT grupo FROM Usuarios WHERE grupo = '".$grupo."'";
            $st = $this->conn()->prepare($sql);
            $st->execute();
            if ($st->rowCount() > 0) {
                return true;
            }
        }
        
        return false;*/
        
        $sql = "SELECT * FROM listas_grupos WHERE id_lista = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            return true;
        }
        return false;

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

    public function listarVariaveis($dataEntrega) {
        $sql = "SELECT *, l.id as id_item, 1 as item_ativo, 1 as id_lista, p.id as id_produto, p.imagem as imagem FROM produtosVar l LEFT JOIN produtos p";
        $sql .= " ON p.id = l.idProduto";
        $sql .= " WHERE l.data_entrega = '".$dataEntrega."'";
        $sql .= " ORDER BY p.nome";

        return $this->listarProdutos($sql);
    }

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