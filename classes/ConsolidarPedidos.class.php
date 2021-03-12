<?php
class ConsolidarPedidos extends Livres {
    
    private $limite = 1; //número de pedidos consolidados por vez

    public function __construct($dataEntrega = "") {
        if ($dataEntrega != "") {
            $this->dataEntrega = $this->dataParaTime($dataEntrega);
            //echo "<hr>".$this->dataEntrega."<hr>";
        }
    }

    public function consolidarAgoraTudo() {
        $this->populaPedidosCestaFixa();
        $this->populaItensCestaFixa();
        $this->populaItensCestaVariavel();
        $this->atualizaValores();
        $this->apagaPedidosSemItens();

        return true;
    }

    public function consolidarAgoraVariavel() {
        $this->populaItensCestaVariavel();

        return true;
    }

    private function apagaPedidosSemItens() {
        $sql = "SELECT ped.pedido_id as pedido_id FROM pedidos_consolidados ped LEFT JOIN pedidos_consolidados_itens it
                ON it.pedido_id = ped.pedido_id
                WHERE it.pedido_id IS null";
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) { return false; }

        $rs = $st->fetchAll();
        foreach ($rs as $row) {
            $sql = "DELETE FROM pedidos_consolidados WHERE pedido_id = ".$row["pedido_id"];
            $st = $this->conn()->prepare($sql);
            $st->execute();
        }
    }

    private function apagaPedido($idConsumidor) {
        $sql = "DELETE  FROM pedidos_consolidados WHERE consumidor_id = ? and pedido_data = ?";
        $st = $this->conn()->prepare($sql);
        $dados = [
            $idConsumidor,
            $this->dataTimeParaString($this->dataEntrega)
        ];
        $st->execute($dados);
    }

    private function apagaItensFixos($idConsumidor) {
        $sql = "SELECT pedido_id, consumidor_id, pedido_data FROM pedidos_consolidados WHERE consumidor_id = ? and pedido_data = ?";
        $st = $this->conn()->prepare($sql);
        $dados = [
            $idConsumidor,
            $this->dataTimeParaString($this->dataEntrega)
        ];
        $st->execute($dados);

        if ($st->rowCount() > 0) {
            $rs = $st->fetch();
            $idPedido = $rs["pedido_id"];

            $sql = "DELETE FROM pedidos_consolidados_itens WHERE pedido_id = ? AND item_tipo_cesta = ?";
            $st = $this->conn()->prepare($sql);
            $st->execute(array($idPedido, "fixa"));
        }
    }

    //acao = insert/update | dados = array
    private function novoItem($acao, $dados) {
        if ($acao == 'update') {
            //item já cadastrado, realizar update
            $sql = "UPDATE pedidos_consolidados_itens SET admin_id_consolidado = :admin_id_consolidado, item_qtde = :item_qtde, item_tipo = :item_tipo,
                    item_produto = :item_produto, item_valor = :item_valor, item_produtor = :item_produtor, item_valor_produtor = :item_valor_produtor,
                    item_tipo_cesta = :item_tipo_cesta, item_freq_cesta = :item_freq_cesta 
                    WHERE pedido_id = :pedido_id AND produto_id = :produto_id AND item_id = :item_id";
            
        }
        if ($acao == 'insert') {
            //cadastrar item
            $sql = "INSERT INTO pedidos_consolidados_itens (pedido_id, admin_id_consolidado, produto_id, item_qtde, item_tipo, 
                            item_produto, item_valor, item_produtor, item_valor_produtor, item_tipo_cesta, item_freq_cesta)
                            VALUES (:pedido_id, :admin_id_consolidado, :produto_id, :item_qtde, :item_tipo, 
                            :item_produto, :item_valor, :item_produtor, :item_valor_produtor, :item_tipo_cesta, :item_freq_cesta)";
        }
        if (isset($sql)) {
            $st = $this->conn()->prepare($sql);
            /*echo $sql."<hr>";
            echo '<pre>';
            print_r($dados);
            echo '</pre>';*/            
            $st->execute($dados);
        }
    }

    private function cadastraPedido($consumidor) {
        $sql = "INSERT INTO pedidos_consolidados (consumidor_id, pedido_cota, pedido_fixa, pedido_variavel, pedido_mensal, pedido_endereco, 
                                    pedido_retirada, pedido_entrega_valor, pedido_data, pedido_consolidado) ";
        $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";    
        $dados = [
            $consumidor["id"],
            0,
            0,
            0,
            0,
            $consumidor["endereco"],
            3,
            0,
            $this->dataTimeParaString($this->dataEntrega),
            0
        ];
        $st = $this->conn()->prepare($sql);
        $st->execute($dados);
    }

    private function recadastraPedido($consumidor) {
        $sql = "UPDATE pedidos_consolidados SET pedido_cota = ?, pedido_fixa = ?, pedido_variavel = ?, pedido_mensal = ?, pedido_endereco = ?, 
                pedido_retirada = ?, pedido_entrega_valor = ?, pedido_consolidado = ? WHERE consumidor_id = ? AND pedido_data = ?";
        $dados = [
            0,
            0,
            0,
            0,
            $consumidor["endereco"],
            3,
            0,
            0,
            (int) $consumidor["id"],
            $this->dataTimeParaString($this->dataEntrega)
        ];
        $st = $this->conn()->prepare($sql);
        $st->execute($dados);
    }

    private function atualizaPedido($dados) {
        $sql = "UPDATE pedidos_consolidados SET pedido_cota = :pedido_cota, pedido_fixa = :pedido_fixa, pedido_variavel = :pedido_variavel, 
                pedido_mensal = :pedido_mensal WHERE pedido_id = :pedido_id";
        $st = $this->conn()->prepare($sql);
        /*echo $sql."<hr>";
        echo "<pre>";
        print_r($dados);
        echo "</pre>";*/
        $st->execute($dados);
    }

    private function populaItensCestaFixa() {
        //Pedido Fixo cadastrado
        $oPedidos = new Pedidos();
        $itensFixosRS = $oPedidos->listarPedidoFixoSemana($this->dataEntrega);

        //transformar array de itens fixos de forma que seja fácil consultar se determinado produto de determinado consumidor está nele
        foreach ($itensFixosRS as $row) {
            $itensFixosSimples[$row["IDConsumidor"]][$row["IDProduto"]] = $row["Quantidade"];
        }

        //Lista de produtos ativos
        $oProdutos = new Produtos();
        $produtos = $oProdutos->listarProdutosAtivos($this->dataEntrega);

        $unidades = $oProdutos->unidades("unidade");

        //Produtores
        $oProdutores = new Produtores();
        $produtores = $oProdutores->listaProdutoresPorNome();

        //Lista dos pedidos consolidados cadastrados no banco
        $oPedidosCons = new PedidosConsolidados($this->dataEntrega);
        $pedidosConsRS = $oPedidosCons->listaPedidos(array("pedido_id", "consumidor_id"));

        //transformar array de pedidos de forma que seja fácil consultar o ID do pedido pelo ID do consumidor
        if (is_array($pedidosConsRS)) {
            foreach ($pedidosConsRS as $row) {
                $pedidosCons[$row["consumidor_id"]] = $row["pedido_id"];
            }
        } else {
            $pedidosCons[] = "";
        }
        
        /*print_r($pedidosCons);*/

        //Lista dos itens consolidados cadastrados no banco
        $itensConsRS = $oPedidosCons->listaItensFixos();

        //Transformar array de itens de forma que seja possível consultar fácil se produto está cadastrado para determinado pedido
        if (is_array($itensConsRS)) {
            foreach ($itensConsRS as $row) {
                $itensCons[$row["pedido_id"]][$row["produto_id"]] = $row["item_id"];
            }
        } else {
            $itensCons[] = "";
        }

        //Confrontar itens cadastrados no pedido consolidado com array de pedidos da cesta. Caso não existam, apagar item consolidado
        if (is_array($itensConsRS) && is_array($itensFixosSimples)) {
            foreach ($itensConsRS as $row) {
                $idConsumidor = $row["consumidor_id"];
                $idProduto = $row["produto_id"];
                $itemId = $row["item_id"];
                /*
                CÓDIGO PARA AJUDAR NO DEBUG EM 16/06/2020 09:10
                if ($idConsumidor == 173 && $idProduto == 102) {
                    echo $idConsumidor." - ".$idProduto.'<hr>';
                    echo '<pre>';
                    print_r($itensFixosSimples);
                    echo '</pre>';
                    // echo '<pre>';
                    // print_r($itensFixosSimples[$idConsumidor][$idProduto]);
                    // echo '</pre>';
                    echo $row["item_tipo_cesta"];
                }*/               
                if ((!array_key_exists($idConsumidor, $itensFixosSimples) || !array_key_exists($idProduto, $itensFixosSimples[$idConsumidor])) && $row["item_tipo_cesta"] == "fixa") {
                    //item existe no pedido consolidado, é do tipo de cesta fixa, porém não existe mais no pedido de cesta fixa do consumidor (ou foi removido, ou não está disponível para semana)
                    //portanto, apagar da tabela de pedido consolidado
                    $sql = "DELETE FROM pedidos_consolidados_itens WHERE item_id = ".$itemId;
                    $st = $this->conn()->prepare($sql);
                    $st->execute();
                }
            }
        }

        //Percorrer itens da cesta e cadastrá-los no pedido consolidado (ou atualizar dados, caso já cadastrados)
        foreach ($itensFixosRS as $item) { //2
            $idProduto = $item["IDProduto"];
            $idPedido = $pedidosCons[$item["IDConsumidor"]]; //1
            $idAdmin = 1;
            $produto = $produtos[$idProduto];

            $dados = [
                ":pedido_id"            => $idPedido,
                ":admin_id_consolidado" => $idAdmin,
                ":produto_id"           => $idProduto,
                ":item_qtde"            => $item["Quantidade"],///$produto["multiplicador_unidade2"],
                ":item_produto"         => $produto["nome"],
                ":item_valor"           => $produto["preco"],//*$produto["multiplicador_unidade2"],
                ":item_produtor"        => $produtores[$produto["produtor"]],
                ":item_valor_produtor"  => $produto["preco_produtor"],
                ":item_tipo"            => $unidades[$produto["unidade"]],//$unidades[$produto["unidade2"]],
                ":item_tipo_cesta"      => "fixa",
                ":item_freq_cesta"      => strtolower($item["Frequencia"])
            ];
            if (is_array($itensCons) && is_array($pedidosCons) && array_key_exists($idPedido, $itensCons) && array_key_exists($idProduto, $itensCons[$idPedido])) {
                $dados[":item_id"] = $itensCons[$idPedido][$idProduto];
                $this->novoItem('update', $dados);
            } else {
                $this->novoItem('insert', $dados);
            }
        }

    }

    private function populaPedidosCestaFixa() {
        //Obter consumidores ativos
        $oConsumidores = new Consumidores();
        $consumidores = $oConsumidores->consumidoresAtivos("comunidade, consumidor");

        //Pedido Fixo cadastrado
        $oPedidos = new Pedidos();
        $pedidosFixos = $oPedidos->listarPedidoFixoSemana($this->dataEntrega);

        //Criar lista de consumidores com cesta na semana (obter IDs únicos de consumidores a partir dos pedidos da semana)
        foreach ($pedidosFixos as $pedido) {
            $pedidos[$pedido["IDConsumidor"]] = $pedido["IDConsumidor"];
        }
        $pedidos = array_unique($pedidos);

        //Obter lista dos pedidos cadastrados no banco para não ter cadastros duplicados
        $oPedidosCons = new PedidosConsolidados($this->dataEntrega);
        $pedidosCons = $oPedidosCons->listaPedidos("consumidor_id");

        //Verificar e caso não exista pedido fixo, apagar itens de cesta fixa que estão no banco consolidados, mas não estão na cesta da semana
        if (is_array($pedidosCons)) {
            foreach ($pedidosCons as $idConsumidor) {
                if (is_array($pedidos) && !array_key_exists($idConsumidor, $pedidos)) {
                    $this->apagaItensFixos($idConsumidor);
                }
            }
        }

        //Cadastrar pedidos que ainda não estão no banco
        foreach ($pedidos as $idConsumidor) {
            //cadastrar pedido no banco caso ainda não esteja cadastrado
            if (!is_array($pedidosCons) || array_search($idConsumidor, $pedidosCons) === false) {
                $this->cadastraPedido($consumidores[$idConsumidor]);
                //echo "cadastrar: ".$idConsumidor."<br>";
            } else {
                $this->recadastraPedido($consumidores[$idConsumidor]);
                //echo "recadastrar: ".$idConsumidor."<br>";
            }
        }
    }

    private function populaItensCestaVariavel() {
        //Pedido variável da semana
        $pedidos = new Pedidos();
        $variaveis = $pedidos->listarPedidoVariavelSemana($this->dataEntrega);
        
        //produtos cadastrados
        $oProdutos = new Produtos();
        $produtos = $oProdutos->listarProdutosTodos();

        //produtores cadastrados
        $oProdutores = new Produtores();
        $produtores = $oProdutores->listaProdutoresPorNome();

        //unidades de medida
        $unidades = $oProdutos->unidades('unidade');

        //Obter lista dos pedidos cadastrados no banco (se não tiver pedido para consumidor, não inserir variável)
        $oPedidosCons = new PedidosConsolidados($this->dataEntrega);
        $pedidosConsRS = $oPedidosCons->listaPedidos(array("pedido_id", "consumidor_id"));

        //transformar array de pedidos de forma que seja fácil consultar o ID do pedido pelo ID do consumidor
        if (is_array($pedidosConsRS)) {
            foreach ($pedidosConsRS as $row) {
                $pedidosCons[$row["consumidor_id"]] = $row["pedido_id"];
            }
        } else {
            $pedidosCons[] = "";
        }

        //Obter lista dos itens variáveis consolidados cadastrados no banco
        $itensConsRS = $oPedidosCons->listaItensVariaveis();

        //Transformar array de itens de forma que seja possível consultar fácil se produto está cadastrado para determinado pedido
        if (is_array($itensConsRS)) {
            foreach ($itensConsRS as $row) {
                $itensCons[$row["pedido_id"]][$row["produto_id"]] = $row["item_id"];
            }
        } else {
            $itensCons[] = "";
        }
        
        //Loop nos itens variáveis cadastrados no banco para confirmar que eles ainda estão no pedido
        if (is_array($itensConsRS)) {
            foreach ($itensConsRS as $item) {
                $idConsumidor = $item["consumidor_id"];
                if (is_array($variaveis) && array_key_exists($idConsumidor, $variaveis)) {
                    //checar se este produto específico que já está no banco ($item) ainda está no pedido de variáveis
                    $itens = $variaveis[$idConsumidor];
                    $apagar = true;
                    foreach ($itens["item"] as $pedidoVariavel) {
                        if ($pedidoVariavel["idProduto"] == $item["produto_id"] && $pedidoVariavel["quantidade"] > 0) {
                            $apagar = false;
                        }
                    }
                    if ($apagar) {
                        //Se cair aqui, quer dizer que o produto cadastrado no pedido consolidado ($item) não consta mais no pedido do consumidor e deve ser apagado do banc
                        $sql = "DELETE FROM pedidos_consolidados_itens WHERE item_id = ".$item["item_id"];
                        $st = $this->conn()->prepare($sql);
                        $st->execute();
                    }
                } else {
                    //produto está no pedido consolidado, mas consumidor não tem mais pedido variável cadastrado, então apagar do consolidado
                    $sql = "DELETE FROM pedidos_consolidados_itens WHERE item_id = ".$item["item_id"];
                    $st = $this->conn()->prepare($sql);
                    $st->execute();
                }
            }
        }

        $x=0;
        //Loop nos itens variáveis no pedido dos consumidores para cadastrar ou atualizar no banco consolidado
        if (is_array($variaveis)) {
            foreach ($variaveis as $idConsumidor=>$variavel) {
                $delivery = $variavel["delivery"];
                $idPedido = "";
                foreach ($variavel["item"] as $item) {
                    //verificar se existe pedido para este consumidor
                    //caso não haja, não deve ser criado pedido só para variável
                    if (is_array($pedidosCons) && array_key_exists($idConsumidor, $pedidosCons)) {
                        $idPedido = $pedidosCons[$idConsumidor];
                        $idProduto = $item["idProduto"];
                        $produto = $produtos[$idProduto];
                        $idAdmin = 1;
                        $dados = [
                            ":pedido_id"            => $idPedido,
                            ":admin_id_consolidado" => $idAdmin,
                            ":produto_id"           => $idProduto,
                            ":item_qtde"            => $item["quantidade"],///$produto["multiplicador_unidade2"],
                            ":item_produto"         => $produto["nome"],
                            ":item_valor"           => $produto["preco"],//*$produto["multiplicador_unidade2"],
                            ":item_produtor"        => $produtores[$produto["produtor"]],
                            ":item_valor_produtor"  => $produto["preco_produtor"],
                            ":item_tipo"            => $unidades[$produto["unidade"]],//$unidades[$produto["unidade2"]],
                            ":item_tipo_cesta"      => "variavel",
                            ":item_freq_cesta"      => 'variavel',
                        ];
                        if (is_array($itensCons) && is_array($pedidosCons) && array_key_exists($idPedido, $itensCons) && array_key_exists($idProduto, $itensCons[$idPedido])) {
                            //item existente, atualizar
                            $dados[":item_id"] = $itensCons[$idPedido][$idProduto];
                            $this->novoItem('update',$dados);
                        } else {
                            //novo item, inserir
                            $this->novoItem('insert',$dados);
                        }                    
                    }
                }
                //atualizar pedido para incluir forma de entrega (já que este dado é preenchido com o variável)
                //atualizar caso a variável $idPedido tenha valor (ou seja, existe pedido para este variável)
                if ($idPedido != "") {
                    $delivery = $this->dadosDelivery($delivery);
                    $sql = "UPDATE pedidos_consolidados SET pedido_retirada = ".$delivery["id"].",
                            pedido_entrega_valor = ".$delivery["valor_entrega"]."
                            WHERE pedido_id = ".$idPedido;
                    $st = $this->conn()->prepare($sql);
                    $st->execute();
                }
            }
        }
    }

    private function atualizaValores() {
        $oPedidosCons = new PedidosConsolidados($this->dataEntrega);
        $itensCons = $oPedidosCons->listaItensTodos();

        if (is_array($itensCons)) {
            foreach ($itensCons as $row) {
                if (!isset($totais) || !array_key_exists($row["pedido_id"],$totais)) {
                    $totais[$row["pedido_id"]]["fixa"]["semanal"] = 0;
                    $totais[$row["pedido_id"]]["fixa"]["quinzenal"] = 0;
                    $totais[$row["pedido_id"]]["fixa"]["mensal"] = 0;
                    $totais[$row["pedido_id"]]["variavel"]["variavel"] = 0;
                    $totais[$row["pedido_id"]]["avulso"]["avulso"] = 0;
                    $totais[$row["pedido_id"]]["pre"]["pre"] = 0;
                }                
                $totais[$row["pedido_id"]][$row["item_tipo_cesta"]][$row["item_freq_cesta"]] += $row["item_valor"]*$row["item_qtde"];
            }
        }

        if (is_array($totais)) {
            foreach ($totais as $idPedido => $valor) {
                $fixoSemana = $valor["fixa"]["semanal"]+$valor["fixa"]["quinzenal"];
                $pedido_mensal = $valor["fixa"]["mensal"];
                $variavelSemana = $valor["variavel"]["variavel"];
                if ($fixoSemana == 0) {
                    $cotaSemana = $this->cotaIdeal($pedido_mensal);
                } else {
                    $cotaSemana = $this->cotaIdeal($fixoSemana);
                }
                
                $cotaVariavel = $cotaSemana-$fixoSemana;
                $pedido_variavel = $variavelSemana;                

                $dados = [
                    ":pedido_id"            => $idPedido,
                    ":pedido_cota"          => $cotaSemana,
                    ":pedido_fixa"          => $fixoSemana,
                    ":pedido_variavel"      => $pedido_variavel,
                    ":pedido_mensal"        => $pedido_mensal,
                ];

                $this->atualizaPedido($dados);
            }
        }
    }
    
    public function totalPedidosConsolidadosPorData() {

        $oCalendario = new Calendario();
        $datasRs = $oCalendario->listaDatas();

        foreach ($datasRs as $id=>$data)  {
            $datas[$id] = [
                "data_id"   => $id,
                "data"      => $data,
            ];                
        }
        
                
        $sql = "SELECT it.item_tipo_cesta, COUNT(ped.pedido_id) AS totalPedidos, cal.id as dataId, cal.data as data
        FROM Calendario cal
        LEFT JOIN pedidos_consolidados ped
        ON cal.data = ped.pedido_data
        LEFT JOIN pedidos_consolidados_itens it
        ON ped.pedido_id = it.pedido_id
        GROUP BY cal.id,it.item_tipo_cesta";
        
        $st = $this->conn()->prepare($sql);
        $st->execute();

        if ($st->rowCount() == 0) return false;

        $rs = $st->fetchAll();

        foreach ($rs as $row) {
            if (array_key_exists($row["dataId"],$datas)) {
                $datas[$row["dataId"]][$row["item_tipo_cesta"]] = $row["totalPedidos"];
            }
        }
        return $datas;
    }

}
?>