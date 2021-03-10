<p>
    <a href="index.php">Início</a><br>
    <a href="Entregas.php">Lista Entregas da Semana</a><br>
    <a href="pedido_produtores.php">Pedido Produtores da Semana</a><br>
	<!--<a href="cota_consumidores.php?imprimir=1">Cota Fixo/Variavel da Semana</a><br>-->
	<a href="variaveis.php">Pedido Variáveis da Semana</a><br>
	<a href="cad_variaveis.php">Cadastro Variáveis da Semana</a><br>
	<a href="demanda_produto.php">Demanda</a><br>
    <a href="produtos.php?imprimir=1">Lista de Produtos</a><br>
    <a href="consumidores.php">Lista de Consumidores</a><br>
	<a href="planilha_caixa.php">Planilha Controle Caixa</a><br>
    <br>
    
    <a href="pedidos_avulsos.php">Pedidos Avulsos</a><br>
    <a href="consumidores_avulsos.php">Consumidores Avulsos</a><br>
    <a href="contatos_feiras.php">Contatos das Feiras</a><br>
    <br>
    <a href="calendario.php">Calendário</a><br>
    <a href="contabilidade.php">Resumo Contabilidade</a><br>
    <a href="delivery.php?imprimir=1">Delivery</a><br>
    <a href="criar_usuario.php">Cadastrar Administrador</a><br>
    <a href="interessados.php">Interessados Cadastrados</a><br>
    <a href="trocasenha.php" target="_blank">Trocar Senha</a><br>
    <a href="deslogar.php">Deslogar</a>
    <?php
    if ($_SESSION["login"] == "hrmele@gmail.com") {
        echo '<br><br>';
        echo '<a href="logs.php">Logs</a>';
        echo '<br><br>';
    }
    ?>
</p>