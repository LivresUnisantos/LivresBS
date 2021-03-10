<?php

include "../config.php";

if (isset($_POST["cpf"])) {

    $conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );

    $sql = "SELECT * FROM Consumidores WHERE cpf = '".$_POST["cpf"]."'";
    $st = $conn->prepare($sql);
    $st->execute();
    
    if ($st->rowCount() == 0) {
        echo 0;
    } else {
        echo 1;
    }

}

?>