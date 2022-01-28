<?php
class Caixa extends Livres {

    //ID de formas de pagamento específicas
    public $idAbertura = 7;
    public $idFechamento = 8;
    public $idSangria = 6;
    public $idDinheiro = 1;
    
    public function caixaExiste($idCaixa) {
        if (!$this->getCaixa($idCaixa)) {
            return false;
        }
        return true;
    }
    
    public function getCaixaAberto() {
        $sql = "SELECT * FROM Caixa WHERE dataFechamento IS NULL";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        $rs = $st->fetch();
        return $rs["id"];
    }
    
    public function getCaixa($idCaixa) {
        $sql = "SELECT * FROM Caixa WHERE id = ".$idCaixa;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        $rs = $st->fetch();
        $arr["id_admin"] = $rs["id_admin"];
        $arr["dataAbertura"] = $rs["dataAbertura"];
        $arr["dataFechamento"] = $rs["dataFechamento"];
        
        return $arr;
    }
    
    public function caixaAberto($idCaixa) {
        if ($idCaixa == "") return false;
        $caixa = $this->getCaixa($idCaixa);
        if (!$caixa) return false;
        
        if ($caixa["dataFechamento"] == "" || is_null($caixa["dataFechamento"]) || strtolower($caixa["dataFechamento"]) == "null") return true;
        
        return false;
    }
    
    public function abrirCaixa($idAdmin, $valor) {
        $dataAbertura = date("Y-m-d H:i:s");
        $sql = "INSERT INTO Caixa (id_admin, dataAbertura) VALUES (".$idAdmin.",'".$dataAbertura."')";
        $st = $this->conn()->prepare($sql);
        if ($st->execute()) {
            $id = $this->getCaixaAberto();
            $this->cadastraTransacao($id, "Saldo Abertura de Caixa", $valor, $this->idAbertura);
            return true;
        } else {
            return false;
        }
    }

    public function listaTransacoes($idCaixa, $caixaAberto = true) {
        $html = '<input type="hidden" id="id_caixa" name="id_caixa" value="'.$idCaixa.'" />';
        $sql = "SELECT cx.*, fr.forma_pagamento FROM CaixaTransacoes cx LEFT JOIN CaixaFormas fr ON cx.id_forma_pagamento = fr.id WHERE id_caixa = ".$idCaixa." ORDER BY id ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0)  {
            $html .= '<table class="table table-hover table-bordered table-striped table-sm">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>#</th>';
            $html .= '<th>Data</th>';
            $html .= '<th>Descrição</th>';
            $html .= '<th>Valor</th>';
            $html .= '<th>Forma Pagamento</th>';
            $html .= '<th></th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                $html .= '<tr>';
    		    $html .= '<td>'.$row["id"].'</td>';
    		    $html .= '<td>'.date('d/m/Y H:m:s', strtotime($row["data"])).'</td>';
    		    $html .= '<td>'.$row["descricao"].'</td>';
    		    $html .= '<td>R$'.number_format($row["valor"],2,",",".").'</td>';
    		    $html .= '<td>'.$row["forma_pagamento"].'</td>';
    		    if ($row["id_forma_pagamento"] != $this->idAbertura && $caixaAberto) {
    		        $html .= '<td><button type="button" class="btn btn-danger" id_transacao="'.$row["id"].'" name="apagar_transacao">Apagar</button></td>';
    		    } else {
    		        $html .= '<td></td>';
    		    }
        		$html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '</table>';
        }
        return $html;
    }
    
    public function cadastraTransacao($id_caixa, $descricao, $valor, $forma_pagamento) {
        $valor = str_replace("R", "", $valor);
        $valor = str_replace("$", "", $valor);
        $valor = str_replace(" ", "", $valor);
        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);
        
        if ($forma_pagamento == $this->idFechamento || $forma_pagamento == $this->idSangria) {
            $valor = -1 * $valor;
        }
        
        $sql = "INSERT INTO CaixaTransacoes (id_caixa, descricao, valor, id_forma_pagamento) VALUES";
        $sql .= " (".$id_caixa.",'".$descricao."', ".$valor.", ".$forma_pagamento.")";
        
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }
    
    public function apagaTransacao($idTransacao) {
        $sql = "DELETE FROM CaixaTransacoes WHERE id = ".$idTransacao;
        $st = $this->conn()->prepare($sql);
        return $st->execute();
    }
    
    public function fecharCaixa($idCaixa, $valor) {
        $sql = "UPDATE Caixa SET dataFechamento = '".date("Y-m-d H:m:i")."' WHERE id = ".$idCaixa;
        $st = $this->conn()->prepare($sql);
        if ($st->execute()) {
            return $this->cadastraTransacao($idCaixa, "Saldo Fechamento de Caixa", $valor, $this->idFechamento);
        } else {
            return false;
        }
    }
    
    public function relatorioCaixa($idCaixa) {
        $sql = "SELECT tr.*, fr.*, cx.*, SUM(tr.valor) as valor_total FROM CaixaTransacoes tr LEFT JOIN CaixaFormas fr ON tr.id_forma_pagamento = fr.id";
        $sql .= " LEFT JOIN Caixa cx ON cx.id = tr.id_caixa WHERE tr.id_caixa = ".$idCaixa;
        $sql .= " GROUP BY tr.id_forma_pagamento ORDER BY forma_pagamento ASC";
        
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() > 0) {
            $abertura = 0;
            $fechamento = 0;
            $dinheiro = 0;
            $sangria = 0;
            $rs = $st->fetchAll();
            $html = "";
            //$html = '<div class="col-3">';
            $html .= '<b>Caixa de ' . date('d/m/Y H:i:s', strtotime($rs[0]["dataAbertura"])).'</b>';
            $html .= '<table class="table table-hover table-bordered table-striped table-sm">';
            $html .= '<thead>';
            $html .= "<tr>";
            $html .= '<td>Forma de Pagamento</td>';
            $html .= '<td>Valor</td>';
            $html .= "</tr>";
            $html .= '</thead>';
            $html .= '<tbody>';
            $total = 0;
            foreach ($rs as $row) {
                $total += $row["valor_total"];
                if ($row["id_forma_pagamento"] == $this->idDinheiro) {
                    $dinheiro = $row["valor_total"];
                }
                if ($row["id_forma_pagamento"] == $this->idAbertura) {
                    $abertura = $row["valor_total"];
                    $total -= $row["valor_total"];
                }
                if ($row["id_forma_pagamento"] == $this->idFechamento) {
                    $fechamento = $row["valor_total"];
                    $total -= $row["valor_total"];
                }
                if ($row["id_forma_pagamento"] == $this->idSangria) {
                    $sangria = $row["valor_total"];
                    $total -= $row["valor_total"];
                }
                if ($row["id_forma_pagamento"] != $this->idSangria && $row["id_forma_pagamento"] != $this->idAbertura && $row["id_forma_pagamento"] != $this->idFechamento) {
                    $html .= '<tr>';
                    $html .= '<td>'.$row["forma_pagamento"].'</td>';
                    $html .= '<td>R$'.number_format($row["valor_total"],2,",",".").'</td>';
                    $html .= '</tr>';
                }
            }
            if ($sangria != 0) {
                $html .= '<tr>';
                $html .= '<td>Sangria</td>';
                $html .= '<td>R$'.number_format($sangria,2,",",".").'</td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>Total de vendas</td>';
            $html .= '<td>R$'.number_format($total,2,",",".").'</td>';
            $html .= '</tr>';
            
            $html .= '<tr>';
            $html .= '<td>Abertura</td>';
            $html .= '<td>R$'.number_format($abertura,2,",",".").'</td>';
            $html .= '</tr>';
            
            $saldo = $dinheiro + $abertura + $sangria;
            
            $html .= '<tr>';
            $html .= '<td>Saldo Previsto no Caixa</td>';
            $html .= '<td>R$'.number_format($saldo,2,",",".").'</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td>Saldo Real em Caixa</td>';
            $html .= '<td>R$'.number_format(-1*$fechamento,2,",",".").'</td>';
            $html .= '</tr>';
            
            $sobra = -1*$fechamento-$saldo;
            $html .= '<tr>';
            $html .= '<td>'.(($sobra <= 0) ? 'Falta' : 'Sobra').'</td>';
            $html .= '<td>R$'.number_format(abs($sobra),2,",",".").'</td>';
            $html .= '</tr>';

            $html .= '</tbody>';
            $html .= "</table>";
            //$html .= "</div>";
            return $html;
        }
        return false;
    }
    
    public function listarCaixasAbertos() {
        $sql = "SELECT * FROM Caixa WHERE dataFechamento IS NULL ORDER BY dataAbertura ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        return $st->fetchAll();
    }
    
    public function listarCaixasFechados() {
        $sql = "SELECT * FROM Caixa WHERE dataFechamento IS NOT NULL ORDER BY dataAbertura DESC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        return $st->fetchAll();
    }
    
    //Obter formas de pagamento
    public function getFormas() {
        $sql = "SELECT * FROM CaixaFormas ORDER BY forma_pagamento ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            if ($row["id"] != $this->idAbertura && $row["id"] != $this->idFechamento) {
                $formas[$row["id"]] = $row["forma_pagamento"];
            }
        }
        return $formas;
    }
    
    //Atualizar comentário do caixa
    public function salvaComentario($idCaixa, $comentario) {
        if ($idCaixa == 0 || $idCaixa == "") return false;
        if (!$this->getCaixa($idCaixa)) return false;
        
        $sql = "UPDATE Caixa SET comentario = ? WHERE id = ?";
        $st = $this->conn()->prepare($sql);
        if ($st->execute([$comentario, $idCaixa])) {
            echo $st->debugDumpParams();
            return true;
        }
        echo 'def';
        return false;
    }
    
    public function getComentario($idCaixa) {
        if ($idCaixa == 0 || $idCaixa == "") return false;
        if (!$this->getCaixa($idCaixa)) return false;
        
        $sql = "SELECT * FROM  Caixa WHERE id = ?";
        $st = $this->conn()->prepare($sql);
        if ($st->execute([$idCaixa])) {
            if ($st->rowCount() == 0) return false;
            $rs = $st->fetch();
            return $rs["comentario"];
        }
        return false;
        
    }
}
?>




















