<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Orgânico</title>
    <link rel="stylesheet" href="css/foundation.css">
    <link rel="stylesheet" href="css/app.css">
  </head>
  <body>
<?php
include "config.php";
//$conn = new PDO("mysql:host=localhost;dbname=id1608716_livres","id1608716_henderson","190788");
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$sql = "SELECT * FROM produtos WHERE mensal = 1 ORDER BY categoria,nome ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();

?>
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
	<?php
	function getArray($unidade) {
		if ($unidade == "kg") {
			return array(0.25,0.5,0.75,1,1.25,1.5,1.75,2);
		} elseif ($unidade = "unidade") {
			return array(1,2,3,4,5);
		}
	}
	?>
    <script src="js/vendor/jquery.js"></script>
    <script src="js/vendor/what-input.js"></script>
    <script src="js/vendor/foundation.js"></script>
    <script src="js/app.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script>
    $.validate({
        lang: 'pt',
        modules : 'brazil'
    });
    </script>
	<script>
	$(document).ready(function() {
		var getMulti = function(freq) {
			if (freq == "Semanal") {
				return 1;
			} else if (freq == "Quinzenal") {
				return 0.5;
			} else {
				return 1;
			}
		}
		var atualizaTotais = function(id) {
			//Alterar o subtotal
			preco = $("#prod_"+id).attr("preco");
			qtdade = $("#prod_"+id).val();
			freq = $("#freq_prod_"+id).val();
			if (freq == "") {
				$("#freq_prod_"+id).val("Mensal");
				freq="Mensal";
			}
			multi = getMulti(freq);
			preco=qtdade*preco*multi;
			preco=Math.round(preco*100)/100;
			$("#subtotal_prod_"+id).text("R$"+preco);
			//Alterar valor total da cesta
			total=0.0;
			$(".pedido").each(function(index) {
				id = $(this).attr("prod_id");
				preco = $("#prod_"+id).attr("preco");
				qtdade = $("#prod_"+id).val();
				freq = $("#freq_prod_"+id).val();
				if (freq=="") {
					multi=1;
				} else {
					multi=getMulti(freq);
				}
				total=total+qtdade*preco*multi;
				total=Math.round(total*100)/100;
			});
			$("#valorcesta").text("R$ "+total);		
		}
		$(".frequencia").change(function() {
			atualizaTotais($(this).attr("prod_id"));
		});
		$(".pedido").change(function() {
			atualizaTotais($(this).attr("prod_id"));
		});
	});
	</script>
  </body>
</html>
