<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$levelRequired=100000;
require_once "../includes/autoloader.inc.php";
require_once '../twig/autoload.php';
require_once "acesso.php";

if (!isset($_POST["id"])) {
    $ids="";
} else {
    $ids = $_POST["id"];
}
?>
<html>
    <head>
        <style>
            .alert {
                color: #ff0000;
            }
        </style>
    </head>
    <body>
    Separe múltiplos produtos por vírgula
    <form method="POST">
        <input type="text" id="id" name="id" value="<?php echo $ids; ?>" />
        <input type="submit" value="Buscar" />
    </form>
<?php
$livres = new Livres();

$buscar['lista_itens'] = 'SELECT * FROM listas_itens WHERE id_produto = @search_id;';
$buscar['PedidosVar'] = 'SELECT * from PedidosVar where idOpcao1=@search_id or idOpcao2=@search_id or escolhaOpcao1=@search_id or escolhaOpcao2=@search_id';
$buscar['produtosVar'] = 'SELECT * from produtosVar where idProduto=@search_id';
$buscar['pedidos_consolidados_itens'] = 'SELECT * from pedidos_consolidados_itens where produto_id=@search_id';
$buscar['pedidos'] = 'SELECT * from Pedidos where IDProduto=@search_id';

if (isset($_POST["id"])) {
    $ids = explode(",",$ids);
    $total=0;
    foreach ($ids as $id) {
        $sqlProduto = "SELECT * FROM produtos WHERE id = ".$id;
        $st = $livres->conn()->prepare($sqlProduto);
        $st->execute();
        $rs = $st->fetch();
        
        echo '<p>ID = '.$id.' Nome: '.$rs["nome"].'</p>';
        foreach ($buscar as $tbl=>$sql) {
            $sql = str_replace("@search_id",$id,$sql);
            $st = $livres->conn()->prepare($sql);
            $st->execute();
            
            $total += $st->rowCount();
            $resultado[$tbl] = $st->rowCount();
            if ($resultado[$tbl] > 0) {
                echo '<span class="alert">'.$tbl.' - '.$resultado[$tbl].'</span><br>';
            } else {
                echo $tbl." - ".$resultado[$tbl]."<br>";
            }
        }
        if ($total > 0) {
            echo "<br>";
            echo '<span class="alert">Produto não pode ser apagado</span>';
        }
        echo "<hr>";
    }
}
    
?>
    </body>
</html>