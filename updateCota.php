<?php

$id=46; //46=augusto. cota 30 precisa mudar pra 35
$dataCota = "2019-06-07";

include "config.php";
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=livresbs_demo",$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$sqlConsumidor = "SELECT * FROM Consumidores WHERE id = ".$id;
$st = $conn->prepare($sqlConsumidor);
$st->execute();
if ($st->rowCount()>0) {
    $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id WHERE produtos.previsao <= '".$dataCota."' Pedidos.IDConsumidor = ".$id;
    $st = $conn->prepare($sqlConsumidor);
    $st->execute();
    if ($st->rowCount()>0) {
        $rs = $st->fetchAll();
        $pedido=0;
        $contaSemanal=0;
        foreach ($rs as $row) {
            if($row["Frequencia"]=="Semanal") {
                $contaSemanal++;
            }
            $subtotal = $row["Quantidade"]*$row["preco"];
            if ($row["Frequencia"] == "Quinzenal") {
                $pedido += $subtotal/2;
            } else {
                if ($row["Frequencia"] == "Semanal") {
                    $pedido += $subtotal;
                }
            }
        }
        if ($contaSemanal ==0) {
            $pedido=$pedido*2;
        }
        //Calcular cota
        $proximaCota=floor($pedido/5)*5+5;
        $variavel = $proximaCota-$pedido;
        if ($variavel < 4) {
            $cota = $proximaCota+5;
        } else {
            $cota=$proximaCota;
        }
        $sqlUpdate="UPDATE Consumidores SET cota_calc = ".$proximaCota." WHERE id = ".$id;
        $st = $conn->prepare($sqlUpdate);
        $st->execute();
    }
}
?>

<?php
/**
<div class="grid-container">
<div class="grid-x grid-padding-x">
<div class="large-12 cell">
<h1>Livres - Comboio Orgânico</h1>
</div>
</div>

<div class="grid-x grid-padding-x">
<div class="large-8 medium-8 cell" id="cesta">
<h5>Vamos agora amontar sua cesta.</h5>
<?php
if (isset($_GET["cpf"])) {
//Verificar se CPF está cadastrado, para facilitar mais para frente
$cpf = $_GET["cpf"];
$cpf=str_replace(".","",$cpf);
$cpf=str_replace(",","",$cpf);
$cpf=str_replace(" ","",$cpf);
$cpf=str_replace("-","",$cpf);
$sqlCpf = "SELECT * FROM Consumidores WHERE cpf = '".$cpf."'";
$stCpf = $conn->prepare($sqlCpf);
$stCpf->execute();
if ($stCpf->rowCount()==1) {
$cpfExiste = 1;
} else {
$cpfExiste = 0;
if (strlen($cpf)>0) {
$msg = "CPF não encontrado";
}
}
}
if (!isset($_GET["cpf"]) || $_GET["cpf"] == "" || !$cpfExiste) {
?>
<form method="GET" action="">
<div class="grid-x grid-padding-x">
<div class="large-12 cell">
<div class="callout">
<p><b>Dados Pessoais</b>
<?php
if (strlen($msg)>0) {
echo '<br><b>'.$msg.'</b>';
} else {
echo '<br>Precisamos dos seus dados para identificar qual a sua cesta em cada entrega.';
}
?>
</p>
<!-- CPF -->
<div class="grid-x grid-padding-x">
<div class="large-12 cell">
<label>CPF</label>
<input name="cpf" type="text" placeholder="Preencha seu CPF" data-validation="cpf" />
</div>
<div class="large-12 cell">
<input type="submit" class="button" value="Enviar">
</div>
</div>
</div>
</div>
</div>
</form>
<?php
} else {
?>
<form method="GET" action="salvaMensal.php">
<input type="hidden" name="cpf" id="cpf" value="<?php echo $cpf; ?>"/>
<!-- Produtos -->
<?php
$cat="";
$counter=0;
foreach ($rs as $row) {
if ($row["categoria"] != $cat) {
$counter=1;
if ($cat != "") {
?>
</div>
<?php
}
$cat=$row["categoria"];
?>
<div class="callout">
<p><b><?php echo $cat; ?></b></p>
<?php
} else {
$counter++;
}
/*
echo "<tr>";
echo "<td>".$row['nome']."</td>";
echo "<td>".$row['preco']."</td>";
echo "<td>".createoption($row['unidade'],$row["preco"],$row["id"])." ". $row["unidade"] ."</td>";
echo "</tr>";
*/
/**
?>
<?php
if ($counter > 1) {
echo "<hr>";
}
?>
<div class="grid-x grid-padding-x">
<div class="large-3 medium-3 cell">
<label><?php echo $row["nome"]; ?></label>
<label>R$<?php echo number_format($row["preco"],2)."/".$row["unidade"]; ?></label>
</div>
<div class="large-3 medium-3 cell">
<div class="grid-x">
<div class="input-group">
<select class="pedido input-group-field" prod_id="<?php echo $row["id"]; ?>" id="prod_<?php echo $row["id"]; ?>" name="prod_<?php echo $row["id"]; ?>" preco="<?php echo $row["preco"]; ?>">
<option>0</option>
<?php
$arr=getArray($row["unidade"]);
foreach ($arr as $x) {
?>
<option><?php echo $x; ?></option>									
<?php
}
?>
</select>
<span class="input-group-label"><?php echo $row["unidade"]; ?></span>
</div>
</div>
</div>
<div class="large-3 medium-3 cell">
<div class="grid-x">
<div class="input-group">
<select class="frequencia" prod_id="<?php echo $row["id"]; ?>" id="freq_prod_<?php echo $row["id"]; ?>" name="freq_prod_<?php echo $row["id"]; ?>">
<option></option>
<option selected="selected">Mensal</option>
</select>
</div>
</div>
</div>
<div class="large-3 medium-3 cell">
<label>Subtotal: </label><label id="subtotal_prod_<?php echo $row["id"]; ?>">R$0,00</label>
</div>
</div>
<?php
} //fecha foreach dos produtos
?>
</div> <!-- fecha última DIV de categoria aberta pelo foreach de produtos -->
<div class="large-12 cell">
<input type="submit" class="button" value="Enviar">
</div>
<?php
} //fecha o IF que identifica preenchimento do CPF
?>
</form>
</div>

<div class="large-4 medium-4 cell" data-sticky-container>
<div class="sticky" data-sticky data-anchor="cesta">
<h2>Carrinho Comunitário</h2>
<h5>Valor da sua cesta Mensal:</h5>
(Note que este é o preço da sua cesta MENSAL. Quando ela for entregue, será acrescido neste valor o montante usual de sua cota.)
<h4 id="valorcesta">R$ 0,00</h4>
<p></p>
<!--<p>
Cotas disponíveis:
R$20, R$25, R$30, R$35, R$45, R$50, R$70 e R$90
</p>
-->
</div>
</div>
</div>
</div>
?>