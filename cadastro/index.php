<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Comboio Agroecológico</title>
    <link rel="stylesheet" href="../css/foundation.css">
    <link rel="stylesheet" href="../css/app.css">
  </head>
  <body>
<?php
include "../config.php";
//$conn = new PDO("mysql:host=localhost;dbname=id1608716_livres","id1608716_henderson","190788");
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);
$sql = "SELECT * FROM produtos WHERE carrinho = 1 ORDER BY categoria,nome ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();

?>
    <div class="grid-container">
      <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
          <h1>Livres - Comboio Agroecológico</h1>
        </div>
      </div>

      <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
          <div class="callout">
            <?php
            if ($_SERVER["HTTP_HOST"] == "livresbs.com.br") {
            ?>
                <h3>Parabéns pela escolha de fazer parte do Comboio Agroecológico do Livres!</h3>
                <p>Antes de continuar, pedimos que você leia atentamente as instruções abaixo, para garantirmos que você receba sua cesta do jeitinho que você quer.</p>
                <p>Escolha produtos tendo em vista um padrão de consumo de 6 meses. Esse compromisso permite que os produtores realizem uma política de benefícios de economia comunitária melhor que a do "livre mercado" para atendê-los. Você pode escolher sua cesta com produtos semanais e outros quinzenais, ou apenas semanais, ou apenas quizenais, eles farão um média de preço fixo!</p>
    
                <p>Para que possamos ter maior variedade na nossa alimentação, para estimular a pedagogia das trocas de alimentos devido sua sazonalidade, para salvar plantas em extinção e conhecer produtos locais que jamais conheceríamos, instituímos as cotas que serão preenchidas por uma seleção de alimentos semanalmente. Veja à direita as cotas que temos próximas ao seu perfil de consumo.</p>
    
                <p>Por isso tudo, preste atenção no preenchimento de seu carrinho comunitário.</p>
            <?php } else { ?>
                <h3>Parabéns pela escolha de fazer parte do Comboio Agroecológico do Livres!</h3>
                <p>Antes de continuar, pedimos que você leia atentamente as instruções abaixo, para garantirmos que você receba sua cesta do jeitinho que você quer.</p>
                <p>Escolha produtos tendo em vista um padrão de consumo de 3 meses. Esse compromisso permite que os produtores realizem uma política de benefícios de economia comunitária melhor que a do "livre mercado" para atendê-los. Você pode escolher sua cesta com produtos semanais!</p>
    
                <p>Para que possamos ter maior variedade na nossa alimentação, para estimular a pedagogia das trocas de alimentos devido sua sazonalidade, para salvar plantas em extinção e conhecer produtos locais que jamais conheceríamos, instituímos as cotas que serão preenchidas por uma seleção de alimentos semanalmente. Veja à direita as cotas que temos próximas ao seu perfil de consumo.</p>
    
                <p>Por isso tudo, preste atenção no preenchimento de seu carrinho comunitário.</p>
            <?php } ?>
            
          </div>
        </div>
      </div>

      <div class="grid-x grid-padding-x">
        <div class="large-8 medium-8 cell" id="cesta">
          <h5>Vamos agora amontar sua cesta.</h5>
          <form id="myForm" name="myForm" method="GET" action="salva.php">
			<div class="grid-x grid-padding-x">
				<div class="large-12 cell">
					<div class="callout">
						<p><b>Dados Pessoais</b>
						<br>Precisamos dos seus dados para identificar qual a sua cesta em cada entrega.</p>
						<!-- Nome  -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>Nome Completo</label>
							<input name="consumidor" type="text" placeholder="Preencha seu nome completo" data-validation="length" data-validation-length="3-255" />
						  </div>
						</div>
						<!-- Email  -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>E-mail</label>
							<input name="email" type="text" placeholder="Preencha seu e-mail" data-validation="length email" data-validation-length="10-255" />
						  </div>
						</div>
						<!-- CPF -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>CPF</label>
							<input name="cpf" id="cpf" type="text" placeholder="Preencha seu CPF" data-validation="cpf" />
						  </div>
						</div>
						<!-- Endereço  -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>Endereço</label>
							<input name="endereco" type="text" placeholder="Preencha seu endereço completo" data-validation="length" data-validation-length="3-255" />
						  </div>
						</div>
						<!-- Telefone  -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>Whatsapp</label>
							<input name="telefone" type="text" placeholder="Preencha seu Whatsapp com DDD" data-validation="length" data-validation-length="3-40" />
						  </div>
						</div>
						<!-- Entrega  -->
						<div class="grid-x grid-padding-x">
						  <div class="large-12 cell">
							<label>Dia de Entrega</label>
							<select id="preferencia_entrega" name="preferencia_entrega" data-validation="length" data-validation-length="2-200">
							    <option value="" selected="selected"></option>
							    <?php
                                    if ($_SERVER["HTTP_HOST"] == "livresbs.com.br") {
                                ?>
    							    <option value="Terça-Feira">Terça-Feira</option>
    							    <option value="Sábado">Sábado</option>
							    <?php } else { ?>
    							    <option value="Sábado">Sábado</option>
							    <?php } ?>
							</select>
						  </div>
						</div>
					</div>
				</div>
			</div>
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
						<label id="nome_prod_<?php echo $row["id"] ;?>"><?php echo $row["nome"]; ?></label>
						<label>R$<?php echo number_format($row["preco"]*$row["multiplicador_unidade2"],2)."/".$row["unidade2"]; ?></label>
					</div>
					<div class="large-3 medium-3 cell">
						<div class="grid-x">
							<div class="input-group">
								<select class="pedido input-group-field" prod_id="<?php echo $row["id"]; ?>" id="prod_<?php echo $row["id"]; ?>" name="prod_<?php echo $row["id"]; ?>" preco="<?php echo $row["preco"]*$row["multiplicador_unidade2"]; ?>">
									<option>0</option>
									<?php
									$arr=getArray($row["unidade2"]);
									foreach ($arr as $x) {
									?>
										<option><?php echo $x; ?></option>									
									<?php
									}
									?>
								</select>
								<span class="input-group-label"><?php echo $row["unidade2"]; ?></span>
							</div>
						</div>
					</div>
					<div class="large-3 medium-3 cell">
						<div class="grid-x">
							<div class="input-group">
								<select class="frequencia" prod_id="<?php echo $row["id"]; ?>" id="freq_prod_<?php echo $row["id"]; ?>" name="freq_prod_<?php echo $row["id"]; ?>" preco="<?php echo $row["preco"]*$row["multiplicador_unidade2"]; ?>">
									<option></option>
									<option>Semanal</option>
									<option>Quinzenal</option>
								    <?php
								    if ($row["mensal"] == 1) {
								        echo '<option>Mensal</option>';
								    }
								    ?>
								</select>
							</div>
						</div>
					</div>
					<div class="large-3 medium-3 cell">
						<label>Subtotal: </label><label idproduto="<?php echo $row["id"]; ?>" class="subtotal_produto" id="subtotal_prod_<?php echo $row["id"]; ?>">R$0,00</label>
					</div>
				</div>
				<?php
			} //fecha foreach dos produtos
			?>
			</div> <!-- fecha última DIV de categoria aberta pelo foreach de produtos -->
			<div class="large-12 cell">
				<input type="submit" class="button" value="Enviar">
			</div>
          </form>
        </div>

        <div class="large-4 medium-4 cell" data-sticky-container>
			<div class="sticky" data-sticky data-anchor="cesta">
			    <h2>Carrinho Comunitário</h2>
				<h5>Valor da sua cesta</h5>
				<p>Cesta Semanal:<h4 id="valorcestasemanal">R$ 0,00</h4></p>
				<p>Cesta Quinzenal:<h4 id="valorcestaquinzenal">R$ 0,00</h4></p>
				<p>Cesta Mensal:<h4 id="valorcestamensal">R$ 0,00</h4></p>
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
		/*if ($unidade == "kg") {
			return array(0.25,0.5,0.75,1,1.25,1.5,1.75,2);
		} elseif ($unidade == "unidade") {
			return array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
		} elseif ($unidade == "dúzia") {
			return array(1,2,3,4,5,6,7,8,9,10);
		}*/
		if ($unidade == "kg") {
			$from = 0.25;
			$to = 6;
			$step = 0.25;
		} elseif ($unidade == "unidade") {
			$from = 1;
			$to = 25;
			$step = 1;
		} elseif ($unidade == "dúzia") {
			$from = 0.5;
			$to = 10;
			$step = 0.5;
		} else {
		    $from = 1;
			$to = 25;
			$step = 1;
		}
		for ($i = $from; $i <= $to; $i+=$step) {
		    $arr[] = $i;
		}
		return $arr;
	}
	?>
    <script src="../js/vendor/jquery.js"></script>
    <script src="../js/vendor/what-input.js"></script>
    <script src="../js/vendor/foundation.js"></script>
    <script src="../js/app.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
    <script>
    $.validate({
        lang: 'pt',
        modules : 'brazil',
    });
    /*
    $('#myform').validate(
        rules: {
            preferencia_entrega: { required: true }
        }
    }
    */
    </script>
	<script>
	$(document).ready(function() {
	    var currformat = function(preco) {
	        preco = preco.toFixed(2);
	        preco = preco.replace('.',',');
	        return "R$"+preco;
	    }
	    var atualizaSubtotais = function(id) {
	        freq = $('#freq_prod_'+id).val();
	        preco = $('#freq_prod_'+id).attr('preco');
	        quantidade = $('#prod_'+id).val();
	        if (freq == "" || quantidade == "") {
	            $('#subtotal_prod_'+id).text("R$0,00");
	        } else {
	            $('#subtotal_prod_'+id).text(currformat(preco*quantidade));
	        }
	    }
		var atualizaTotais = function() {
		    precosemanal = 0;
	        precoquinzenal = 0;
	        precomensal = 0;
		    $(".subtotal_produto").each(function(index) {
		        id = $(this).attr('idproduto');
		        freq = $('#freq_prod_'+id).val();
		        preco = $('#freq_prod_'+id).attr('preco');
		        quantidade = $('#prod_'+id).val();
		        
		        if (freq == 'Semanal') {
		            precosemanal = precosemanal + (preco*quantidade);
		            precoquinzenal = precoquinzenal + (preco*quantidade);
		        } else {
		            if (freq == 'Quinzenal') {
		                precoquinzenal = precoquinzenal + (preco*quantidade);
		            } else {
		                if (freq == 'Mensal') {
		                    precomensal = precomensal + (preco*quantidade);
		                }
		            }
		        }
            });
            $("#valorcestasemanal").text(currformat(precosemanal));
            $("#valorcestaquinzenal").text(currformat(precoquinzenal));
            $("#valorcestamensal").text(currformat(precomensal));
		}
		$(".frequencia").change(function() {
			atualizaTotais();
			atualizaSubtotais($(this).attr("prod_id"));
		});
		$(".pedido").change(function() {
			atualizaTotais();
			atualizaSubtotais($(this).attr("prod_id"));
		});
		$("#cpf").change(function() {
			$.ajax({
				type: "POST",
				url: 'check.php',
				data: "cpf="+$(this).val(),
				success: function(data) {
					if (data==1) {
						alert("Seu CPF já está cadastrado. \r\nConsulte seu pedido em "+window.location.hostname+"/Cestas\r\nEntre em contato com os coordenadores do Livres caso queira alterá-lo.");
					}
				}
			}).done(function(data) {
				return data;
			});
		});
		$("#myForm").submit(function() {
		    //alert('erro. atualize a página');
		    var conta=0;
		    var validado=true;
		    $("select.pedido").each(function(index) {
		        prodid=$(this).attr('prod_id');
		        qt=$(this).val();
		        freq=$("#freq_prod_"+prodid).val();
		        nome=$("#nome_prod_"+prodid).text();
		        //console.log(prodid+" - "+qt+" | "+freq);
		        if (qt>0 && freq == "") {
		            alert("Você não preencheu a frequência para o produto '" + nome + "'. Preencha frequência ou altere a quantidade para zero.");
		            $("#freq_prod_"+prodid).focus();
		            validado=false;
		            return false;
		        }
		        if (qt>0) {
		            conta=conta+1;
		        }
            });
			/*
			//checar cpf
			var form = $(this);
			var cpf = $.ajax({
				type: "POST",
				url: 'check.php',
				data: form.serialize(), // serializes the form's elements.
				success: function(data) {
					cpf = data;
					if (data == 0) {
						//alert("Seu CPF já está cadastrado. \r\nConsulte seu pedido em "+window.location.hostname+"/Cestas\r\nEntre em contato com os coordenadores do Livres caso queira alterá-lo.");
						validado=false;
						return false;
					} else {
						return true;
					}
				}
			}).done(function(data) {
				return data;
			});
			console.log(cpf.responseText);
			if (cpf == 0) {
				alert("Seu CPF já está cadastrado. \r\nConsulte seu pedido em "+window.location.hostname+"/Cestas\r\nEntre em contato com os coordenadores do Livres caso queira alterá-lo.");
				validado=false;
				return false;
			}
			*/
            if (validado) {
                if (conta == 0) {
                    alert("Seu pedido não tem nenhum produto. Preencha antes de continuar");
                    return false;
                }
            } else {
                return false;
            }
		});
	});
	</script>
  </body>
</html>
