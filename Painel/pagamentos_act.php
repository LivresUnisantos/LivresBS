<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$levelRequired=10000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";
require_once "helpers.php";

//print_r($_POST);
//exit();
if (isset($_POST["id_pedido"]) && isset($_POST["pgt_status"]) && isset($_POST["pgt_valorpago"]) && isset($_POST["pgt_forma"]) && isset($_POST["pgt_comentario"])) {
    $livres = new Livres();
    $conn = $livres->conn();

    $fidPedido   = explode("@@@",$_POST["id_pedido"]);
    $fstatus     = explode("@@@",$_POST["pgt_status"]);
    $fvalor      = explode("@@@",$_POST["pgt_valorpago"]);
    $fforma      = explode("@@@",$_POST["pgt_forma"]);
    $fcomentario = explode("@@@",$_POST["pgt_comentario"]);
    
    
    if (count($fidPedido) != count($fcomentario) || count($fstatus) != count($fcomentario) || count($fvalor) != count($fcomentario) || count($fforma) != count($fcomentario)) {
        echo "Erro ao salvar dados. Arrays com dimens천es erradas";
        echo count($fidProdutos)."/".count($fstatus)."/".count($fvalor)."/".count($fforma)."/".count($fcomentario);

    } else {
        $erros = "";
        for($i = 0; $i < count($fidPedido); $i++) {
            $idPedido = $fidPedido[$i];
            $status = $fstatus[$i];
            $valor = $fvalor[$i];
            $valor = str_replace(",",".",$valor);
            $valor = str_replace("R$","",$valor);
            $forma = $fforma[$i];
            $comentario = $fcomentario[$i];
            $comentario = addslashes($comentario);
            
            $sql = "SELECT * FROM pedidos_consolidados WHERE pedido_id = ".$idPedido;
            $st = $conn->prepare($sql);
            $st->execute();
            
            if ($st->rowCount() == 0) {
                $erros .= "Pedido não encontrado (".$idPedido.")";
                setlog("log_pagamentos.txt",$_SESSION["login"]." tentou alterar pagamento de pedido inexistente (id=".$idPedido.")",$sql);
            } else {
                $rs = $st->fetch();
                $dadosAnteriores = $rs["pgt_status"]."/".$rs["pgt_valorpago"]."/".$rs["pgt_forma"]."/".$rs["pgt_comentario"];
                $dadosDepois = $status."/".str_replace(".",",",$valor)."/".$forma."/".stripslashes($comentario);
                $sql = "UPDATE pedidos_consolidados SET pgt_status = ".$status.", pgt_valorpago=".$valor.", pgt_forma='".$forma."', pgt_comentario='".$comentario."', pgt_aprovador='".$_SESSION["login"]."' WHERE pedido_id = ".$idPedido;
                $st = $conn->prepare($sql);
                if ($st->execute()) {
                    setlog("log_pagamentos.txt",$_SESSION["login"]." alterou pagamentos do pedido ".$idPedido." Status/valor/forma/comentario antes: ".$dadosAnteriores." | Depois ".$dadosDepois,$sql);
                } else {
                    $erros .= "Erro ao salvar os dados (".$sql.")";
                }
            }
        }
        if ($erros != "") {
            echo $erros;
        } else {
            echo "ok";
        }
    }
} else {
    if (isset($_POST["act"])) {
        switch($_POST['act']) {
            case 'gerar_links_todos':
                    setlog("log_pagamentos.txt",$_SESSION["login"]." disparou geração de links de pagamentos pendentes para dia".$_SESSION["data_consulta"],"");
                    $oPix = new Pix();
                    $gerarCodigos = $oPix->GerarCopiaColaPendentes();
                    if ($gerarCodigos != "") {
                        echo $gerarCodigos;
                        setlog("log_pagamentos.txt","Erro ao gerar links pendentes","");
                    } else {
                        echo "ok";
                        setlog("log_pagamentos.txt","Links pendentes gerados","");
                    }
            break;
            
            case 'regerar_links_todos':
                    setlog("log_pagamentos.txt",$_SESSION["login"]." disparou geração NOVA de links de pagamentos pendentes para dia".$_SESSION["data_consulta"],"");
                    $oPix = new Pix();
                    $gerarCodigos = $oPix->GerarCopiaColaTodos();
                    if ($gerarCodigos != "") {
                        echo $gerarCodigos;
                        setlog("log_pagamentos.txt","Erro ao gerar links pendentes","");
                    } else {
                        echo "ok";
                        setlog("log_pagamentos.txt","Links pendentes gerados","");
                    }
            break;
            
            case 'gerar_link_unico':
                if (isset($_POST['atualizacao_link'])) {
                    $msg = "atualização do";
                } else {
                    $msg = "criação do primeiro";
                }
                setlog("log_pagamentos.txt",$_SESSION["login"]." disparou ".$msg." de link de pagamento para o pedido ".$_POST['pedido_id']." do dia".$_SESSION["data_consulta"],"");
                $oPix = new Pix();
                $gerarCodigos = $oPix->GerarCopiaColaUnico($_POST['pedido_id']);
                if ($gerarCodigos != "") {
                    echo $gerarCodigos;
                    setlog("log_pagamentos.txt","Erro ao gerar link de pagamento","");
                } else {
                    echo "ok";
                    setlog("log_pagamentos.txt","Link de pagamento gerado","");
                }
            break;
            
            case 'apagar_link':
                setlog("log_pagamentos.txt",$_SESSION["login"]." solicitou remoção do link de pagamento para o pedido ".$_POST['pedido_id']." do dia".$_SESSION["data_consulta"],"");
                $oPix = new Pix();
                $apagarPix = $oPix->ApagarLinkPagamento($_POST["pedido_id"]);
                if ($apagarPix != "") {
                    echo $apagarPix;
                    setlog("log_pagamentos.txt","Erro ao apagar link de pagamento","");
                } else {
                    echo "ok";
                    setlog("log_pagamentos.txt","Link de pagamento apagado","");
                }
            break;
        }
    } else {
        echo "Erro ao salvar os dados";
    }
}
?>