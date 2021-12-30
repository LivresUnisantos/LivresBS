<?php
class Produtos extends Livres {

    public function listarProdutosTodos($ordem = "nome") {
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
                    $produtos[$row["id"]][$coluna] = $valor;
                }
            }
            return $produtos;
        } else {
            return false;
        }
    }
    
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

        /*echo "<pre>";
        print_r($dados);
        echo "</pre>";*/

        $sql = "INSERT INTO produtos (".$sqlFields.") VALUES (".$sqlValues.")";
        //echo $sql;
        $st = $this->conn()->prepare($sql);
        if ($st->execute($dados)) {
            echo "Produto cadastrado";
        } else {
            echo "Falha ao cadastrar produto";
        }
    }

    //Lista quais backups de produtos gerados durante consolidação de pedidos estão disponíves para restauração
    public function listarBackupProdutos() {
        $sql = "SELECT a.nome as consolidado_por_nome, b.* FROM BackupProdutos b";
        $sql .= " LEFT JOIN Admins a ON b.consolidado_por = a.id";
        $sql .= " ORDER BY b.id DESC";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() ==0) return false;

        $rs = $st->fetchAll();
        return $rs;
    }

    //Realiza backup de todos os produtos no estado atual (campo 'previsao')
    public function realizarBackupProdutos($dataEntrega = "") {
        $produtos = $this->listarProdutosTodos();

        foreach ($produtos as $produto) {
            $ids[] = $produto["id"];
            $previsoes[] = strtotime($produto["previsao"]);
        }
        $id = implode(",",$ids);
        $previsao = implode(",", $previsoes);

        if ($dataEntrega == "") {
            $sql = "INSERT INTO BackupProdutos (consolidado_por, produtos_id, produtos_previsao) VALUES (".$_SESSION["id"].",'".$id."', '".$previsao."')";
        } else {
            $sql = "INSERT INTO BackupProdutos (pedido_data, consolidado_por, produtos_id, produtos_previsao) VALUES ('".date("Y-m-d",$dataEntrega)."',".$_SESSION["id"].",'".$id."', '".$previsao."')";
        }
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }  

    //Restaura backups que são criados no momento em que pedidos são consolidados
    public function restaurarBackupProdutos($idBackup) {
        $sql = "SELECT * FROM BackupProdutos WHERE id = ".$idBackup;
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) {
            return "Backup não encontrado";
        }
        $rs = $st->fetch();
        $ids = explode(",",$rs["produtos_id"]);
        $previsoes = explode(",",$rs["produtos_previsao"]);

        if (count($ids) != count($previsoes)) {
            return "Dados inconsistêntes, restauração não realizada";
        }

        foreach ($ids as $key => $id) {
            $backup[$id] = $previsoes[$key];
        }

        $oProdutos = new Produtos;
        $produtos = $oProdutos->listarProdutosTodos();

        foreach ($produtos as $produto) {
            $id= $produto["id"];
            $produtosDB[$id] = $produto["nome"];
            if (!array_key_exists($id, $backup)) {
                $erros[] = "Produto '".$produto["nome"]."' está cadastrado na lista de produtos, porém não existe no backup. Não foi feito alteração nele.";
            } else {
                if ($backup[$id] != strtotime($produto["previsao"])) {
                    $to_update[$id] = $backup[$id];
                }
            }            
        }

        //checar se todos os produtos do backup existem na database
        $flag=false;
        foreach($backup as $id => $produto) {
            if (!array_key_exists($id, $produtosDB)) {
                if (!$flag) $erros[] = "Os produtos abaixo estão no backup, mas não existem mais no banco de dados. Não foi feito alteração neles:";
                $erros[] = 'Código produto: '.$id;
            }
        }

        //Realizar restauração do backup
        foreach ($to_update as $id => $previsao) {
            $sql = "UPDATE produtos SET previsao = '".date("Y-m-d", $previsao)."' WHERE id = ".$id;
            $st = $this->conn()->prepare($sql);
            if (!$st->execute()) {
                $erros[] = "Falha ao atualizar produto código ".$id;
            }
        }

        if (isset($erros)) {
            return $erros;
        } else {
            return true;
        }
    }
    
}
?>