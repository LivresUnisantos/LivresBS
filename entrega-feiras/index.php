<!doctype html>
<html class="no-js" lang="en" dir="ltr">
  <head><meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres - Cadastro Consumidor</title>
    <link rel="stylesheet" href="../css/foundation.css">
    <link rel="stylesheet" href="../css/app.css">
    <style>
        body {
            background: #29abe2;
            font-size: 18px;
        }
        h1 {
            color: #ffffff;
        }
        .bg {
            background: #29abe2;
        }
        label, b {
            color: #ffffff;
            font-size: 18px;
        }
        #wrapper {
            background-image: url("logo_livres.png");
            background-repeat: no-repeat;
            background-size: 250px 436px;
            background-position: right bottom;
            -webkit-background-size: 250px 436px;
            -moz-background-size: 250px 436px;
            -o-background-size: 250px 436px;
            width: 100% !important;
            z-index: 0;
        }
        .callout {
            border-color: #ffffff;
        }
        /*
        #simbol1 {
            position: fixed;
            bottom: 0px;
            right: 0px;
            margin-right: 100px;
            margin-bottom: 100px;
            width: 250px;
            height: 250px;
            background-image: url("logo_livres.png");
            background-repeat: no-repeat;
            background-size: cover;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            width: 100% !important;
            z-index: 1;
        }
        h1 {
            background-image: url("logo_livres.png");
            background-repeat: no-repeat;
            background-size: cover;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            width: 100% !important;
        }
        */
    </style>
  </head>
  <body>
<?php
include "../config.php";
//$conn = new PDO("mysql:host=localhost;dbname=id1608716_livres","id1608716_henderson","190788");
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);//
$sql = "SELECT * FROM produtos ORDER BY categoria,nome ASC";//
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();

?>
    <div id="wrapper">
    <div class="grid-container">
      <div class="grid-x grid-padding-x">
        <div class="large-12 cell">
          <h1>Livres - Cadastro Consumidor</h1>
        </div>
      </div>

      

      <div class="grid-x grid-padding-x">
        <div class="large-8 medium-8 cell" id="cesta">
          <form id="myForm" name="myForm" method="GET" action="salva.php">
			<div class="grid-x grid-padding-x">
				<div class="large-12 cell">
					<div class="bg callout">
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
							<input name="cpf" type="text" placeholder="Preencha seu CPF" data-validation="cpf" />
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
							<label>Telefone/Whatsapp</label>
							<input name="telefone" type="text" placeholder="Preencha seu telefone com DDD" data-validation="length" data-validation-length="3-40" />
						  </div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="large-12 cell">
				<input type="submit" class="button" value="Enviar">
			</div>
          </form>
        </div>
      </div>
    </div>
    </div>
    <div id="simbolo"></div>
	<?php
	function getArray($unidade) {
		if ($unidade == "kg") {
			return array(0.25,0.5,0.75,1,1.25,1.5,1.75,2);
		} elseif ($unidade = "unidade") {
			return array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);//
		}
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
		var getMulti = function(freq) {
			if (freq == "Semanal") {
				return 1;
			} else if (freq == "Quinzenal") {
				return 0.5;
			} else {
				return 0.25;
			}
		}
		var atualizaTotais = function(id) {
			//Alterar o subtotal
			preco = $("#prod_"+id).attr("preco");
			qtdade = $("#prod_"+id).val();
			freq = $("#freq_prod_"+id).val();
			if (freq == "") {
				$("#freq_prod_"+id).val("Semanal");
				freq="Semanal";
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
