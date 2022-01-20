<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config.php";
include "../Painel/helpers.php";
include "../Painel/acesso.php";
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link href="../css/style.css" rel="stylesheet">
    </head>
<body>
    
    <div class="container-fluid">
	<div class="row">
	    <!-- MENU SUPERIOR -->
		<div class="col-md-12">
			<ul class="nav">
				<li class="nav-item">
					<a class="nav-link active" href="index.php">Início do Caixa</a>
				</li>
				<li class="nav-item">
					<a class="nav-link active" href="../Painel">Voltar para o Painel</a>
				</li>
				<!--
				<li class="nav-item">
					<a class="nav-link" href="#">Profile</a>
				</li>
				<li class="nav-item">
					<a class="nav-link disabled" href="#">Messages</a>
				</li>
				<li class="nav-item dropdown ml-md-auto">
					 <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink" data-toggle="dropdown">Dropdown link</a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
						 <a class="dropdown-item" href="#">Action</a> <a class="dropdown-item" href="#">Another action</a> <a class="dropdown-item" href="#">Something else here</a>
						<div class="dropdown-divider">
						</div> <a class="dropdown-item" href="#">Separated link</a>
					</div>
				</li>
				-->
			</ul>
			<!-- FIM MENU SUPERIOR -->
		</div>
	</div>
<?php
//Variáveis de controle
$livres = new Livres();
$oCaixa = new Caixa();

$conn = $livres->conn();
$idAdmin = $_SESSION["id"];
$nowStamp = strtotime(date("Y-m-d H:i:s")) - 3*60*60;
$nowStr = date("Y-m-d H:i:s", $nowStamp);
$todayStr = date("Y-m-d", $nowStamp);


function isDate($date) {
    if (DateTime::createFromFormat('Y-m-d', $date) !== FALSE) {
        return true;
    }
    return false;
}

//Abertura de caixa sendo realizada
if (isset($_POST["abrirCaixa"])) {
    if (strlen($_POST["valor"]) == 0 )  {
        echo "<p>Preencha o valor de abertura (preencha com 0 se necessário)</p>";
    } else {
        if ($oCaixa->listarCaixasAbertos()) {
            echo "<p>Já existe um caixa aberto. Feche primeiro antes de abrir outro.</p>";
        } else {
            //Salvar abertura de caixa
            /*$dataAbertura = date("Y-m-d H:i:s");
            $valorAbertura = $_POST["valor"];
            $valorAbertura = str_replace("R","",$valorAbertura);
            $valorAbertura = str_replace("$","",$valorAbertura);
            $valorAbertura = str_replace(" ","",$valorAbertura);
            $valorAbertura = str_replace(",",".",$valorAbertura);
            $sql = "INSERT INTO Caixa (id_admin, dataAbertura, valorAbertura) VALUES (".$idAdmin.",'".$dataAbertura."', ".$valorAbertura.")";
            $st = $conn->prepare($sql);
            if ($st->execute()) {
                echo "<p>Caixa aberto</p>";
            } else {
                echo "<p>Erro ao abrir o caixa.</p>";
            }*/
            if ($oCaixa->abrirCaixa($idAdmin, $_POST["valor"])) {
                echo "<p>Caixa aberto</p>";
            } else {
                echo "<p>Erro ao abrir o caixa.</p>";
            }
        }
    }
}

if (isset($_GET["fecharCaixa"]) && isset($_POST["id_fechar_caixa"]) && isset($_POST["valor_fechar_caixa"])) {
    if ($oCaixa->fecharCaixa($_POST["id_fechar_caixa"], $_POST["valor_fechar_caixa"])) {
        echo "<p>Caixa fechado</p>";
    } else {
        echo "<p>Falha ao fechar caixa</p>";
    }
}
//Selecionar caixa
if (isset($_GET["selecionarCaixa"])) {
    //$_SESSION["idCaixa"] = 0;
    $idCaixa = $_GET["selecionarCaixa"];
    $sql = "SELECT * FROM Caixa WHERE id = ".$idCaixa;
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 0) {
        echo "<p>Caixa não encontrado</p>";
    } else {
        $rs = $st->fetch();
        if (is_null($rs["dataFechamento"])) {
            //$_SESSION["idCaixa"] = $idCaixa;
        } else {
            echo "<p>O caixa selecionado já está fechado;</p>";
        }
    }
}
//Buscar se existe caixa aberto, caso nenhum caixa esteja selecionado
if (!isset($_GET["selecionarCaixa"])) {
    //$sql = "SELECT * FROM Caixa WHERE id_admin = ".$idAdmin." AND dataFechamento IS NULL";
    $sql = "SELECT * FROM Caixa WHERE dataFechamento IS NULL";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() > 1) {
        exit ("Erro: existe mais de um caixa aberto. Feche os caixas antes de abrir outro.");
    } else {
        if ($st->rowCount() == 0) {
            echo "Não há caixa aberto. Deseja abrir o caixa? Preencha os dados abaixo e prossiga.";
            echo '<p>O caixa será aberto para o dia '.date("d/m/Y").'</p>';
            ?>
            <form role="form" method="POST" action="index.php">
                <div class="form-group">
                    <label for="valor">Valor de abertura</label>
                    <input type="text" class="form-control dinheiro" id="valor" name="valor"/>
                </div>
                <button type="submit" id="abrirCaixa" name="abrirCaixa" class="btn btn-primary">Abrir Caixa</button>
            </form>
            <?php
            //exit();
        } else {
            $rs = $st->fetch();
            if (strtotime($rs["dataAbertura"]) != $todayStr) {
                echo '<p>Caixa aberto em '.date("d/m/Y H:i",strtotime($rs["dataAbertura"])).'<br>';
                echo '<a href="?selecionarCaixa='.$rs["id"].'">Prosseguir com caixa aberto</a></p>';
                //exit();
            }
        }
    }
}
//Prosseguir com caixa selecionado
if (isset($_GET["selecionarCaixa"])) {
    $idCaixa = $_GET["selecionarCaixa"];
    $formas = $oCaixa->getFormas();
}   
?>
	<?php if (isset($_GET["selecionarCaixa"])) { ?>
    	<div class="row">
    	    <div class="col-md-12 .table-responsive" id="container_transacoes">
                <?php
                echo '<b>Caixa aberto em '.date('d/m/Y H:i:s', strtotime($oCaixa->getCaixa($idCaixa)["dataAbertura"])).'</b>';
                echo $oCaixa->listaTransacoes($idCaixa);
                ?>
    	    </div>
    	</div>
    	
    	<div class="row">
    		<div class="col-md-12">
                <form role="form" method="POST" class="form-inline">
                    Lançar venda&nbsp;
                    <label class="sr-only" for="descricao">Descrição</label>
                    <input type="text" class="form-control mb-2 mr-sm-2" id="descricao" placeholder="Descrição">
                    
                    <label class="sr-only" for="valor">Valor</label>
                    <input type="text" class="form-control mb-2 mr-sm-2 dinheiro" id="valor" placeholder="Valor">
                    
                    <label class="sr-only my-1 mr-2" for="forma_pagamento">Forma Pagamento</label>
                    <select class="custom-select my-1 mr-sm-2" id="forma_pagamento">
                        <option value="">Forma de Pagamento</option>
                        <?php
                        if (isset($formas)) {
                            foreach ($formas as $id=>$forma) {
                                echo '<option value="'.$id.'">'.$forma.'</option>';
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" id="adicionarTransacao" name="adicionarTransacao" class="btn btn-primary">Adicionar</button>
                </form>
            </div>
    	</div>
    	
    	<div class="row">
    		<div class="col-md-12">
                <form role="form" method="POST" class="form-inline" action="?fecharCaixa=1">
                    <input type="hidden" id="id_fechar_caixa" name="id_fechar_caixa" value="<?php echo $_GET["selecionarCaixa"]; ?>" />
                    <label for="valor_fechar_caixa" class="my-1 mr-2">Saldo em dinheiro no caixa</label>
                    <input type="text" id="valor_fechar_caixa" name="valor_fechar_caixa" value="" class="form-control mb-2 mr-sm-2 dinheiro" />
                    <button type="submit" id="fecharCaixa" name="fecharCaixa" class="btn btn-primary">Fechar Caixa</button>
                </form>
            </div>
    	</div>
	<?php } ?>
	<div id="container_relatorio">
    	<?php
    	if (!isset($_GET["selecionarCaixa"])) {
        	if ($caixas = $oCaixa->listarCaixasFechados()) {
        	    echo "<h5>Relatório de Caixas Fechados</h5>";
            	foreach ($caixas as $row) {
            	    echo date("d/m/Y H:i",strtotime($row["dataAbertura"])).' - <a href="?verRelatorio=1&id_caixa_rel='.$row["id"].'">Ver Relatório</a><br>';
            	}
        	}
        	
        	if (isset($_GET["verRelatorio"]) && isset($_GET["id_caixa_rel"])) {
        	    echo $oCaixa->relatorioCaixa($_GET["id_caixa_rel"]);
        	}
    	} else {
    	    echo $oCaixa->relatorioCaixa($_GET["selecionarCaixa"]);
    	}
    	?>
	</div>
</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>


<script src="../js/scripts.js"></script>
<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>  
<script>
    $(document).ready(function() {
        $('.dinheiro').mask('#.##0,00', {reverse: true});

        function atualizaTransacoes() {
            idCaixa = $('#id_caixa').val();
            
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    "act": 'listaTransacoes',
                    "id_caixa": idCaixa,
                }
            })
            .done(function(msg) {
                //refresh transacoes
                $("#container_transacoes").html(msg);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            });
        }
        
        function limparFormTransacoes() {
            $("#descricao").val("");
            $("#valor").val("");
            $("#forma_pagamento").val("");
        }
        
        function atualizaRelatorio() {
            idCaixa = $('#id_caixa').val();
            
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    "act": 'relatorioCaixa',
                    "id_caixa": idCaixa,
                }
            })
            .done(function(msg) {
                //refresh transacoes
                $("#container_relatorio").html(msg);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            });
        }
        
        $("#fecharCaixa").on("click", function() {
            if ($("#valor_fechar_caixa").val() == "") {
                event.preventDefault();
                alert('Preencha o valor em dinheiro no caixa no momento do fechamento.');
            } else {
                if (!confirm("Deseja realmente fechar o caixa?")) {
                    event.preventDefault();
                }
            }
        });
        
        $(document).on("click", "[name='apagar_transacao']", function() {
            id = $(this).attr('id_transacao');
            conf1 = confirm('Deseja prosseguir a apagar a transacao ' + id + '?');
            if (!conf1) return;
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    "act": 'apagaTransacao',
                    "id_transacao": id,
                }
            })
            .done(function(msg) {
                //refresh transacoes
                atualizaTransacoes();
                atualizaRelatorio();
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            });
        });
        
        $("#adicionarTransacao").on("click", function() {
            event.preventDefault();
            idCaixa = $('#id_caixa').val();
            descricao = $("#descricao").val();
            valor = $("#valor").val();
            forma_pagamento = $("#forma_pagamento").val();
            
            if (idCaixa == "" || descricao == "" || valor == "" || forma_pagamento == "") {
                alert("Preencha todos os campos");
                return false;
            }
            
            $.ajax({
                method: "POST",
                url: "ajax.php",
                data: {
                    "act": 'cadastraTransacao',
                    "id_caixa": idCaixa,
                    "descricao": descricao,
                    "valor": valor,
                    "forma_pagamento": forma_pagamento
                }
            })
            .done(function(msg) {
                //refresh transacoes
                atualizaTransacoes();
                atualizaRelatorio();
                limparFormTransacoes();
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            });
        });
        
    });
    
</script>
</html>







































