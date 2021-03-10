<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);

function getSaldo($id,$conn) {
    $sql = "SELECT * FROM Consumidores WHERE id = ".$id;
    $st = $conn->prepare($sql);
    $st->execute();
     if ($st->rowCount() > 0) {
        $rs = $st->fetch();
        $saldo = round($rs["credito"],2);
        if ($saldo >= 0) {
            $getSaldo = "R$";
        } else {
            $getSaldo = "-R$";
        }
        $getSaldo .= number_format(abs($saldo),2,",",".");
        return $getSaldo;
    } else {
        return "R$0,00";
    }
}

if (isset($_POST["id"])) {
    $id = $_POST["id"];
    if (isset($_POST["act"])) {
        //Tratativas referente saldo de sacolas
        if ($_POST["act"] == "sacolas") {
            $inc = $_POST["inc"];
            $sql = "INSERT INTO Sacolas (IDConsumidor,Admin,Quantidade) VALUES (".$id.",'".$_SESSION["login"]."',".$inc.")";
            $st = $conn->prepare($sql);
            $st->execute();
            
            $sql = "SELECT SUM(Quantidade) AS somaQuantidade FROM Sacolas WHERE IDConsumidor = ".$id;
            $st = $conn->prepare($sql);
            $st->execute();
            $rs = $st->fetch();
            $sacolas = $rs["somaQuantidade"];
            
            $sql = "UPDATE Consumidores SET sacolas = ".$sacolas." WHERE id = ".$id;
            $st = $conn->prepare($sql);
            $st->execute();
            
            echo $sacolas;
        }
        //Tratativas referentes créditos/débitos  (adicionar/remover)
        if ($_POST["act"] == "fiado") {
            $operacao = $_POST["operacaoFiado"];
            $valor = str_replace (",",".",$_POST["valorFiado"]);
            if (strlen($valor) == 0 || strlen($operacao) == 0) {
                echo "Falha. Preencha todos os campos.";
            } else {
                if ($operacao == "debito") {
                    $valor=$valor*(-1.0);
                } else {
                    $valor=$valor*(1.0);
                }
                $obsFiado = $_POST["obsFiado"];
                $sql = "INSERT INTO Fiado (IDConsumidor,Admin,Valor,Observacao) VALUES (".$id.",'".$_SESSION["login"]."',".$valor.",'".$obsFiado."')";
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    //Atualizar campo de crédito na tabela do consumidor
                    $sql = "SELECT SUM(Valor) AS somaFiado FROM Fiado WHERE IDConsumidor = ".$id;
                    $st = $conn->prepare($sql);
                    $st->execute();
                    $rs = $st->fetch();
                    $totalFiado = $rs["somaFiado"];
                    $sql = "UPDATE Consumidores SET credito = ".round($totalFiado,2)." WHERE id = ".$id;
                    $st = $conn->prepare($sql);
                    $st->execute();
                    echo "Registro realizado.";
                } else {
                    echo "Falha ao registrar";
                }
            }
        }
        //Obtém lista dos créditos/débitos adicionados/removidos
        if ($_POST["act"] == "lista") {
            $sql = "SELECT * FROM Fiado WHERE IDConsumidor = ".$id." ORDER BY data_criacao DESC";
            $st = $conn->prepare($sql);
            $st->execute();
            if ($st->rowCount() > 0) {
                $rs = $st->fetchAll();
                echo '<table border="1">';
                echo '<tr>';
                echo '<td>Data</td>';
                echo '<td>Transação</td>';
                echo '<td>Valor</td>';
                echo '<td>Observação</td>';
                echo '</tr>';
                foreach ($rs as $row) {
                    echo '<tr>';
                    echo '<td>'.date("d/m/Y H:i:s", strtotime($row["data_criacao"])).'</td>';
                    echo '<td>'.($row["Valor"] >= 0 ? "Crédito" : "Débito").'</td>';
                    echo '<td>'.number_format(abs($row["Valor"]),2,",",".").'</td>';
                    echo '<td>'.$row["Observacao"].'</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        //Dados do consumidor
        if ($_POST["act"] == "consumidor") {
            $sql = "SELECT * FROM Consumidores WHERE id = ".$id;
            $st = $conn->prepare($sql);
            $st->execute();
            if ($st->rowCount() > 0) {
                $rs = $st->fetch();
                echo "<p>Editando dados de <b>".ucwords(strtolower($rs["consumidor"]))."</b>.";
                echo "Saldo atual: ";
                if ($rs["credito"] >= 0) {
                    echo '<span id="fiadoSaldoPopup" class="credito">';
                } else {
                    echo '<span id="fiadoSaldoPopup" class="debito">';
                }
                echo getSaldo($id,$conn);
                echo "</span>";
                echo "</p>";
            }
        }
        if ($_POST["act"] == "saldo") {
            echo getSaldo($id,$conn);
        }
        if ($_POST["act"] == "sinalSaldo") {
             $sql = "SELECT * FROM Consumidores WHERE id = ".$id;
            $st = $conn->prepare($sql);
            $st->execute();
             if ($st->rowCount() > 0) {
                $rs = $st->fetch();
                echo round($rs["credito"],2);
             } else {
                 echo 0;
             }
        }
        //Muda consumidor de grupo
        if ($_POST["act"] == "atualizaGrupo") {
            $comunidade = $_POST["comunidade"];
            $sql = "UPDATE Consumidores SET comunidade = ".$comunidade." WHERE id = ".$id;
            $st = $conn->prepare($sql);
            if ($st->execute()) {
                echo "Alteração realizada. A página será atualizada.";
                setlog("log.txt","Alteração de grupo",$sql);
            } else {
                echo "Falha oa realizar alteração. A página será atualizada.";
            }
        }
    }
}
?>