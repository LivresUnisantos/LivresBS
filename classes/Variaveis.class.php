<?php

class Variaveis extends Livres {

    private $debugMode = false;

    function __construct($dataEntrega) {
        if ($dataEntrega != "") {
            if (!is_numeric($dataEntrega)) {
                $this->dataEntrega = $this->dataParaTime($dataEntrega);
            } else {
                $this->dataEntrega = $dataEntrega;
            }
        }
    }

    public function limitePedido() {
        //Entregas fecham 2 dias antes ao meio-dia
        $entrega = date('Y-m-d H:i', $this->dataEntrega);
        $limite = strtotime('-2 days', strtotime($entrega));
        $limite = strtotime('+ 12 hours', $limite);
        $limite = date("Y-m-d H:i",$limite);
    
        //Quando em debug mode, manter pedido sempre aberto
        if ($this->debugMode) {
            $limite = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));
            $limite = date("Y-m-d H:i",$limite);
        }

        return $limite;
    }

    public function pedidoAberto($consumidor) {
        $agora = strtotime("-3 hours",strtotime(date("Y-m-d H:i:s")));
        $agora = date("Y-m-d H:i",$agora);

        $limite = $this->limitePedido();
    
        if (strtotime($agora) > strtotime($limite)) {
            $msg = "Ops... Ultrapassamos o horário limite para escolha dos variáveis essa semana. <br>";
            $msg .= "Nossos produtores aguardam o pedido de cada consumidor para realizar sua colheira e o envio do produto fresquinho para ".strftime('%A', $this->dataEntrega);
            $msg .= ", por isso é tão importante mantermos o prazo limite de ".strftime('%A %kh', strtotime($limite)).".";
            $this->setLog("log.txt","CPF: ".$consumidor["cpf"]." - tentativa de preenchimento fora do horário","");
            return $msg;
        } else {
            return true;
        }
    }
}