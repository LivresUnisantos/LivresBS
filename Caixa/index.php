<?php
include "../config.php";
include "../Painel/helpers.php";
include "../Painel/acesso.php";
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link href="../css/style.css" rel="stylesheet">
    </head>
<body>
<?php
//Variáveis de controle
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
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
    if (strlen($_POST["valor"]) == 0 || strlen($_POST["data"]) == 0)  {
        echo "<p>Preencha valor e data de abertura</p>";
    } else {
        if (!isDate($_POST["data"])) {
            echo "<p>A data preenchida não é válida</p>";
        } else {
            //Salvar abertura de caixa
            $dataAbertura = $_POST["data"];
            $horaAbertura = date("Y-m-d H:i:s");
            $valorAbertura = $_POST["valor"];
            $valorAbertura = str_replace(",",".",$valorAbertura);
            $sql = "INSERT INTO Caixa (idAdmin, dataAbertura, horaAbertura, valorAbertura) VALUES (".$idAdmin.",'".$dataAbertura."','".$horaAbertura."',".$valorAbertura.")";
            $st = $conn->prepare($sql);
            if ($st->execute()) {
                echo "<p>Caixa aberto</p>";
                //Tratar das transações automáticas
                $sql = "SELECT * FROM Calendario WHERE data = ".$dataAbertura;
                $st = $conn->prepare($sql);
                $st->execute();
                if ($_POST["autogerar"]) {
                    //Gerar transações automáticas caso seja dia de entrega
                    $sql = "SELECT * FROM Calendario WHERE data = ".$dataAbertura;
                    $st = $conn->prepare($sql);
                    $st->execute();
                    if ($st->rowCount() == 0) {
                        echo "<p>Não foram geradas transações automáticas porque o dia ".date("d/m/Y",strtotime($dataAbertura))." não é dia de entrega</p>";
                    } else {
                        //Buscar todos os dados de produtos e consumidores para gerar transações.
                        /*
                        PENDENTE
                        echo "Transações automáticas criadas"
                        */
                    }
                } else {
                    if ($st->rowCount() > 0) {
                        echo "<p>Atente para o fato de que você não realizou a geração de transações automáticas para esse dia, mesmo ele possuindo entregas.</p>";
                    }
                }
            } else {
                echo "<p>Erro ao abrir o caixa.</p>";
            }
        }
    }
}
//Selecionar caixa
if (isset($_GET["selecionarCaixa"])) {
    $_SESSION["idCaixa"] = 0;
    $idCaixa = $_GET["selecionarCaixa"];
    $sql = "SELECT * FROM Caixa WHERE id = ".$idCaixa;
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() == 0) {
        echo "<p>Caixa não encontrado</p>";
    } else {
        $rs = $st->fetch();
        if (is_null($rs["dataFechamento"])) {
            $_SESSION["idCaixa"] = $idCaixa;
        } else {
            echo "<p>O caixa selecionado já está fechado;</p>";
        }
    }
}
//Buscar se existe caixa aberto, caso nenhum caixa esteja selecionado
if (!isset($_SESSION["idCaixa"]) || $_SESSION["idCaixa"] == 0) {
    $sql = "SELECT * FROM Caixa WHERE idAdmin = ".$idAdmin." AND dataFechamento IS NULL";
    $st = $conn->prepare($sql);
    $st->execute();
    if ($st->rowCount() > 1) {
        exit ("Erro: existe mais de um caixa aberto para seu usuário. Consultar administrador");
    } else {
        if ($st->rowCount() == 0) {
            echo "Não há caixa aberto. Deseja abrir o caixa? Preencha os dados abaixo e prossiga.";
            ?>
            <form role="form" method="POST" action="">
                <div class="form-group">
                    <label for="data">Data de abertura</label>
                    <input type="date" class="form-control" id="data" name="data" value="'.$todayStr.'" />
                </div>
                <div class="form-group">
                    <label for="valor">Valor de abertura</label>
                    <input type="text" class="form-control dinheiro" id="valor" name="valor"/>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="autogerar" name="autogerar" /> Gerar transações automáticas referente entregas/produtores do dia?
                    </label>
                </div> 
                <button type="submit" id="abrirCaixa" name="abrirCaixa" class="btn btn-primary">Abrir Caixa</button>
            </form>
            <?php
            exit();
        } else {
            $rs = $st->fetch();
            if (strtotime($rs["dataAbertura"]) != $todayStr) {
                echo '<p>Caixa aberto para o dia '.date("d/m/Y",strtotime($rs["dataAbertura"])).'. Deseja:</p>';
                echo '<a href="">Fechar Caixa</a> | <a href="?selecionarCaixa='.$rs["id"].'">Prosseguir com caixa do dia '.date("d/m/Y",strtotime($rs["dataAbertura"])).'</a>';
                exit();
            }
        }
    }
}
//Prosseguir com caixa selecionado
$idCaixa = $_SESSION["idCaixa"];
    //Obter transações realizadas
$sql = "SELECT * FROM TransacoesRealizadas WHERE idCaixa = ".$idCaixa;
$st = $conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0)  {
    $rs = $st->fetchAll();
    foreach ($rs as $row) {
        $tRealizadas[$row["id"]]["idPrevista"] = $row["idPrevista"];
        $tRealizadas[$row["id"]]["data"] = $row["data"];
        $tRealizadas[$row["id"]]["tempo"] = $row["tempo"];
        $tRealizadas[$row["id"]]["tipo"] = $row["tipo"];
        $tRealizadas[$row["id"]]["descricao"] = $row["descricao"];
        $tRealizadas[$row["id"]]["observacao"] = $row["observacao"];
        $tRealizadas[$row["id"]]["valor"] = $row["valor"];
    }
}
    //Obter transações previstas
$sql = "SELECT * FROM TransacoesPrevistas WHERE idCaixa = ".$idCaixa;
$st = $conn->prepare($sql);
$st->execute();
if ($st->rowCount() > 0)  {
    $rs = $st->fetchAll();
    foreach ($rs as $row) {
        $tRealizadas[$row["id"]]["idRealizada"] = $row["idRealizada"];
        $tPrevistas[$row["id"]]["data"] = $row["data"];
        $tPrevistas[$row["id"]]["tempo"] = $row["tempo"];
        $tPrevistas[$row["id"]]["tipo"] = $row["tipo"];
        $tPrevistas[$row["id"]]["descricao"] = $row["descricao"];
        $tPrevistas[$row["id"]]["valor"] = $row["valor"];
    }
}
?>
<div class="container-fluid">
	<div class="row">
	    <!-- MENU SUPERIOR -->
		<div class="col-md-12">
			<ul class="nav">
				<li class="nav-item">
					<a class="nav-link active" href="#">Home</a>
				</li>
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
			</ul>
			<!-- FIM MENU SUPERIOR -->
		</div>
	</div>
	<div class="row">
	    <div class="col-md-12 .table-responsive">
	        <table class="table table-hover table-bordered table-striped table-sm">
            	<thead>
            		<tr>
            			<th>#</th>
            			<th>Tipo</th>
            			<th>Descrição</th>
            			<th>Observação</th>
            			<th>Valor</th>
            			<th></th>
            		</tr>
            	</thead>
            	<tbody>
            	    <?php
                	foreach ($tRealizadas as $id=>$dados) {
                    ?>
            		<tr>
            		    <td><?php echo $id; ?></td>
            			<td><?php echo $dados["tipo"]; ?></td>
            			<td><?php echo $dados["descricao"]; ?></td>
            			<td><?php echo $dados["observacao"]; ?></td>
            			<td>R$<?php echo number_format($dados["valor"],2,",","."); ?></td>
            			<td><button type="button" class="btn btn-danger">Cancelar</button></td>
            		</tr>
            		<?php
                	}
                    ?>
            	</tbody>
            </table>
	    </div>
	</div>
	<div class="row">
		<div class="col-md-12">
		    <form role="form" method="POST">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="inputGroupSelect01">Venda Prevista</label>
                    </div>
                    <select class="custom-select" id="inputGroupSelect01">
                        <option selected>Escolher...</option>
                        <option value="1">Venda 1</option>
                        <option value="2">Venda 2</option>
                        <option value="3">Venda 3</option>
                    </select>
                    <button type="submit" id="VendaPrevistaAdd" name="adicionarTransacao" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
	</div>
	<!--
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				Vendas Previstas
				<div class="col-md-12">
					caixas
				</div>
			</div>
			<div class="row">
				Compras Previstas
				<div class="col-md-12">
					caixas
				</div>
			</div>
			<div class="row">
				Vendas Avulsas
				<div class="col-md-12">
					Caixas
				</div>
			</div>
			<div class="row">
				Compras Avulsas
				<div class="col-md-12">
					Caixas
				</div>
			</div>
		</div>
	</div>
	-->
</div>
</body>
<script src="../js/vendor/jquery.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/scripts.js"></script>
<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>  
<script>
            $(document).ready(function() {
                $('.dinheiro').mask('#.##0,00', {reverse: true});
            });
        </script>
</html>