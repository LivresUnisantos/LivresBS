<?php
if (!isset($_POST["cpf"])) {
?>
    Consulta Pedido - Atualizado em 28/05/2019
    <form action="" method="POST">
        <label for="cpf" />CPF</label>
        <input type="text" value="" name="cpf" id="cpf" />
        <input type="submit" value="Enviar" />
    </form>
<?php
} else {
    $cpf = $_POST["cpf"];
    $cpf=str_replace(".","",$cpf);
    $cpf=str_replace(",","",$cpf);
    $cpf=str_replace("-","",$cpf);
    header("Location: http://livresbs.com.br/Cestas/".$cpf.".pdf");
}
?>