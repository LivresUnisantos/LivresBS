<?php
class Calendario extends Livres {

    public function listaDatas($dataSelecionada = "") {
        if ($dataSelecionada == "") {
            $sql = "SELECT * FROM Calendario WHERE data > '2020-12-01' ORDER BY data ASC";
        } else {
            $sql = "SELECT * FROM Calendario WHERE data = '".$dataSelecionada."' ORDER BY data ASC";
        }
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $datas[$row["id"]] = strtotime($row["data"]);
        }
        return $datas;
    }

    public function montaArrayDatas() {
        $sql = "SELECT * FROM Calendario WHERE data > '".(date("Y")-1)."-12-01' ORDER BY data ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $datas[] = [
                "id"    =>  $row["id"],
                "data"  =>  $row["data"]
            ];
        }
        return $datas;
    }

    public function montaMenuDatas($dataSelecionada = "") {
        $sql = "SELECT * FROM Calendario WHERE data > '".(date("Y")-1)."' ORDER BY data ASC";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();
        $menu = '<select id="data" name="data" class="data-selecao form-control">
              <option value="">Escolha</option>';

        foreach ($rs as $row) {
            if ($dataSelecionada == $row["id"]) {
                $menu .= '<option value="'.$row["id"].'" selected="selected">'.date("d/m/Y",strtotime($row["data"])).'</option>';
            } else {
                $menu .= '<option value="'.$row["id"].'">'.date("d/m/Y",strtotime($row["data"])).'</option>';
            }
        }

        $menu .= '</select>';

        return $menu;
    }

    //public function montaDisplayFrequenciaSemana($dataEntrega) {
    public function montaDisplayFrequenciaSemana() {
        if (!isset($_SESSION["data_id"])) return "Sem entrega";
        $dataStr = $this->dataPeloID($_SESSION["data_id"],'string');
        if (!$dataStr) return "Sem entrega";
        $frequencias = $this->frequenciasEntrega(strtotime($dataStr));
        foreach ($frequencias as $grupo=>$fcod) {
            if (strlen($fcod) > 0) {
                $fcurr = [];
                if ($fcod[0] == 1) $fcurr[] = "S";
                if ($fcod[1] == 1) $fcurr[] = "Q";
                if ($fcod[2] == 1) $fcurr[] = "M";

                $fcurr = join("+",$fcurr);
                if (strlen($fcurr) > 0) {
                    $fstr[] = 'G'.$grupo.' = '.$fcurr;
                }
            }
        }
        $fstr = join(" | ",$fstr);
        return $fstr;
    }
}
?>