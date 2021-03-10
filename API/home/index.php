 <?php
if (isset($_POST["nome"])) {
    include "../../config.php";
	$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"],
		array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
	);
	if (strlen($_POST["nome"])>0 && strlen($_POST["email"])>0 && strlen($_POST["telefone"])>0) {
	    $sql = "INSERT INTO Interessados (nome,email,telefone) VALUES ('".$_POST["nome"]."','".$_POST["email"]."','".$_POST["telefone"]."')";
	    $st = $conn->prepare($sql);
	    if ($st->execute()) {
	        echo "ok";
	    } else {
	        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	    }
	} else {
	    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	    /*echo "Preencha todos os campos!";
	    $nome = $_POST["nome"];
	    $email = $_POST["email"];
	    $telefone = $_POST["telefone"];
	    */
	}
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
}
?>