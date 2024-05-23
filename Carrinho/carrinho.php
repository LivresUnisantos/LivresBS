<!DOCTYPE html>
<?php
session_start();

include 'functions.php';
$pdo = pdo_connect_mysql();

?>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="content-wrapper">
            <h1>Carrinho de Compras</h1>
            <nav>
                <a href="#">Usuário</a>
                <a href="#">Endereço do Usuário</a>
            </nav>
            <div class="link-icons">
                <i class="fas fa-shopping-cart"></i>
                <span>3</span>
            </div>
        </div>
    </header>
    <main>
        <div class="cart content-wrapper">
            <h1>Carrinho de Compras</h1>
            <div class="cart-items">
                <!-- Aqui os produtos serão adicionados dinamicamente -->
            </div>
            <div class="order-summary">
                <h3>Resumo do pedido</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aqui os produtos serão adicionados dinamicamente -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Valor Total</td>
                            <td>R$21,93</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="buttons">
                    <button type="submit" name="update" class="form-control btn btn-success">Finalizar Pedido</button>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p> © 2024, Livres</p>
    </footer>
    <script src="https://use.fontawesome.com/releases/v5.7.1/js/all.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Captura o clique no botão de carrinho
            document.querySelectorAll('.form-control.btn.btn-success').forEach(button => {
                button.addEventListener('click', () => {
                    // Identifica o produto clicado pelo ID
                    const productId = button.closest('.box-product').id;
                    // Clona a caixa do produto correspondente
                    const productClone = document.getElementById(productId).cloneNode(true);
                    // Remove o ID do produto clonado para evitar duplicatas
                    productClone.removeAttribute('id');
                    // Adiciona o produto clonado à lista de itens no carrinho
                    document.querySelector('.cart-items').appendChild(productClone);
                    // Remove a mensagem de carrinho vazio se houver produtos
                    document.querySelector('.cart-items').querySelector('.empty-cart-message').style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
