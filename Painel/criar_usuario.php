<?php
$levelRequired=15000;
include "../config.php";
include "acesso.php";
include "password.php";

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
    <!--<link rel="stylesheet" href="painel.css">-->
    </head>
    <body>
<?php
echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);

if (!isset($_POST["login"]) || !isset($_POST["nome"]) || !isset($_POST["cpf"]) || !isset($_POST["nascimento"]) || !isset($_POST["senha1"]) || !isset($_POST["senha2"])) {
    if (isset($_POST["login"])) {
        $login=$_POST["login"];
    } else {
        $login="";
    }
    if (isset($_POST["nome"])) {
        $nome=$_POST["nome"];
    } else {
        $nome="";
    }
    $campos = ["login", "nome", "cpf", "nascimento"];
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $$campo = $_POST[$campo];
        } else {
            $$campo = "";
        }
    }
?>
<form action="" method="POST">
    <div class="col-3">
        <div class="form-group">
            <label for="login">Email</label>
            <input class="form-control" type="text" name="login" id="senha" value="<?php echo $login; ?>" />
        </div>
        <div class="form-group">
            <label for="nome">Nome</label>
            <input class="form-control" type="text" name="nome" id="senha" value="<?php echo $nome; ?>" />
        </div>
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input class="form-control" type="text" name="cpf" id="cpf" value="<?php echo $cpf; ?>" />
        </div>
        <div class="form-group">
            <label for="nascimento">Nascimento</label>
            <input class="form-control" type="date" name="nascimento" id="nascimento" value="<?php echo $nascimento; ?>" />
        </div>
        <div class="form-group">
            <label for="senha1">Senha</label>
            <input class="form-control" type="password" name="senha1" id="senha1" />
        </div>
        <div class="form-group">
            <label for="senha2">Confirmação Senha</label>
            <input class="form-control" type="password" name="senha2" id="senha2" />
        </div>
        <input class="btn btn-primary" type="submit" name="Cadastrar" id="Cadastrar" value="Cadastrar" />
    </div>
</form>
<?php
} else {
    $login = $_POST["login"];
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $nascimento = $_POST["nascimento"];
    $senha1 = $_POST["senha1"];
    $senha2 = $_POST["senha2"];
    
    $cpf = str_replace(".","",$cpf);
    $cpf = str_replace(",","",$cpf);
    $cpf = str_replace("-","",$cpf);
    $cpf = str_replace("","",$cpf);
    
    if (strlen($login) == 0 || strlen($nome) == 0 || strlen($cpf) == 0 || strlen($nascimento) == 0 || strlen($senha1) == 0 || strlen($senha2) == 0) {
        echo "Preencha todos os campos.";
    } else {
        if ($senha1 != $senha2) {
            echo "Confirmação de senha não é igual a senha.";
        } else {
            //Verificar se email já existe no banco
            //$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
            $livres = new Livres();
            $conn = $livres->conn();
            $sql = "SELECT * FROM Admins WHERE login = '".$login."'";
            $st = $conn->prepare($sql);
            $st->execute();
            if ($st->rowCount() > 0) {
                echo "Email já cadastrado";
            } else {
                //Liberado para cadastrar
                $senha = password_hash ($senha1,PASSWORD_DEFAULT);
                $sql = "INSERT INTO Admins (login, nome, cpf, nascimento, password, level) VALUES (?, ?, ?, ?, ?, ?)";
                $st = $conn->prepare($sql);
                if ($st->execute([$login, $nome, $cpf, $nascimento, $senha, 1000])) {
                    echo "Usuário cadastrado";
                } else {
                    echo "Falha ao cadastrar usuário<br>";
                    $st->debugDumpParams();
                }
            }
        }
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
<?php
/*$conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"],$c_db["user"],$c_db["password"]);
$senha = password_hash("190788",PASSWORD_DEFAULT);
$sql = "UPDATE Admins SET password = '".$senha."'";
$st = $conn->prepare($sql);
$st->execute();
*/
?>