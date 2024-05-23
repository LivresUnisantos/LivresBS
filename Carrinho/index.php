<!DOCTYPE html>
<html lang="en">
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
            <form action="cart.php" method="post">
                <div class="cart-items">
                    <?php
                    include 'db.php'; // Inclui a conexão com o banco de dados
                    
                    $stmt = $pdo->prepare("SELECT c.id, p.name, p.price, p.image, c.quantity FROM cart c JOIN products p ON c.product_id = p.id");
                    $stmt->execute();
                    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($cart_items as $item):
                    ?>
                    <div class="cart-item">
                        <img src="img/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>R$<?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="quantity-controls">
                            <button type="button" class="decrease">-</button>
                            <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>">
                            <button type="button" class="increase">+</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
                            <?php
                            $total = 0;
                            foreach ($cart_items as $item):
                                $total += $item['price'] * $item['quantity'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>R$<?php echo number_format($item['price'] * $item['quantity'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Valor Total</td>
                                <td>R$<?php echo number_format($total, 2, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="buttons">
                        <button type="submit" name="update">Finalizar Pedido</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <footer>
        <p>© 2024, Livres</p>
    </footer>
    <script src="https://use.fontawesome.com/releases/v5.7.1/js/all.js"></script>
</body>
</html>
