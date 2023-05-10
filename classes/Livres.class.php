<?php
class Livres {

    public $host = "";
    public $name = "";
    public $user = "";
    public $password = "";
    
    public $dataEntrega="";

    //producao|teste -> basicamente o modo 'teste' mostra mais erros
    public $modo = 'producao';

    public function conn() {
        include(__DIR__."/db_info.php");
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->password = $password;
        
        $conn = new PDO("mysql:host=".$this->host.";dbname=".$this->name,$this->user,$this->password,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if ($this->modo == 'teste') {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $conn;
    }

    public function getParametro($parametro) {
        $sql = "SELECT * FROM Parametros WHERE parametro = '".$parametro."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            return $rs["valor"];
        } else {
            return false;
        }
    }

    public function defineDataEntrega($dataEntrega) {
        $this->dataEntrega = $dataEntrega;
    }

    public function obtemDataEntrega() {
        return $this->dataEntrega;
    }

    public function formataCPF($cpf) {
        return substr($cpf, 0,3).".".substr($cpf,3,3).".".substr($cpf,6,3)."-".substr($cpf,9,2);
    }

    public function cotaIdeal($valor) {
        if ($valor == 0) {
            return 0;
        }
        $cotaCalculada = floor($valor/5)*5+5;
        if ($cotaCalculada-$valor < 4) { $cotaCalculada += 5; }

        if ($cotaCalculada < 15) { $cotaCalculada = 15; }

        return $cotaCalculada;
    }

    public function frequenciasEntrega($dataEntrega = "") {
        if ($dataEntrega == "") {
            if ($this->dataEntrega == "") {
                exit('Erro - Sem referência de data');
            }
        } else {
            $this->dataEntrega = $dataEntrega;
        }
        $sql = "SELECT * FROM Calendario WHERE data = '".date("Y-m-d",$dataEntrega)."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            for ($i = 1; $i <= $this->getParametro('grupos'); $i++) {
                $frequencias[$i] = $rs[$i."acomunidade"];
            }
            return $frequencias;
        }
        return false;
    }

    public function frequenciaNaSemana($frequenciaSemana, $frequenciaProduto) {
        if (strlen($frequenciaSemana) < 3) return false;
        $frequenciaProduto = strtolower($frequenciaProduto);        
        $flag = ($frequenciaProduto == 'semanal' && $frequenciaSemana[0]) || ($frequenciaProduto == 'quinzenal' && $frequenciaSemana[1]) || ($frequenciaProduto == 'mensal' && $frequenciaSemana[2]);
        return $flag;
    }

    public function dataParaTime($dataEntrega) {
        try {
            new \DateTime($dataEntrega);
            return strtotime($dataEntrega);
        } catch (\Exception $e) {
            return $dataEntrega;
        }
    }

    public function dataTimeParaString($dataEntrega) {
        return date('Y-m-d H:i',$dataEntrega);
    }

    //retorno pode ser string ou time
    public function dataPeloID($id, $retorno = 'string') {
        if ($id == "" || is_null($id)) return false;

        $sql = "SELECT * FROM Calendario WHERE id = ".$id;
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;
                
        $rs = $st->fetch();

        if ($retorno == 'time') {
            return strtotime($rs["data"]);
        } else {
            return $rs["data"];
        }
    }

    public function dataPelaString($data) {
        if ($data == "" || is_null($data)) return false;

        $data = DateTime::createFromFormat('Y-m-d H:i', $data);

        $sql = "SELECT * FROM Calendario WHERE data = '".$data->format('Y-m-d')."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;
                
        $rs = $st->fetch();

        return $rs["id"];
    }

    public function dadosDelivery($id) {
        if (is_null($id)) {
            $sql = "SELECT * FROM FormaEntrega";
            $st = $this->conn()->prepare($sql);
            $st->execute();
            if ($st->rowCount() == 0) return false;

            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                $ret[$row["id"]]["id"] = $row["id"];
                $ret[$row["id"]]["descricao_entrega"] = $row["descricao_entrega"];
                $ret[$row["id"]]["valor_entrega"] = $row["valor_entrega"];
            }
            return $ret;
        } else {
            $sql = "SELECT * FROM FormaEntrega WHERE id = ".$id;
            $st = $this->conn()->prepare($sql);
            $st->execute();
            if ($st->rowCount() == 0) return false;
            
            $rs = $st->fetch();
            return [
                "id"                => $rs["id"],
                "descricao_entrega" => $rs["descricao_entrega"],
                "valor_entrega"     => $rs["valor_entrega"]
            ];
        }
    }
    
    public function formas_pagamento() {
        $sql = "SELECT * FROM formas_pagamento";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        
        if ($st->rowCount() == 0) return false;
        
        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $ret[$row["id"]] = $row["forma_pagamento"];
        }
        return $ret;
    }

    public function setLog($file,$msg,$sql) {
        $fp = fopen($file, 'a');
        if (session_status() == PHP_SESSION_NONE || !isset($_SESSION["login"])) {
            $msg = date("Y-m-d-H:i:s")." () ".$msg;
        } else {
            $msg = date("Y-m-d-H:i:s")." (".$_SESSION["login"].") ".$msg;
        }
        $msg .= " | ";
        $msg .= $sql;
        $msg = "\n\r".$msg;
        fwrite($fp, $msg);
        fclose($fp);
    }
}
?>