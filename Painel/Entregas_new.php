<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//$levelRequired=10000;
$levelRequired = 2000; //alterado para 2000 para dar permiss�o aos bikers
include "../config.php";
include "helpers.php";
include "acesso.php";
include "menu.php";

var_dump($_SESSION);
?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            table, td {
                border: 1px solid black;
                border-collapse: collapse;
                text-align: center;
            }
            .firstLine {
                background: #11479e;
                color:#fff;
                font-weight:bold;
            }
        </style>
        <title>Livres - Comboio Orgânico</title>
        <link rel="stylesheet" href="css/foundation.css">
        <link rel="stylesheet" href="css/app.css">
    </head>
    <body>
        <?php
        $conn = new PDO("mysql:host=" . $c_db["host"] . ";dbname=" . $c_db["name"], $c_db["user"], $c_db["password"]);
        include "menu_selecao_data.php";

        if (isset($_GET["data"])) {
            if (strlen($_GET["data"]) > 0) {
                $sql = "SELECT * FROM Calendario WHERE id = " . $_GET["data"];
                $st = $conn->prepare($sql);
                $st->execute();
                $rs = $st->fetchAll();
                $dataEntrega = $rs[0]["data"];
                $dataEntrega = strtotime($dataEntrega);
                $frequencias = getFrequencias($conn, $_GET["data"]);
                ?>
                <table style="vertical-align:top;">
                    <?php
                    //Dicion�rio de produtos
                    $sql = "SELECT * FROM produtos ORDER BY nome";
                    $st = $conn->prepare($sql);
                    $st->execute();
                    $rs = $st->fetchAll();
                    foreach ($rs as $row) {
                        $produtos[$row["id"]]["produto"] = $row["nome"];
                        $produtos[$row["id"]]["unidade"] = $row["unidade"];
                        $produtos[$row["id"]]["preco"] = $row["preco"];
                    }
                    //Pedido Fixo
                    $sql = getSQLPedidoSemana($dataEntrega, $frequencias, 1, "consumidor");
                    $st = $conn->prepare($sql);
                    $st->execute();
                    $rs = $st->fetchAll();
                    $totalGeral = 0;
                    $pedidos = "";
                    foreach ($rs as $row) {
                        if (!is_array($pedidos) || !array_key_exists($row["IDConsumidor"], $pedidos)) {
                            $pedidos[$row["IDConsumidor"]]["consumidor"] = $row["consumidor"];
                            $pedidos[$row["IDConsumidor"]]["email"] = $row["email"];
                            $pedidos[$row["IDConsumidor"]]["cpf"] = $row["cpf"];
                            $pedidos[$row["IDConsumidor"]]["cota"] = $row["cota_imediato"];
                            $pedidos[$row["IDConsumidor"]]["IDConsumidor"] = $row["IDConsumidor"];
                            $pedidos[$row["IDConsumidor"]]["comunidade"] = $row["comunidade"];
                            $pedidos[$row["IDConsumidor"]]["mensal"] = 0;
                        }
                        if (!array_key_exists("produtos", $pedidos[$row["IDConsumidor"]])) {
                            $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["quantidade"] = 0;
                        }
                        if (!array_key_exists($row["IDProduto"], $pedidos[$row["IDConsumidor"]]["produtos"])) {
                            $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["quantidade"] = 0;
                        }
                        $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["produto"] = $row["nome"];
                        $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["quantidade"]+=$row["Quantidade"];
                        $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["unidade"] = $row["unidade"];
                        $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["frequencia"] = $row["Frequencia"];
                        $pedidos[$row["IDConsumidor"]]["produtos"][$row["IDProduto"]]["codigo"] = $row["IDProduto"];
                        if (strtolower($row["Frequencia"]) == "mensal") {
                            $pedidos[$row["IDConsumidor"]]["mensal"] += $row["preco"] * $row["Quantidade"];
                        }
                    }
                    //Pedidos Vari�veis
                    $sql = "SELECT * FROM PedidosVar";
                    $sql .= " LEFT JOIN Consumidores ON PedidosVar.idConsumidor = Consumidores.id WHERE Consumidores.ativo = 1 AND PedidosVar.idCalendario = " . $_GET["data"];
                    $st = $conn->prepare($sql);
                    $st->execute();
                    if ($st->rowCount() > 0) {
                        $rs = $st->fetchAll();
                        $variaveis = "";
                        foreach ($rs as $row) {
                            if (!array_key_exists($row["idConsumidor"], $pedidos)) {
                                $erro = "Erro catastr�fico. Consumidor com vari�vel e sem pedido fixo. Opera��o abortada";
                                $erro .= "<br>" . $row["consumidor"] . " C�digo: " . $row["idConsumidor"];
                                exit($erro);
                            }
                            $pedidos[$row["idConsumidor"]]["delivery"] = $row["delivery"];
                            //Op��o 1
                            if (!is_null($row["escolhaOpcao1"]) && strlen($row["escolhaOpcao1"]) > 0) {
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["produto"] = $produtos[$row["escolhaOpcao1"]]["produto"];
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["unidade"] = $produtos[$row["escolhaOpcao1"]]["unidade"];
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["frequencia"] = "Vari�vel";
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["codigo"] = $row["escolhaOpcao1"];
                                if (!array_key_exists("quantidade", $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]])) {
                                    $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["quantidade"] = $row["quantidadeOpcao1"];
                                } else {
                                    $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao1"]]["quantidade"] += $row["quantidadeOpcao1"];
                                }
                            }
                            //Op��o 2
                            if (!is_null($row["escolhaOpcao2"]) && strlen($row["escolhaOpcao2"]) > 0) {
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["produto"] = $produtos[$row["escolhaOpcao2"]]["produto"];
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["unidade"] = $produtos[$row["escolhaOpcao2"]]["unidade"];
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["frequencia"] = "Vari�vel";
                                $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["codigo"] = $row["escolhaOpcao2"];
                                if (!array_key_exists("quantidade", $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]])) {
                                    $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["quantidade"] = $row["quantidadeOpcao2"];
                                } else {
                                    $variaveis[$row["idConsumidor"]]["produtos"][$row["escolhaOpcao2"]]["quantidade"] += $row["quantidadeOpcao2"];
                                }
                            }
                        }
                    }
                    //Montar lista
                    $contador = 0;
                    foreach ($pedidos as $idConsumidor => $dados) {
                        $contador++;
                        $totalMensal = 0;
                        echo "<p>";
                        echo "<b>" . $contador . ") Consumidor: " . ucwords(strtolower($dados["consumidor"])) . " (c�digo: " . $dados["IDConsumidor"] . " - " . $dados["comunidade"] . "� Comunidade)</b><br>";
                        echo "Entrada de sacolas:<br>";
                        echo "Sa�da de sacolas:<br>";
                        echo "Forma Entrega: ";
                        if (array_key_exists("delivery", $dados)) {
                            if (strtolower($dados["delivery"]) == "sim") {
                                echo "Delivery<br>";
                            }
                            if (strtolower($dados["delivery"]) == "n�o") {
                                echo "Retirada<br>";
                            }
                            if (strtolower($dados["delivery"]) == "n�o sei ainda") {
                                echo "N�o escolhido ainda<br>";
                            }
                        } else {
                            echo "Ainda n�o definido<br>";
                        }
                        echo "Email: " . $dados["email"] . "<br>";
                        echo "CPF: " . formatCPF($dados["cpf"]) . "<br>";
                        echo "Cota: R$" . number_format($dados["cota"], 2, ",", ".");
                        if ($dados["mensal"] > 0) {
                            echo " (Cesta Mensal: R$" . number_format($dados["mensal"], 2, ",", ".") . ")";
                        }
                        echo "<br>Pedido: ";
                        //Produtos cesta fixa
                        foreach ($dados["produtos"] as $idProduto => $dadosProduto) {
                            echo "<br>";
                            if (strlen($dadosProduto["quantidade"]) == 0) {
                                echo "??";
                            } else {
                                echo $dadosProduto["quantidade"];
                            }
                            echo " " . $dadosProduto["unidade"] . " x ";
                            echo $dadosProduto["produto"] . " (" . strtoupper(substr($dadosProduto["frequencia"], 0, 1)) . " - c�digo " . $idProduto . ")";
                        }
                        //Produtos cesta vari�vel
                        if (is_array($variaveis)) {
                            if (array_key_exists($idConsumidor, $variaveis)) {
                                foreach ($variaveis[$idConsumidor]["produtos"] as $idProduto => $dadosProduto) {
                                    echo "<b>";
                                    echo "<br>";
                                    if (strlen($dadosProduto["quantidade"]) == 0) {
                                        echo "??";
                                    } else {
                                        echo $dadosProduto["quantidade"];
                                    }
                                    if (strtolower($dadosProduto["unidade"]) == "d�zia") {
                                        echo " unidade x ";
                                    } else {
                                        echo " " . $dadosProduto["unidade"] . " x ";
                                    }
                                    echo $dadosProduto["produto"] . " (" . strtoupper(substr($dadosProduto["frequencia"], 0, 1)) . " - c�digo " . $idProduto . ")";
                                    echo "</b>";
                                }
                            }
                        }
                        echo "</p>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>