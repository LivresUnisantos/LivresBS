<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';

$livres = new Livres();
$calendario = new Calendario();
$loader = new \Twig\Loader\FilesystemLoader('../templates/layouts/painel');
$twig = new \Twig\Environment($loader, ['debug' => false]);
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link rel="stylesheet" href="painel.css">
    <style>
        .sacolas {
            cursor: pointer;
        }
        .editar {
            cursor: pointer;
        }
        .credito {
            color:#34ad5c;
        }
        .debito {
            color:#ff0000;
        }
    </style>
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <script src="../js/vendor/jquery.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        function ativar(destino,nivel, consumidor) {
            if (nivel < 10000) {
                alert('Você não possui permissão para executar essa ação');
            } else {
                var r = confirm("Deseja realmente ativar o consumidor '" + consumidor + "'?");
                if (r == true) {
                    window.location.href= destino;
                }
            }
        }
        function desativar(destino,nivel, consumidor) {
            if (nivel < 10000) {
                alert('Você não possui permissão para executar essa ação');
            } else {
                var r = confirm("Deseja realmente desativar o consumidor '"+consumidor+"'?");
                if (r == true) {
                    window.location.href= destino;
                }
            }
        }
    </script>
    <script>
	$(document).ready(function() {
	    var dialog;
	    dialog = $("#creditos").dialog({
            autoOpen: false,
            position: { my: "center top", at: "center top", of: window },
            width: 650,
            height: 600
        });
        /* funções */
        //atualizar lista de créditos/débitos
        var atualizaListaFiado;
        function atualizaListaFiado(idConsumidor) {
            $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    act: "lista",
                }
            })
            .done(function(html) {
                $('#listaFiado').html(html);
            });
        }
        //atualizar saldos do consumidor na tela
        var atualizaSaldos;
        function atualizaSaldos(idConsumidor) {
                //ajusta texto do saldo
            $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    act: "saldo",
                }
            })
            .done(function(html) {
                $('#fiadoSaldoPopup').html(html);
                $('span[campo="saldo"][idConsumidor="'+idConsumidor+'"]').html(html);
            });
                //ajusta classe do saldo
            $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    act: "sinalSaldo",
                }
            })
            .done(function(html) {
                $("#fiadoSaldoPopup").removeClass("debito");
                $("#fiadoSaldoPopup").removeClass("credito");
                $('span[campo="saldo"][idConsumidor="'+idConsumidor+'"]').removeClass("debito");
                $('span[campo="saldo"][idConsumidor="'+idConsumidor+'"]').removeClass("credito");
                if (html >= 0) {
                    $("#fiadoSaldoPopup").addClass("credito");
                    $('span[campo="saldo"][idConsumidor="'+idConsumidor+'"]').addClass("credito");
                }
                if (html < 0) {
                    $("#fiadoSaldoPopup").addClass("debito");
                    $('span[campo="saldo"][idConsumidor="'+idConsumidor+'"]').addClass("debito");
                    
                }
            });
        }
        /*fim funções*/
        
        //Saldo de sacolas
		$('.sacolas').click(function() {
		    idConsumidor = $(this).attr('idConsumidor');
		    inc = $(this).attr("inc");
		    $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    act: "sacolas",
                    inc: inc
                }
            })
            .done(function(msg) {
                $('#saldoSacola'+idConsumidor).text(msg);
                //alert(msg);
            });
        });
        
        //Abrir popup
        $('.editar').on("click",function() {
            idConsumidor = $(this).attr('idConsumidor');
            atualizaListaFiado(idConsumidor);
            //obter dados do cliente
            $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    act: "consumidor",
                }
            })
            .done(function(msg) {
                $("#consumidorFiado").html(msg);
            });
            atualizaSaldos(idConsumidor);
            dialog.dialog("open");
            $("#idFiado").val(idConsumidor);
        });
        
        //Salva formulário de fiado
        $("#consFiado").submit(function(event){
        	event.preventDefault();
        	var post_url = $(this).attr("action"); //get form action url
        	var request_method = $(this).attr("method"); //get form GET/POST method
        	var form_data = $(this).serialize(); //Encode form elements for submission
        	$.ajax({
        		method: "POST",
                url: "consumidores_act.php",
        		data : form_data
        	}).done(function(html){ //
        	    alert(html);
        	    //atualiza lista de operações
        	    atualizaListaFiado($("#idFiado").val());
        	    //atualiza dados do consumidor
        	    atualizaSaldos(idConsumidor);
        	    //atualiza dados da página "parent"
        	    //limpa formulário se não for erro
        	    if (html.substring(0,5).toLowerCase() != "falha") {
        	        $("#consFiado")[0].reset();
        	    }
        	});
        });
        
        //Mudar consumidor de grupo
        $("select[mudagrupo]").change(function(event){
            idConsumidor = $(this).attr('idConsumidor');
            vComunidade = $(this).val();
            $.ajax({
                method: "POST",
                url: "consumidores_act.php",
                data: {
                    id: idConsumidor,
                    comunidade: vComunidade,
                    act: "atualizaGrupo",
                }
            })
            .done(function(msg) {
                alert(msg);
                location.reload();
            });
        });
        
        //marcar/desmarcar consumidor como cliente do banco
        $("input[name='checkbox_banco']").on('click', function() {
            idConsumidor = $(this).attr('consumidor_id');
            if ($(this).is(':checked')) {
                banco = 1;
            } else {
                banco = 0;
            }
            //console.log(idConsumidor);
            //console.log(banco);
            
            $.ajax({
        		method: "POST",
                url: "consumidores_act.php",
        		data : {
        		    act: 'banco',
        		    id: idConsumidor,
        		    banco: banco
        		}
        	}).done(function(html){ //
        	    alert(html);
        	    if (html != 'Alteração realizada.') {
        	        location.reload();
        	    }
        	});
        });
	});
	</script>
    </head>
    <body>
    <?php
    echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);
?>
<div id="creditos" title="Créditos e Débitos">
    <div id="consumidorFiado"></div>
    <form id="consFiado" name="consFiado" method="" action"consumidores_act.php">
        <select id="operacaoFiado" name="operacaoFiado">
            <option value="">Escolha</option>
            <option value="credito">Crédito</option>
            <option value="debito">Débito</option>
        </select>
        <input type="text" name="valorFiado" id="valorFiado" placeholder="Valor" />
        <input type="text" name="obsFiado" id="obsFiado" placeholder="Observação" />
        <input type="hidden" name="id" id="idFiado" value="" />
        <input type="hidden" name="act" id="act" value="fiado" />
        <input type="submit" name="subFiado" id="subFiado" value="Salvar" />
    </form>
    <div id="listaFiado"></div>
</div>
<?php
$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
if (isset($_GET["cpf"])) {
    $cpf = $_GET["cpf"];
    $ativo = $_GET["ativo"];
    if (!is_numeric($cpf) || !is_numeric($ativo)) {
        echo "<p>Dados preenchidos incorretamente</p>";
    } else {
        $sql = "UPDATE Consumidores SET ativo = ".$ativo." WHERE cpf = ".$cpf;
        $st = $conn->prepare($sql);
        if ($st->execute()) {
            setLog("log.txt","Atualização Consumidor.",$sql);
            if ($ativo == 1) {
                echo "<p>Consumidor ativado</p>";
            } else {
                echo "<p>Consumidor desativado</p>";
            }
        } else {
            if ($ativo == 1) {
                echo "<p>Erro ao ativar consumidor</p>";
            } else {
                echo "<p>Erro ao desativar consumidor</p>";
            }
        }
    }
}
//Obter total de grupos
$sql = "SELECT * FROM Parametros WHERE parametro = 'grupos'";
$st = $conn->prepare($sql);
$st->execute();
$rs = $st->fetch();
$nGrupos = intval ($rs["valor"]);
for ($i = 1; $i <= $nGrupos; $i++) {
    $sql = "SELECT * FROM Consumidores WHERE ativo = 1 AND comunidade = ". $i ." ORDER BY consumidor ASC";
    $st = $conn->prepare($sql);
    $st->execute();
    $rs=$st->fetchAll();
    echo '<p>Consumidores Ativos - Grupo '.$i.'</p>';
    echo '<table id="tblCons'.$i.'" border="1px" width="950">';
    echo '<tr>';
    echo '<td width="350">Consumidor</td>';
    echo '<td width="100">Nascimento</td>';
    echo '<td width="100">Sacolas no Livres</td>';
    echo '<td width="150">Ver Cesta</td>';
    echo '<td width="150">Editar Cesta</td>';
    echo '<td width="100">Ativar/Desativar</td>';
    echo '<td>Banco</td>';
    echo '</tr>';
    foreach ($rs as $row) {
        $classeSaldo = "";
        $credito = round($row["credito"],2);
        if ($credito >= 0) {
            $classeSaldo = "credito";
        } else {
            if ($credito < 0) {
                $classeSaldo = "debito";
            }
        }
        echo '<td>'.ucwords(mb_strtolower($row["consumidor"],'UTF-8')).' ('.$row["id"].')</td>';
        //echo '<td style="text-align:right;"><span campo="saldo" class="'.$classeSaldo.'" idConsumidor="'.$row["id"].'">'.formatSaldo($credito).'</span> <img src="../images/edit.png" class="editar" width="16" height="16" alt="Editar" idConsumidor="'.$row["id"].'" /></td>';
        if (strlen($row["nascimento"]) > 0) {
            echo '<td>'.date('d/m/Y',strtotime($row["nascimento"])).'</td>';
        } else {
            echo '<td></td>';
        }
        echo '<td style="text-align:center;">';
        echo '<span class="sacolas" idConsumidor="'.$row["id"].'" inc="-1"><img src="../images/less.png" width="16" height="16" /></span>';
        echo '<span style="margin-left:15px;margin-right:15px;" id="saldoSacola'.$row["id"].'">'.$row["sacolas"].'</span>';
        echo '<span class="sacolas" idConsumidor="'.$row["id"].'" inc="+1"><img src="../images/plus.png" width="16" height="16" /></a>';
        echo '</td>';
        echo '<td><a href="../Cestas/?cpf='.$row["cpf"].'" target="_blank">Ver Cesta</a></td>';
        echo '<td><a href="editar_cesta.php?cpf='.$row["cpf"].'" target="_blank">Editar Cesta</a></td>';
        echo '<td><a href="javascript:desativar(\'?cpf='.$row["cpf"].'&ativo=0\','.$_SESSION["level"].',\''.$row["consumidor"].'\')">Desativar</a></td>';
        echo '<td><input type="checkbox" id="banco_'.$row["id"].'" name="checkbox_banco" consumidor_id="'.$row["id"].'" '.(($row["banco"] == 1) ? 'checked="checked"' : "").' /></td>';
        echo '</tr>';
    }
    echo '</table>';
}
/*
$sql = "SELECT * FROM Consumidores WHERE ativo = 0 or comunidade = 0 ORDER BY consumidor ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
echo '<p>Consumidores Inativos e sem grupo</p>';
foreach ($rs as $row) {
    if (!isset($consumidores) || !array_key_exists($row["consumidor"],$consumidores)) {
        $consumidores[$row["consumidor"]]["contador"] = 0;
    }
    if (!isset($cpfs) || !array_key_exists($row["cpf"],$cpfs)) {
        $cpfs[$row["cpf"]] = 0;
    }
    $cpfs[$row["cpf"]]++;
    $consumidores[$row["consumidor"]]["contador"]++;
    $consumidores[$row["consumidor"]]["cpf"] = $row["cpf"];
    $consumidores[$row["consumidor"]]["data_criacao"] = $row["data_criacao"];
    $consumidores[$row["consumidor"]]["preferencia_entrega"] = $row["preferencia_entrega"];
    $consumidores[$row["consumidor"]]["comunidade"] = $row["comunidade"];
    $consumidores[$row["consumidor"]]["id"] = $row["id"];
}
echo '<table id="tblConsIn" border="1px" width="950">';
echo '<tr>';
echo '<td width="350">Consumidor</td>';
echo '<td width="150">Cadastro</td>';
echo '<td width="50">Grupo</td>';
echo '<td width="100">Entrega</td>';
echo '<td width="150">Ver Cesta</td>';
echo '<td width="150">Editar Cesta</td>';
echo '<td width="100">Ativar/Desativar</td>';
echo '</tr>';
foreach ($consumidores as $consumidor=>$dados) {
    echo '<tr>';
    echo '<td>';
    if ($dados["contador"] > 1 || $cpfs[$dados["cpf"]] > 1) {
        echo '<span style="color:#FF0000;">';
        echo ucwords(strtolower($consumidor))."(".$dados["id"].")";
        echo '</span>';
    } else {
        echo ucwords(strtolower($consumidor))."(".$dados["id"].")";
    }
    echo '</td>';
    echo '<td>Em '.date('d/m/Y',strtotime($dados["data_criacao"])).'</td>';
    //<grupo>
    echo '<td>';
    echo '<select id="grupo_'.$dados["id"].'" name="grupo_'.$dados["id"].'" idConsumidor="'.$dados["id"].'" mudagrupo>';
    for ($i = 0; $i <= $nGrupos; $i++) {
        if ($i == $dados["comunidade"]) {
            echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
        } else {
            echo '<option value="'.$i.'">'.$i.'</option>';
        }
    }
    echo '</select>';
    echo '</td>';
    //</grupo>
    echo '<td>'.$dados["preferencia_entrega"].'</td>';
    echo '<td><a href="../Cestas/?cpf='.$dados["cpf"].'" target="_blank">Ver Cesta</a></td>';
    echo '<td><a href="editar_cesta.php?cpf='.$dados["cpf"].'" target="_blank">Editar Cesta</a></td>';
    if ($dados["ativo"] == 0) {
        echo '<td><a href="javascript:ativar(\'?cpf='.$dados["cpf"].'&ativo=1\','.$_SESSION["level"].',\''.ucwords(strtolower($consumidor)).'\')">Ativar</a></td>';
    } else {
        echo "ja ativo";
    }
    echo '</tr>';
}
echo '</table>';
*/
function formatSaldo($saldo) {
    if ($saldo >= 0) {
        return "R$".number_format(abs($saldo),2,",",".");
    } else {
        return "-R$".number_format(abs($saldo),2,",",".");
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="painel.js"></script>

<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.js"></script>
<script src="https://livresbs.com.br/Painel/_js/datepicker/datepicker.pt-BR.js"></script>

<script>
$(function () {
	//DATEPICKER CONFIG
	var start = new Date(), prevDay, startHours = 0;

	// ÚLTIMO HORÁRIO
	start.setHours(0);
	start.setMinutes(0);

	var dataPicker = $('.jwc_datepicker_start').datepicker({
		timepicker: true,
		startDate: start,
		minHours: startHours
	}).data('datepicker');
});
</script>
    </body>
</html>