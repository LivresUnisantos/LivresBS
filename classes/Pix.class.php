<?php

require __DIR__.'/pix/vendor/autoload.php';
require __DIR__.'/pix/config-pix.php';
    
use \App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

class Pix extends Livres {
    
    public $dataEntrega;
    public $dataEntregaID;
    
    public $pixChave;
    public $pixDescricao;
    public $pixNomeConta;
    public $pixCidadeConta;
    
    function __construct() {
        if (isset($_SESSION["data_consulta"])) {
            $this->dataEntregaID = $this->dataPelaString($_SESSION["data_consulta"]);
            $this->dataEntrega = $this->dataPeloID($this->dataEntregaID,'string');
        }
        
        $this->pixChave = $this->getParametro('pixChave');
        $dt = $this->dataParaTime($this->dataEntrega);
        $this->pixDescricao = "Cesta dia ".Date('d/m/Y', $dt);
        $this->pixNomeConta = $this->getParametro('pixNomeConta');
        $this->pixCidadeConta = $this->getParametro('pixCidadeConta');
    }
    
    public function CopiaColaPendentes() {
        $sql = "SELECT * FROM pedidos_consolidados WHERE (pgt_pix_copiacola = '' OR pgt_pix_copiacola IS NULL) AND consumidor_id IS NOT NULL AND pedido_data = '".$this->dataEntrega."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();    
        return $st->rowCount();
    }
    
    public function CopiaColaErroValor() {
        $sql = "SELECT * FROM pedidos_consolidados WHERE pedido_valor_total <> pgt_valor_linkpix AND consumidor_id IS NOT NULL AND pedido_data = '".$this->dataEntrega."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();    
        return $st->rowCount();
    }
    
    public function PagamentoErroValor() {
        $sql = "SELECT * FROM pedidos_consolidados WHERE pedido_valor_total <> pgt_valorpago AND pgt_status > 0 AND consumidor_id IS NOT NULL AND pedido_data = '".$this->dataEntrega."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();    
        return $st->rowCount();
    }
    
    public function GerarCopiaColaPendentes() {
        $sql = "SELECT * FROM pedidos_consolidados WHERE (pgt_pix_copiacola = '' OR pgt_pix_copiacola IS NULL) AND consumidor_id IS NOT NULL AND pedido_data = '".$this->dataEntrega."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        $error = "";
        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                $valor = $row['pedido_valor_total'];
                $uuid = $row["uuid"];
                $pixid = $this->encode($uuid);
                $copiaColaPix = $this->GetCopiaCola($valor, $pixid);
                $sqlPix = "UPDATE pedidos_consolidados SET pgt_pix_copiacola = '".$copiaColaPix."', pgt_pix_uuid = '".$pixid."' WHERE pedido_id = ".$row["pedido_id"];
                $st = $this->conn()->prepare($sqlPix);
                if (!$st->execute()) {
                    if (strlen($error) > 0) { $error .= "<br>"; }
                    $error .= "Erro criando link do pedido ".$row["pedido_id"];
                }
            }
        }
        
        return $error;
    }
    
    public function GerarCopiaColaTodos() {
        $sql = "SELECT * FROM pedidos_consolidados WHERE consumidor_id IS NOT NULL AND pedido_data = '".$this->dataEntrega."'";
        $st = $this->conn()->prepare($sql);
        $st->execute();
        $error = "";
        if ($st->rowCount() > 0) {
            $rs = $st->fetchAll();
            foreach ($rs as $row) {
                $valor = $row['pedido_valor_total'];
                $uuid = $row["uuid"];
                $pixid = $this->encode($uuid);
                $copiaColaPix = $this->GetCopiaCola($valor, $pixid);
                $sqlPix = "UPDATE pedidos_consolidados SET pgt_pix_copiacola = '".$copiaColaPix."', pgt_pix_uuid = '".$pixid."', pgt_valor_linkpix = ".$valor." WHERE pedido_id = ".$row["pedido_id"];
                $st = $this->conn()->prepare($sqlPix);
                if (!$st->execute()) {
                    if (strlen($error) > 0) { $error .= "<br>"; }
                    $error .= "Erro criando link do pedido ".$row["pedido_id"];
                }
            }
        }
        
        return $error;
    }
    
    public function GetCopiaCola($valor, $pixid) {
        $obPayload = (new Payload)->setPixKey($this->pixChave)
                              ->setDescription($this->pixDescricao)
                              ->setMerchantName($this->pixNomeConta)
                              ->setMerchantCity($this->pixCidadeConta)
                              ->setAmount($valor)
                              ->setTxid($pixid);
        
        $payloadQrCode = $obPayload->getPayload();
        return $payloadQrCode;

    }
    
    public function GerarCopiaColaUnico($pedido_id) {
        $error = "";
        $sql = "SELECT * FROM pedidos_consolidados WHERE pedido_id = ".$pedido_id;
        $st = $this->conn()->prepare($sql);
        $st->execute();
        if ($st->rowCount() != 1) {
            $error = "Pedido não encontrado";
        } else {
            $row = $st->fetch();
            
            $valor = $row['pedido_valor_total'];
            //$valor = 90;
            $uuid = $row["uuid"];
            $pixid = $this->encode($uuid);
            $copiaColaPix = $this->GetCopiaCola($valor, $pixid);
            
            $sqlPix = "UPDATE pedidos_consolidados SET pgt_pix_copiacola = '".$copiaColaPix."', pgt_pix_uuid = '".$pixid."', pgt_valor_linkpix = ".$valor." WHERE pedido_id = ".$pedido_id;
            $st = $this->conn()->prepare($sqlPix);
            if (!$st->execute()) {
                if (strlen($error) > 0) { $error .= "<br>"; }
                $error .= "Erro criando link do pedido ".$row["pedido_id"];
            }
        }
        
        return $error;
    }
    
    public function ApagarLinkPagamento($pedido_id) {
        $sql = "UPDATE pedidos_consolidados SET pgt_pix_copiacola = '' WHERE pedido_id = ".$pedido_id;
        $st = $this->conn()->prepare($sql);
        if (!$st->execute()) {
            return "Erro ao remover link do pedido ".$pedido_id;
        } else {
            return "";
        }
    }
    
    public function showCopiaCola($uuid) {
        $sql = "SELECT * FROM pedidos_consolidados WHERE pgt_pix_uuid = :uuid";
        $st = $this->conn()->prepare($sql);
        $st->execute(['uuid' => $uuid]);
        
        if ($st->rowCount() != 1) return false;
        
        $row = $st->fetch();
        
        return $row["pgt_pix_copiacola"];
    }

    public function PrintQRCode($pixCopiaCola) {
        $obQrCode = new QrCode($pixCopiaCola);
        $image = (new Output\Png)->output($obQrCode,400);
        return '<img src="data:image/png;base64, '.base64_encode($image).'">';
    }
    
    
    //https://medium.com/@huntie/representing-a-uuid-as-a-base-62-hash-id-for-short-pretty-urls-c30e66bf35f9
    function gmp_base_convert($value, $initialBase, $newBase) {
        return gmp_strval(gmp_init($value, $initialBase), $newBase);
    }
    
    public function encode($uuid) {
        return $this->gmp_base_convert(str_replace('-', '', $uuid), 16, 62);
    }
    
    public function decode($hashid) {
        return array_reduce([20, 16, 12, 8], function ($uuid, $offset) {
            return substr_replace($uuid, '-', $offset, 0);
        }, str_pad($this->gmp_base_convert($hashid, 62, 16), 32, '0', STR_PAD_LEFT));
    }
}