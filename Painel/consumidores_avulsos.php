<?php
$levelRequired=2000;
include "../config.php";
include "acesso.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);

if ($_SESSION["level"] >= 10000) {
    if (isset($_POST["idusuario"]) && isset($_POST["grupo"])) {
        $sql = "UPDATE Usuarios SET grupo = '".$_POST["grupo"]."' WHERE id=".$_POST["idusuario"];
        $st = $conn->prepare($sql);
        if ($st->execute()) {
            echo "Grupo alterado";
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
        exit();
    }
}
include "menu.php";
?>
<html>
    <head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
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
            #edit-box {
                position: absolute;
                background: #000000;
                display: none;
                top: 0;
                left: 0;
            }
        </style>
        <script src="../js/vendor/jquery.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
        $(document).ready(function() {
            $("body").click(function(e) {
                console.log(e.target.id);
                if (e.target.id != "edit-grupo") {
                    $("#edit-box").hide();
                }
            });
            $(".grupo" ).dblclick(function() {
                $("#edit-grupo").attr('idusuario',$(this).attr('idusuario'));
                $("#edit-box").css('top',$(this).offset().top);
                $("#edit-box").css('left',$(this).offset().left);
                $("#edit-box").css('width',$(this).width());
                $("#edit-box").css('height',$(this).height());
                $("#edit-grupo").val($(this).text());
                $("#edit-box").show();
            });
            $("#edit-grupo").change(function() {
                novogrupo = $(this).val();
                idusuario = $(this).attr('idusuario');
                $.ajax({
                        method: "POST",
                        url: "consumidores_avulsos.php",
                        data: {
                            idusuario: idusuario,
                            grupo: novogrupo
                        }
                    })
                    .done(function(msg) {
                        console.log(msg);
                        alert("Alteração Realizada. A página será atualizada.");
                        $("#edit-box").hide();
                        location.reload();
                    })
                    .fail(function() {
                        alert("Falha ao realizar alteração, tente novamente.");
                        $("#edit-box").hide();
                    });
            });
        });
        </script>
    </head>
    <body>
<div id="edit-box">
    <select id="edit-grupo" idusuario="">
        <option value="pre-comunidade">pre-comunidade</option>
        <option value="APROATE">APROATE</option>
        <option value="AOVALE">AOVALE</option>
        <option value="Não sei">não sei</option>
    </select>
</div>
<?php
if (isset($alerta)) {
    if (strlen($alerta) > 0) {
        echo "<script>alert('".$alerta."')</script>";
    }
}
$sql = "SELECT * FROM Usuarios ORDER BY nome";
$st = $conn->prepare($sql);
$st->execute();

if ($st->rowCount() > 0) {
    $rs=$st->fetchAll();
    echo '<table>';
    echo '<tr class="firstLine">';//
    echo '<td>Nome</td>';
    echo '<td>Email</td>';
    echo '<td>Endereço</td>';
    echo '<td>CPF</td>';
    echo '<td>Grupo</td>';
    echo '<td>Telefone</td>';
    echo '</tr>';
    $count=0;
    foreach ($rs as $row) {
        $count++;
        if ($count % 2 == 0) {
            echo '<tr bgcolor="#d1f1ff">';
        } else {
            echo '<tr>';
        }
        echo '<td>'.$row["nome"].'</td>';
        echo '<td>'.$row["email"].'</td>';
        echo '<td>'.$row["endereco"].'</td>';
        echo '<td>'.$row["cpf"].'</td>';
        echo '<td class="grupo" idusuario="'.$row["id"].'">'.$row["grupo"].'</td>';
        echo '<td>'.$row["telefone"].'</td>';
        echo "</tr>";
    }
    echo '</table>';
}
?> 
    </body>
</html>