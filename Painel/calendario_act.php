<?php
$levelRequired=10000;
include "../config.php";
include "acesso.php";
include "helpers.php";

$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);

if (isset($_POST["idCalendario"])) {
    $idCalendario = $_POST["idCalendario"];
    if (isset($_POST["act"])) {
        //Atualiza calendário
        if ($_POST["act"] == "atualiza_calendario") {
            $grupo = $_POST["grupo"];
            $frequencia = $_POST["frequencia"];
            $sql = "UPDATE Calendario SET ".$grupo."acomunidade = '".$frequencia."' WHERE id = $idCalendario";
            $st = $conn->prepare($sql);
            if ($st->execute()) {
                echo "Salvo";
                setlog("log.txt","Calendário atualizado",$sql);
            } else {
                echo "Falha ao atualizar. Tente novamente.";
            }
        }
    }
}
?>