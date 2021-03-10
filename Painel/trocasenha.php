<?php
$levelAll = 20000; //nível em que é possível alterar senha/nível de qualquer usuário
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
    <head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <head>
        <link rel="stylesheet" href="https://livresbs.com.br/Painel/_js/datepicker/datepicker.min.css"/>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="painel.css">
    </head>
    <body>
<?php
echo $twig->render('menu.html', [
	"titulo" => "LivresBS",
	"menu_datas" => $calendario->listaDatas(),
    "data_selecionada"  => (isset($_SESSION['data_consulta']) ? date('d/m/Y H:i',strtotime($_SESSION["data_consulta"])) : ""),
    "frequencia_semana" => $calendario->montaDisplayFrequenciaSemana(),
]);

if (!isset($_GET["login"])) {
?>
<form method="GET" action="">
    <label for="login">Email</label>
    <input type="text" name="login" id="login" value="" />
    <input type="submit" name="submit" id="submit" value="Enviar" />
</form>
<?php
} else {
    if ($_GET["login"] != $_SESSION["login"]) {
        if ($_SESSION["level"] < $levelAll) {
            echo "Você não tem permissão para alterar usuários além do seu.";
            exit();
        }
    }
    $conn = new PDO("mysql:host=".$c_db["host"].";dbname=".$c_db["name"].";charset=utf8",$c_db["user"],$c_db["password"]);
    if (isset($_POST["login"]) && isset($_POST["senha_antiga"]) && isset($_POST["senha_nova1"]) && isset($_POST["senha_nova2"])) {
        $login= $_POST["login"];
        $senhaAntiga= $_POST["senha_antiga"];
        $senhaNova1 = $_POST["senha_nova1"];
        $senhaNova2 = $_POST["senha_nova2"];
        $nivel = $_POST["nivel"];
        $sql = "SELECT * FROM Admins WHERE login = '".$login."'";
        $st = $conn->prepare($sql);
        $st->execute();
        $rs = $st->fetchAll();
        $login = $rs[0]["login"];
        $level = $rs[0]["level"];
        //Retornar nível para o cadastrado no BD, caso usuário realizando a alteração não seja admin
        if ($_SESSION["level"] < $levelAll) {
            $nivel = $level;
        }
        if ($st->rowCount() == 1) {
            $senhaDB = $rs[0]["password"];
            if (password_verify($senhaAntiga,$senhaDB) || $_SESSION["level"] >= $levelAll) {
                if ($senhaNova1 == $senhaNova2) {
                    if (strlen($senhaNova1) + strlen($senhaNova2) > 0) {
                        $senhaNova1 = password_hash ($senhaNova1,PASSWORD_DEFAULT);
                        $sqlUpdate = "UPDATE Admins SET password = '".$senhaNova1."', level=".$nivel." WHERE login = '".$login."'";
                        $st = $conn->prepare($sqlUpdate);
                        $st->execute();
                        if ($_SESSION["level"] < $levelAll) {
                            echo "Senha alterada com sucesso<br>";
                        } else {
                            echo "Senha e nível alterados com sucesso<br>";
                        }
                        if ($login == $_SESSION["login"]) {
                            $_SESSION["logado"]="";
                            $_SESSION["login"]="";
                            $_SESSION["level"]="";
                            session_destroy();
                        }
                        echo '<a href="index.php">Voltar</a>';
                        exit();
                    } else {
                        //Alteração apenas de nível
                        if ($_SESSION["level"] < $levelAll) {
                            echo "Você não tem permissão para alterar apenas nível";
                        } else {
                            $sqlUpdate = "UPDATE Admins SET level = ".$nivel." WHERE login = '".$login."'";
                            $st = $conn->prepare($sqlUpdate);
                            $st->execute();
                            echo "Nível alterado com sucesso<br>";
                            if ($login == $_SESSION["login"]) {
                                $_SESSION["logado"]="";
                                $_SESSION["login"]="";
                                $_SESSION["level"]="";
                                session_destroy();
                            }
                            echo '<a href="index.php">Voltar</a>';
                            exit();
                        }
                    }
                } else {
                    echo "As novas senhas digitadas estão diferentes entre si.";
                }
            } else {
                echo "Email/Senha incorreto(s).";
            }
        } else {
            echo "Email/Senha incorreto(s)!";
        }
    } else {
        $sql = "SELECT * FROM Admins WHERE login = '".$_GET["login"]."'";
        $st = $conn->prepare($sql);
        $st->execute();
        $rs = $st->fetchAll();
        $login = $rs[0]["login"];
        $level = $rs[0]["level"];
    }
    ?>
    <form method="POST" action="">
        <p>
            <label for="login">Email</label>
            <input type="text" name="login" id="login" value="<?php echo $_GET["login"]; ?>" />
        </p><p>
            <label for="senha_antiga">Senha Antiga</label>
            <input type="password" name="senha_antiga" id="senha_antiga" ?>
        </p><p>
            <label for="senha_nova1">Nova Senha</label>
            <input type="password" name="senha_nova1" id="senha_nova1" />
        </p><p>
            <label for="senha_nova2">Nova Senha (repita)</label>
            <input type="password" name="senha_nova2" id="senha_nova2" />
        </p>
        </p>
        <?php
        if ($_SESSION["level"] >= $levelAll) {
        ?>
        <p>
            <label for="nivel">Nível</label>
            <select id="nivel" name="nivel">
                <?php
                for ($i = 0; $i <= $levelAll; $i+=1000) {
                    echo '<option '.(($level == $i) ? " selected" : "").' value="'.$i.'">'.$i.'</option>';
                }
                ?>
            </select>
        </p>
        <?php
        } else {
            echo '<input type="hidden" name="nivel" id="nivel" value="'.$level.'" />';
        }
        ?>
        <p>
            <input type="submit" name="submit" id="submit" value="Enviar" />
        </p>
    </form>
<?php
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